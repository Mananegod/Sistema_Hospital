<?php

namespace App\Http\Controllers;

use App\Imports\MedicamentosImport;
use App\Imports\InsumosImport;
use App\Helpers\LoteHelper;
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

    $fechaInicio = $request->get('fecha_inicio', now()->subDays(30)->toDateString());
    $fechaFin = $request->get('fecha_fin', now()->toDateString());

    $consumoPorFecha = DB::table('retiros')
        ->join('medicamentos', 'retiros.medicamento_id', '=', 'medicamentos.id')
        ->select(
            'medicamentos.nombre_medicamento as medicamento',
            DB::raw('SUM(retiros.cantidad) as total_consumido')
        )
        ->whereBetween(DB::raw('DATE(retiros.created_at)'), [$fechaInicio, $fechaFin])
        ->groupBy('medicamentos.nombre_medicamento')
        ->orderBy('total_consumido', 'desc')
        ->get();

    $almacenadoHoy = DB::table('medicamentos')
        ->whereDate('updated_at', now()->toDateString())
        ->sum('cantidad_stock');

    // Query 1: Medicamentos
    $inventario = DB::table('medicamentos')
        ->select(
            'id as medicamento_id',
            'nombre_medicamento as medicamento',
            'presentacion',
            'cantidad_stock as stock_actual',
            'stock_minimo',
            'tipo_insumo',
            'codigo_lote'
        )
        ->when($request->tipo_insumo, function ($query, $tipo_insumo) {
            return $query->where('tipo_insumo', $tipo_insumo);
        })
        ->orderBy('nombre_medicamento')
        ->paginate(50)
        ->appends($request->query());

    // Query 2: Insumos Médicos
    $insumos = DB::table('insumos_medicos')
        ->when($request->tipo_insumo, function ($query, $tipo_insumo) {
            return $query->where('tipo_insumo', $tipo_insumo);
        })
        ->orderBy('nombre_insumo')
        ->paginate(50, ['*'], 'page_insumos')
        ->appends($request->query());

    return view('almacen.index', compact(
        'inventario', 
        'insumos',
        'areas', 
        'tiposInsumo', 
        'consumoPorFecha', 
        'almacenadoHoy', 
        'fechaInicio', 
        'fechaFin'
    ));
}

    public function importarInsumosExcel(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls,csv|max:10240',
        ], [
            'archivo.max'   => 'El archivo es demasiado grande (máx. 10 MB).',
            'archivo.mimes' => 'Solo se permiten archivos Excel (xlsx, xls) o CSV.',
        ]);

        try {
            session_write_close();

            Excel::import(new InsumosImport, $request->file('archivo'));

            $this->clasificarInsumosNuevosTabla();

            return redirect()->route('almacen.index', ['categoria' => 'insumo'])
                ->with('success', '¡El archivo de insumos médicos fue importado y guardado en la base de datos con éxito!');
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errores = [];
            foreach ($failures as $index => $failure) {
                if ($index >= 10) {
                    $errores[] = "... y más errores omitidos.";
                    break;
                }
                $errores[] = "Fila {$failure->row()}: " . implode(', ', $failure->errors());
            }
            return back()->with('error', 'Error en el formato: ' . implode(' | ', $errores));
        } catch (\Exception $e) {
            return back()->with('error', 'Error en importación de insumos: ' . $e->getMessage());
        }
    }

    private function clasificarInsumosNuevosTabla()
    {
        $diccionario = [
            'Jeringa' => ['jeringa', 'inyectadora', 'scalp', 'obturador', 'aguja'],
            'Material Médico Quirúrgico' => ['gasa', 'compresa', 'guantes', 'venda', 'adhesivo', 'bisturi', 'sutura', 'hilo', 'cateter', 'yelco'],
            'Protección / Bioseguridad' => ['tapaboca', 'mascarilla', 'bata', 'gorro', 'careta', 'alcohol'],
        ];

        foreach ($diccionario as $tipo => $palabras) {
            DB::table('insumos_medicos')
                ->where(function ($query) {
                    $query->whereNull('tipo_insumo')
                          ->orWhere('tipo_insumo', 'Por Determinar');
                })
                ->where(function ($query) use ($palabras) {
                    foreach ($palabras as $palabra) {
                        $query->orWhere(DB::raw('LOWER(nombre_insumo)'), 'like', "%" . strtolower($palabra) . "%");
                    }
                })
                ->update(['tipo_insumo' => $tipo]);
        }

        DB::table('insumos_medicos')
            ->whereNull('tipo_insumo')
            ->update(['tipo_insumo' => 'Por Determinar']);
    }

    public function buscarInsumos(Request $request)
    {
        $q = trim($request->get('q', ''));
        $tipoTabla = $request->get('tabla', 'medicamentos');

        if (empty($q)) {
            return response()->json([]);
        }

        $tabla = ($tipoTabla === 'insumos') ? 'insumos_medicos' : 'medicamentos';
        $columna = ($tipoTabla === 'insumos') ? 'nombre_insumo' : 'nombre_medicamento';

        $resultados = DB::table($tabla)
            ->select('id', "{$columna} as text")
            ->where(DB::raw("LOWER({$columna})"), 'like', '%' . strtolower($q) . '%')
            ->orderBy($columna)
            ->limit(10)
            ->get();

        return response()->json($resultados);
    }

    public function buscarMedicamentos(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (empty($q)) {
            return response()->json([]);
        }

        $medicamentos = DB::table('medicamentos')
            ->select('id', 'nombre_medicamento as text')
            ->where(DB::raw('LOWER(nombre_medicamento)'), 'like', '%' . strtolower($q) . '%')
            ->orderBy('nombre_medicamento')
            ->limit(10)
            ->get();

        return response()->json($medicamentos);
    }

    public function entradaRapida(Request $request)
    {
        $request->validate([
            'medicamento_id' => 'required',
            'cantidad' => 'required|integer|min:1',
        ]);

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
                'cantidad_stock' => DB::raw('cantidad_stock + ' . (int)$request->cantidad),
                'tipo_insumo' => $tipoInsumo,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Stock actualizado correctamente.');
    }

    public function importarExcel(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls,csv|max:10240',
        ], [
            'archivo.max'   => 'El archivo es demasiado grande (máx. 10 MB).',
            'archivo.mimes' => 'Solo se permiten archivos Excel (xlsx, xls) o CSV.',
        ]);

        try {
            session_write_close();

            Excel::import(new MedicamentosImport, $request->file('archivo'));

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
            'Analgésico / Antiinflamatorio' => ['profeno', 'fenaco', 'paracetamol', 'acetaminofen', 'ketoprofeno', 'diclofenac', 'meloxicam', 'acido acetilsalicilico', 'ácido acetilsalicilico'],
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
                        $query->orWhere(DB::raw('LOWER(nombre_medicamento)'), 'like', "%" . strtolower($palabra) . "%");
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
        'area_id'        => 'required',
        'cantidad'       => 'required|integer|min:1',
    ]);

    $medicamento = DB::table('medicamentos')->where('id', $request->medicamento_id)->first();

    if (!$medicamento) {
        return back()->with('error', 'El medicamento seleccionado no existe.');
    }

    if ($medicamento->cantidad_stock < $request->cantidad) {
        return back()->with('error', "Stock insuficiente.");
    }

    DB::transaction(function () use ($request, $medicamento) {
        DB::table('medicamentos')
            ->where('id', $request->medicamento_id)
            ->decrement('cantidad_stock', $request->cantidad);

        DB::table('retiros')->insert([
            'medicamento_id' => $request->medicamento_id,
            'lote_id'        => $medicamento->lote_id, // <-- SE GUARDA EL LOTE EXACTO DESPACHADO
            'area_id'        => $request->area_id,
            'cantidad'       => $request->cantidad,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    });

    return back()->with('success', 'El retiro ha sido registrado con éxito.');
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

    // 1. Buscamos en la tabla de medicamentos
    $medicamentos = DB::table('medicamentos')
        ->where(DB::raw('LOWER(codigo_lote)'), '=', strtolower($codigo_lote))
        ->orderBy('nombre_medicamento')
        ->get();

    // 2. Buscamos en la tabla de insumos_medicos
    $insumos = DB::table('insumos_medicos')
        ->where(DB::raw('LOWER(codigo_lote)'), '=', strtolower($codigo_lote))
        ->orderBy('nombre_insumo')
        ->get();

    // Si no se encuentra registros en NINGUNA de las dos tablas
    if ($medicamentos->isEmpty() && $insumos->isEmpty()) {
        return redirect()->route('almacen.index')
            ->with('error', "No se encontraron registros de medicamentos ni insumos con el lote: {$codigo_lote}");
    }

    return view('almacen.ver_lote', compact('medicamentos', 'insumos', 'codigo_lote'));
}

public function editarMasivo(Request $request)
{
    $request->validate(['ids' => 'required']);
    $ids = json_decode($request->ids, true);

    if (empty($ids)) {
        return back()->with('error', 'No se seleccionó ningún registro válido.');
    }

    $updateData = [];

    if (!empty($request->codigo_lote)) {
        $codigo = trim($request->codigo_lote);
        $updateData['codigo_lote'] = $codigo;
        
        // Asignamos o creamos la llave foránea usando el Helper
        $updateData['lote_id'] = LoteHelper::obtenerOCrearLoteId($codigo);
    }

    if ($request->filled('cantidad_stock')) {
        $updateData['cantidad_stock'] = (int)$request->cantidad_stock;
    }

    if (empty($updateData)) {
        return back()->with('info', 'No se realizó ninguna modificación.');
    }

    $updateData['updated_at'] = now();
    $tabla = ($request->get('tabla') === 'insumos') ? 'insumos_medicos' : 'medicamentos';

    DB::transaction(function () use ($tabla, $ids, $updateData) {
        DB::table($tabla)
            ->whereIn('id', $ids)
            ->update($updateData);
    });

    return back()->with('success', '¡Se han actualizado con éxito los ' . count($ids) . ' registros seleccionados!');
}
}