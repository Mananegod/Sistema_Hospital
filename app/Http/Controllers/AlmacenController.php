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
use Barryvdh\DomPDF\Facade\Pdf;




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
        $todosLosInsumos = DB::table('insumos_medicos')->orderBy('nombre_insumo', 'asc')->get();

        $hasInsumoId = Schema::hasColumn('retiros', 'insumo_id');

        if ($hasInsumoId) {
            $ultimosRetiros = DB::table('retiros')
                ->leftJoin('medicamentos', 'retiros.medicamento_id', '=', 'medicamentos.id')
                ->leftJoin('insumos_medicos', 'retiros.insumo_id', '=', 'insumos_medicos.id')
                ->join('areas', 'retiros.area_id', '=', 'areas.id')
                ->select(
                    'retiros.id',
                    DB::raw("COALESCE(medicamentos.nombre_medicamento, insumos_medicos.nombre_insumo, 'Desconocido') as nombre"),
                    'areas.nombre_area',
                    'retiros.cantidad',
                    'retiros.created_at',
                    DB::raw("CASE 
                        WHEN retiros.insumo_id IS NOT NULL THEN 'Insumo' 
                        ELSE 'Medicamento' 
                    END as tipo_item")
                )
                ->whereDate('retiros.created_at', now()->toDateString())
                ->orderBy('retiros.created_at', 'desc')
                ->get();
        } else {
            $ultimosRetiros = DB::table('retiros')
                ->join('medicamentos', 'retiros.medicamento_id', '=', 'medicamentos.id')
                ->join('areas', 'retiros.area_id', '=', 'areas.id')
                ->select(
                    'retiros.id',
                    'medicamentos.nombre_medicamento as nombre',
                    'areas.nombre_area',
                    'retiros.cantidad',
                    'retiros.created_at',
                    DB::raw("'Medicamento' as tipo_item")
                )
                ->whereDate('retiros.created_at', now()->toDateString())
                ->orderBy('retiros.created_at', 'desc')
                ->get();
        }

        return view('almacen.retiros', compact('areas', 'todosLosMedicamentos', 'todosLosInsumos', 'ultimosRetiros'));
    }

    public function guardarRetiro(Request $request)
{
    $tipo = $request->input('tipo_item', 'medicamento');

    if ($tipo === 'insumo') {
        $request->validate([
            'insumo_id' => 'required',
            'area_id'   => 'required',
            'cantidad'  => 'required|integer|min:1',
        ]);

        $insumo = DB::table('insumos_medicos')->where('id', $request->insumo_id)->first();

        if (!$insumo) {
            return back()->with('error', 'El insumo médico seleccionado no existe.');
        }

        if ($insumo->cantidad_stock < $request->cantidad) {
            return back()->with('error', 'Stock insuficiente.');
        }

        $area = DB::table('areas')->where('id', $request->area_id)->first();
        $nombreArea = $area ? $area->nombre_area : 'Área no especificada';

        DB::transaction(function () use ($request, $insumo) {
            // Descontar del stock de insumos médicos
            DB::table('insumos_medicos')
                ->where('id', $request->insumo_id)
                ->decrement('cantidad_stock', $request->cantidad);

            // Estructura de inserción en retiros
            $insertData = [
                'insumo_id'      => $request->insumo_id,
                'medicamento_id' => null,
                'area_id'        => $request->area_id,
                'cantidad'       => $request->cantidad,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            if (Schema::hasColumn('retiros', 'lote_id')) {
                $insertData['lote_id'] = property_exists($insumo, 'lote_id') ? $insumo->lote_id : null;
            }

            DB::table('retiros')->insert($insertData);
        });

        $usuario = auth()->check() ? auth()->user()->name : 'Usuario';
        $hora = now()->format('h:i A');
        $this->registrarBitacora(
            'Retiro de Insumo',
            "Se retiraron {$request->cantidad} unidades del insumo '{$insumo->nombre_insumo}' para el área '{$nombreArea}' por {$usuario} a las {$hora}"
        );

        return back()->with('success', 'El retiro de insumo médico ha sido registrado con éxito.');

    } else {
        // Retiro de Medicamentos
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
            return back()->with('error', 'Stock insuficiente.');
        }

        $area = DB::table('areas')->where('id', $request->area_id)->first();
        $nombreArea = $area ? $area->nombre_area : 'Área no especificada';

        DB::transaction(function () use ($request, $medicamento) {
            DB::table('medicamentos')
                ->where('id', $request->medicamento_id)
                ->decrement('cantidad_stock', $request->cantidad);

            $insertData = [
                'medicamento_id' => $request->medicamento_id,
                'insumo_id'      => null,
                'area_id'        => $request->area_id,
                'cantidad'       => $request->cantidad,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            if (Schema::hasColumn('retiros', 'lote_id')) {
                $insertData['lote_id'] = $medicamento->lote_id ?? null;
            }

            DB::table('retiros')->insert($insertData);
        });

        $usuario = auth()->check() ? auth()->user()->name : 'Usuario';
        $hora = now()->format('h:i A');
        $this->registrarBitacora(
            'Retiro de Stock',
            "Se retiraron {$request->cantidad} unidades de '{$medicamento->nombre_medicamento}' para el área '{$nombreArea}' por {$usuario} a las {$hora}"
        );

        return back()->with('success', 'El retiro de medicamento ha sido registrado con éxito.');
    }
}

    private function registrarBitacora($accion, $descripcion)
    {
        try {
            $usuario = auth()->check() ? auth()->user()->name : 'Usuario';
            $tabla = Schema::hasTable('bitacoras') ? 'bitacoras' : (Schema::hasTable('bitacora') ? 'bitacora' : null);

            if ($tabla) {
                DB::table($tabla)->insert([
                    'modulo'      => 'Almacén',
                    'accion'      => $accion,
                    'descripcion' => $descripcion,
                    'usuario'     => $usuario,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al registrar en Bitácora: " . $e->getMessage());
        }
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

    $tabla = ($request->get('tabla') === 'insumos') ? 'insumos_medicos' : 'medicamentos';
    $updateData = [];

    if (!empty($request->codigo_lote)) {
        $codigo = trim($request->codigo_lote);
        $updateData['codigo_lote'] = $codigo;
        
        if (Schema::hasColumn($tabla, 'lote_id')) {
            $updateData['lote_id'] = LoteHelper::obtenerOCrearLoteId($codigo);
        }
    }

    if ($request->filled('cantidad_stock')) {
        $updateData['cantidad_stock'] = (int)$request->cantidad_stock;
    }

    // Ahora aplica tanto para medicamentos como para insumos médicos
    if ($request->filled('fecha_vencimiento')) {
        $updateData['fecha_vencimiento'] = $request->fecha_vencimiento;
    }

    if (empty($updateData)) {
        return back()->with('info', 'No se realizó ninguna modificación.');
    }

    $updateData['updated_at'] = now();

    DB::transaction(function () use ($tabla, $ids, $updateData) {
        DB::table($tabla)
            ->whereIn('id', $ids)
            ->update($updateData);
    });

    return back()->with('success', '¡Se han actualizado con éxito los ' . count($ids) . ' registros seleccionados!');
}

public function exportarPdf(Request $request)
{
    $categoria = $request->get('categoria', 'medicamento');
    $tipoInsumo = $request->get('tipo_insumo');

    if ($categoria === 'insumo') {
        $query = DB::table('insumos_medicos');
        if (!empty($tipoInsumo)) {
            $query->where('tipo_insumo', $tipoInsumo);
        }
        $items = $query->get();
        $titulo = 'REPORTE DE INVENTARIO - INSUMOS MÉDICOS';
    } else {
        $query = DB::table('medicamentos');
        if (!empty($tipoInsumo)) {
            $query->where('tipo_insumo', $tipoInsumo);
        }
        $items = $query->get();
        $titulo = 'REPORTE DE INVENTARIO - MEDICAMENTOS';
    }

    return view('almacen.pdf', compact('items', 'titulo', 'categoria'));
}

public function pdf()
{
    $hasInsumoId = Schema::hasColumn('retiros', 'insumo_id');

    if ($hasInsumoId) {
        $ultimosRetiros = DB::table('retiros')
            ->leftJoin('medicamentos', 'retiros.medicamento_id', '=', 'medicamentos.id')
            ->leftJoin('insumos_medicos', 'retiros.insumo_id', '=', 'insumos_medicos.id')
            ->join('areas', 'retiros.area_id', '=', 'areas.id')
            ->select(
                'retiros.id',
                'medicamentos.nombre_medicamento',
                'insumos_medicos.nombre_insumo',
                'areas.nombre_area',
                'retiros.cantidad',
                'retiros.created_at',
                DB::raw("CASE 
                    WHEN retiros.insumo_id IS NOT NULL THEN 'insumo' 
                    ELSE 'medicamento' 
                END as tipo_item")
            )
            ->where(function($query) {
                // Asegurar que solo traiga registros donde al menos uno de los dos exista en la tabla
                $query->whereNotNull('medicamentos.id')
                      ->orWhereNotNull('insumos_medicos.id');
            })
            ->orderBy('retiros.created_at', 'desc')
            ->get();
    } else {
        $ultimosRetiros = DB::table('retiros')
            ->join('medicamentos', 'retiros.medicamento_id', '=', 'medicamentos.id')
            ->join('areas', 'retiros.area_id', '=', 'areas.id')
            ->select(
                'retiros.id',
                'medicamentos.nombre_medicamento',
                'areas.nombre_area',
                'retiros.cantidad',
                'retiros.created_at',
                DB::raw("'medicamento' as tipo_item")
            )
            ->whereNotNull('medicamentos.id')
            ->orderBy('retiros.created_at', 'desc')
            ->get();
    }

    $todosLosInsumos = DB::table('insumos_medicos')->get();

    if (view()->exists('almacen.retiros.pdf')) {
        return view('almacen.retiros.pdf', compact('ultimosRetiros', 'todosLosInsumos'));
    }

    return view('almacen.pdf', compact('ultimosRetiros', 'todosLosInsumos'));
}
}

