<?php

namespace App\Http\Controllers;

use App\Imports\MedicamentosImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class AlmacenController extends Controller
{
    public function index(Request $request)
    {
        $areas = DB::table('areas')->get();

        $tiposInsumo = DB::table('medicamentos')
            ->whereNotNull('tipo_insumo')
            ->distinct()
            ->pluck('tipo_insumo');

        $inventario = DB::table('medicamentos')
            ->select(
                'id as medicamento_id',
                'nombre_medicamento as medicamento',
                'presentacion',
                'cantidad_stock as stock_actual',
                'stock_minimo',
                'area_destino',
                'tipo_insumo',
                'codigo_lote'
            )
            ->when($request->area_id, function ($query, $area_id) {
                $areaDestino = DB::table('areas')->where('id', $area_id)->value('nombre_area');
                return $areaDestino ? $query->where('area_destino', $areaDestino) : $query;
            })
            ->when($request->tipo_insumo, function ($query, $tipo_insumo) {
                return $query->where('tipo_insumo', $tipo_insumo);
            })
            ->orderBy('nombre_medicamento')
            ->paginate(50)
            ->appends($request->query())
            ->through(function ($item) {
                // Saneamiento estricto del código de lote para evitar URLs rotas
                $item->codigo_lote = !empty($item->codigo_lote) ? trim($item->codigo_lote) : null;
                if ($item->codigo_lote === '') {
                    $item->codigo_lote = null;
                }
                return $item;
            });

        return view('almacen.index', compact('inventario', 'areas', 'tiposInsumo'));
    }

    public function buscarMedicamentos(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (empty($q)) {
            return response()->json([]);
        }

        $medicamentos = DB::table('medicamentos')
            ->select('id', 'nombre_medicamento as text')
            ->where('nombre_medicamento', 'ilike', "%{$q}%")
            ->orderBy('nombre_medicamento')
            ->limit(10)
            ->get();

        return response()->json($medicamentos);
    }

    public function entradaRapida(Request $request)
    {
        $request->validate([
            'medicamento_id' => 'required',
            'area_id' => 'required',
            'cantidad' => 'required|integer|min:1',
        ]);

        $areaDestino = DB::table('areas')->where('id', $request->area_id)->value('nombre_area');

        if (!$areaDestino) {
            return back()->with('error', 'El área seleccionada no es válida.');
        }

        $medicamento = DB::table('medicamentos')->where('id', $request->medicamento_id)->first();

        if (!$medicamento) {
            return back()->with('error', 'El insumo seleccionado no existe.');
        }

        $tipoInsumo = $medicamento->tipo_insumo;
        if (empty($tipoInsumo) || $tipoInsumo == 'Por Determinar') {
            $tipoInsumo = $this->deducirTipoInsumo($medicamento->nombre_medicamento);
        }

        DB::table('medicamentos')
            ->where('id', $request->medicamento_id)
            ->update([
                'cantidad_stock' => DB::raw('cantidad_stock + ' . $request->cantidad),
                'area_destino' => $areaDestino,
                'tipo_insumo' => $tipoInsumo,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Stock actualizado correctamente en la ubicación seleccionada.');
    }

    public function importarExcel(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'area_id' => 'required',
            'archivo' => 'required|mimes:xlsx,xls,csv|max:10240',
        ], [
            'archivo.max'  => 'El archivo es demasiado grande (máx. 10 MB).',
            'archivo.mimes' => 'Solo se permiten archivos Excel (xlsx, xls) o CSV.',
        ]);

        try {
            session_write_close();

            Excel::import(new MedicamentosImport($request->area_id), $request->file('archivo'));

            $this->clasificarInsumosNuevos();

            return redirect()->route('almacen.index')->with('success', '¡El archivo F15 fue importado y clasificado con éxito!');
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errores = [];
            foreach ($failures as $index => $failure) {
                if ($index >= 15) {
                    $errores[] = "... y más errores omitidos.";
                    break;
                }
                $errores[] = "Fila {$failure->row()}: " . implode(', ', $failure->errors());
            }
            $mensaje = implode(' | ', $errores);
            if (mb_strlen($mensaje) > 500) {
                $mensaje = mb_substr($mensaje, 0, 497) . '...';
            }
            return back()->with('error', $mensaje);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            return back()->with('error', 'El archivo Excel está dañado o tiene un formato inválido: ' . $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Error inesperado: ' . $e->getMessage());
        }
    }

    private function clasificarInsumosNuevos()
    {
        $diccionario = [
            'Jeringa' => ['jeringa', 'inyectadora', 'scalp', 'obturador', 'aguja'],
            'Solución / Suero' => ['solucion', '0.9%', 'riger', 'ringer', 'dextrosa', 'fisiologica', 'suero', '0,9%'],
            'Electrólitos de Alto Riesgo' => ['potasio', 'magnesio', 'gluconato', 'calcio', 'bicarbonato', 'hipertona', '14.9%', '20%'],
            'Antibiótico' => ['cilina', 'oxacina', 'penicilina', 'ceftriaxona', 'meropenem', 'amikacina', 'ciprofloxacina'],
            'Analgésico / Antiinflamatorio' => ['profeno', 'fenaco', 'paracetamol', 'acetaminofen', 'ketoprofeno', 'diclofenac', 'meloxicam'],
            'Material Médico Quirúrgico' => ['gasa', 'compresa', 'guantes', 'venda', 'adhesivo', 'bisturi', 'sutura', 'hilo', 'cateter', 'yelco'],
            'Protección / Bioseguridad' => ['tapaboca', 'mascarilla', 'bata', 'gorro', 'careta', 'alcohol'],
            'Esteroides / Antialérgicos' => ['metasona', 'cortisona', 'prednisona', 'loratadina', 'hidrocortisona'],
            'Protector Gástrico' => ['prazol', 'omeprazol', 'pantoprazol', 'ranitidina']
        ];

        foreach ($diccionario as $tipo => $palabras) {
            DB::table('medicamentos')
                ->where(function ($query) {
                    $query->whereNull('tipo_insumo')
                          ->orWhere('tipo_insumo', 'Por Determinar');
                })
                ->where(function ($query) use ($palabras) {
                    foreach ($palabras as $palabra) {
                        $query->orWhere('nombre_medicamento', 'ilike', "%{$palabra}%");
                    }
                })
                ->update(['tipo_insumo' => $tipo]);
        }

        DB::table('medicamentos')
            ->whereNull('tipo_insumo')
            ->update(['tipo_insumo' => 'Por Determinar']);
    }

    private function deducirTipoInsumo($nombreMedicamento)
    {
        $desc = mb_strtolower($nombreMedicamento, 'UTF-8');

        $diccionario = [
            'Jeringa' => ['jeringa', 'inyectadora', 'scalp', 'obturador', 'aguja'],
            'Electrólitos de Alto Riesgo' => ['potasio', 'magnesio', 'gluconato', 'calcio', 'bicarbonato', 'hipertona', '14.9%', '20%'],
            'Solución / Suero' => ['solucion', '0.9%', 'riger', 'ringer', 'dextrosa', 'fisiologica', 'suero', '0,9%'],
            'Antibiótico' => ['cilina', 'oxacina', 'penicilina', 'ceftriaxona', 'meropenem', 'amikacina', 'ciprofloxacina'],
            'Analgésico / Antiinflamatorio' => ['profeno', 'fenaco', 'paracetamol', 'acetaminofen', 'ketoprofeno', 'diclofenac', 'meloxicam'],
            'Material Médico Quirúrgico' => ['gasa', 'compresa', 'guantes', 'venda', 'adhesivo', 'bisturi', 'sutura', 'hilo', 'cateter', 'yelco'],
            'Protección / Bioseguridad' => ['tapaboca', 'mascarilla', 'bata', 'gorro', 'careta', 'alcohol'],
            'Esteroides / Antialérgicos' => ['metasona', 'cortisona', 'prednisona', 'loratadina', 'hidrocortisona'],
            'Protector Gástrico' => ['prazol', 'omeprazol', 'pantoprazol', 'ranitidina']
        ];

        foreach ($diccionario as $tipo => $palabras) {
            foreach ($palabras as $palabra) {
                if (str_contains($desc, $palabra)) {
                    return $tipo;
                }
            }
        }

        return 'Por Determinar';
    }

    public function indexRetiros()
    {
        $areas = DB::table('areas')->get();
        $todosLosMedicamentos = DB::table('medicamentos')->orderBy('nombre_medicamento', 'asc')->get();

        $ultimosRetiros = DB::table('retiros')
            ->join('medicamentos', 'retiros.medicamento_id', '=', 'medicamentos.id')
            ->join('areas', 'retiros.area_id', '=', 'areas.id')
            ->select(
                'retiros.id',
                'medicamentos.nombre_medicamento as nombre',
                'areas.nombre_area',
                'retiros.cantidad',
                'retiros.created_at'
            )
            ->whereDate('retiros.created_at', now()->toDateString())
            ->orderBy('retiros.created_at', 'desc')
            ->get();

        return view('almacen.retiros', compact('areas', 'todosLosMedicamentos', 'ultimosRetiros'));
    }

    public function guardarRetiro(Request $request)
    {
        $request->validate([
            'medicamento_id' => 'required',
            'area_id' => 'required',
            'cantidad' => 'required|integer|min:1',
        ]);

        $medicamento = DB::table('medicamentos')->where('id', $request->medicamento_id)->first();

        if (!$medicamento) {
            return back()->with('error', 'El medicamento seleccionado no existe.');
        }

        if ($medicamento->cantidad_stock < $request->cantidad) {
            return back()->with('error', "Stock insuficiente. Solo quedan {$medicamento->cantidad_stock} unidades disponibles de {$medicamento->nombre_medicamento}.");
        }

        DB::transaction(function () use ($request) {
            // CORRECCIÓN CRÍTICA: Cambiado de ->decrement('..., $request->decrement) a usar $request->cantidad
            DB::table('medicamentos')
                ->where('id', $request->medicamento_id)
                ->decrement('cantidad_stock', $request->cantidad);

            DB::table('retiros')->insert([
                'medicamento_id' => $request->medicamento_id,
                'area_id' => $request->area_id,
                'cantidad' => $request->cantidad,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'El retiro ha sido registrado y el stock actualizado con éxito.');
    }

    private function deducirAreaDestino($nombreMedicamento)
    {
        $desc = mb_strtolower($nombreMedicamento, 'UTF-8');

        if (str_contains($desc, 'kit') || str_contains($desc, 'cirugia') || str_contains($desc, 'caina')) {
            return 'QUIRÓFANO';
        }

        $patronesMedicamentos = [
            'acetaminofen', 'paracetamol', 'insulina', 'rinsulin', 'ergometrina', 'mg', 'ml',
            'ampolla', 'amp', 'tableta', 'capsula', 'suspension', 'jarabe', 'gotas',
            'cilina', 'oxacina', 'profeno', 'fenaco', 'prazol', 'sartan'
        ];

        foreach ($patronesMedicamentos as $patron) {
            if (str_contains($desc, $patron)) {
                return 'EMERGENCIA';
            }
        }

        return null;
    }

    public function actualizarVencimientoMasivo(Request $request)
    {
        $request->validate([
            'area_id' => 'required',
            'fecha_vencimiento' => 'required|date',
        ]);

        $areaDestino = DB::table('areas')->where('id', $request->area_id)->value('nombre_area');

        if (!$areaDestino) {
            return back()->with('error', 'El área seleccionada no es válida.');
        }

        $afectados = DB::table('medicamentos')
            ->where('area_destino', $areaDestino)
            ->update([
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'updated_at' => now()
            ]);

        if ($afectados === 0) {
            return back()->with('info', "No se encontraron medicamentos registrados en el área '{$areaDestino}' para actualizar.");
        }

        return back()->with('success', "¡Éxito! Se han actualizado las fechas de vencimiento.");
    }

public function verPorLote(Request $request, $codigo_lote = null)
    {
        if (!$codigo_lote) {
            $queryString = $request->server('QUERY_STRING');
            
            if (str_contains($queryString, '=')) {
                $partes = explode('=', $queryString);
                $codigo_lote = end($partes);
            } else {
                $codigo_lote = $queryString;
            }
        }

        $codigo_lote = trim($codigo_lote);

        if (empty($codigo_lote)) {
            return redirect()->route('almacen.index')
                ->with('error', "No se especificó ningún código de lote válido.");
        }

        $medicamentos = DB::table('medicamentos')
            ->where('codigo_lote', 'ilike', $codigo_lote)
            ->orderBy('nombre_medicamento')
            ->get();

        if ($medicamentos->isEmpty()) {
            return redirect()->route('almacen.index')
                ->with('error', "No se encontraron medicamentos registrados con el lote: {$codigo_lote}");
        }

        return view('almacen.ver_lote', compact('medicamentos', 'codigo_lote'));
    }

    public function editarMasivo(Request $request)
    {
        $request->validate([
            'ids' => 'required', // Recibe el string JSON enviado por Alpine.js
        ]);

        // Decodificamos el JSON para convertirlo en un array común de PHP
        $ids = json_decode($request->ids, true);

        if (empty($ids)) {
            return back()->with('error', 'No se seleccionó ningún insumo médico válido.');
        }

        // Estructuramos qué columnas se van a actualizar realmente
        $updateData = [];

        if (!empty($request->codigo_lote)) {
            $updateData['codigo_lote'] = trim($request->codigo_lote);
        }

        if ($request->filled('cantidad_stock')) {
            $updateData['cantidad_stock'] = (int)$request->cantidad_stock;
        }

        // Si el usuario abrió el modal pero le dio a guardar vacío, no hacemos nada
        if (empty($updateData)) {
            return back()->with('info', 'No se realizó ninguna modificación porque los campos estaban vacíos.');
        }

        $updateData['updated_at'] = now();

        // Ejecutamos una sola consulta masiva en PostgreSQL de forma segura usando transacciones
        DB::transaction(function () use ($ids, $updateData) {
            DB::table('medicamentos')
                ->whereIn('id', $ids)
                ->update($updateData);
        });

        return back()->with('success', '¡Se han actualizado con éxito los ' . count($ids) . ' insumos seleccionados!');
    }
}