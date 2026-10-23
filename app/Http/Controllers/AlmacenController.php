<?php

namespace App\Http\Controllers;

use App\Imports\MedicamentosImport;
use App\Imports\InsumosImport;
use App\Helpers\LoteHelper;
use App\Models\Auditoria; // <-- Importante: Asegúrate de importar el modelo Auditoria
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Carbon\Carbon;

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

        // Query 1: Medicamentos (Ordenados por fecha de vencimiento más próxima primero)
        $inventario = DB::table('medicamentos')
            ->select(
                'id as medicamento_id',
                'nombre_medicamento as medicamento',
                'presentacion',
                'cantidad_stock as stock_actual',
                'stock_minimo',
                'tipo_insumo',
                'codigo_lote',
                'fecha_vencimiento'
            )
            ->when($request->tipo_insumo, function ($query, $tipo_insumo) {
                return $query->where('tipo_insumo', $tipo_insumo);
            })
            ->orderByRaw('fecha_vencimiento IS NULL ASC')
            ->orderBy('fecha_vencimiento', 'asc')
            ->orderBy('nombre_medicamento')
            ->paginate(50)
            ->appends($request->query());

        // Query 2: Insumos Médicos (Ordenados por fecha de vencimiento más próxima primero)
        $insumos = DB::table('insumos_medicos')
            ->when($request->tipo_insumo, function ($query, $tipo_insumo) {
                return $query->where('tipo_insumo', $tipo_insumo);
            })
            ->orderByRaw('fecha_vencimiento IS NULL ASC')
            ->orderBy('fecha_vencimiento', 'asc')
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

            // Registro en Auditoría/Bitácora exactamente igual al módulo de personal
            Auditoria::create([
                'modulo'      => 'Almacén',
                'accion'      => 'Importación',
                'descripcion' => 'Se importó un archivo masivo de insumos médicos mediante Excel.',
            ]);

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

        // Registro en Bitácora
        Auditoria::create([
            'modulo'      => 'Almacén',
            'accion'      => 'Entrada Rápida',
            'descripcion' => "Se sumaron {$request->cantidad} unidades al medicamento: {$medicamento->nombre_medicamento}",
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

            // Registro en Bitácora
            Auditoria::create([
                'modulo'      => 'Almacén',
                'accion'      => 'Importación',
                'descripcion' => 'Se importó el archivo masivo F15 de medicamentos.',
            ]);

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

    // Inicializamos como colección vacía por defecto para usuarios normales
    $solicitudesPendientes = collect();

    // VERIFICAR SI EL USUARIO ES ADMINISTRADOR
    // (Ajusta la condición 'nombre === "Admin"' o tu campo de rol según tengas estructurada tu tabla users, ej: auth()->user()->rol == 'admin')
if (auth()->check() && auth()->user()->nombre === 'Admin') {
        
        $queryPendientes = DB::table('retiros')
            ->leftJoin('medicamentos', 'retiros.medicamento_id', '=', 'medicamentos.id')
            ->leftJoin('insumos_medicos', 'retiros.insumo_id', '=', 'insumos_medicos.id')
            ->join('areas', 'retiros.area_id', '=', 'areas.id')
            ->select(
                'retiros.id',
                'retiros.cantidad',
                'retiros.created_at',
                'areas.nombre_area',
                DB::raw("COALESCE(medicamentos.nombre_medicamento, insumos_medicos.nombre_insumo, 'Desconocido') as nombre"),
                DB::raw("CASE WHEN retiros.insumo_id IS NOT NULL THEN 'Insumo' ELSE 'Medicamento' END as tipo_item"),
                DB::raw("COALESCE(medicamentos.cantidad_stock, insumos_medicos.cantidad_stock, 0) as stock_disponible")
            )
            ->where('retiros.estado', 'pendiente')
            ->orderBy('retiros.created_at', 'desc');

        $solicitudesPendientes = $queryPendientes->get();
    }

    // Historial de retiros aprobados de hoy (se mantiene igual...)
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
                'retiros.estado',
                DB::raw("CASE WHEN retiros.insumo_id IS NOT NULL THEN 'Insumo' ELSE 'Medicamento' END as tipo_item")
            )
            ->where('retiros.estado', 'aprobado')
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
                'retiros.estado',
                DB::raw("'Medicamento' as tipo_item")
            )
            ->where('retiros.estado', 'aprobado')
            ->whereDate('retiros.created_at', now()->toDateString())
            ->orderBy('retiros.created_at', 'desc')
            ->get();
    }

    return view('almacen.retiros', compact('areas', 'todosLosMedicamentos', 'todosLosInsumos', 'ultimosRetiros', 'solicitudesPendientes'));
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

        if (!empty($insumo->fecha_vencimiento) && Carbon::parse($insumo->fecha_vencimiento)->isPast()) {
            return back()->with('error', 'ACCESO DENEGADO: El insumo médico se encuentra CADUCADO.');
        }

        // Se guarda como pendiente sin descontar stock
        DB::table('retiros')->insert([
            'insumo_id'      => $request->insumo_id,
            'medicamento_id' => null,
            'area_id'        => $request->area_id,
            'cantidad'       => $request->cantidad,
            'estado'         => 'pendiente',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        Auditoria::create([
            'modulo'      => 'Almacén',
            'accion'      => 'Solicitud Retiro Insumo',
            'descripcion' => "Solicitud pendiente de retiro de {$request->cantidad} unidades de '{$insumo->nombre_insumo}'.",
        ]);

        return back()->with('success', 'Solicitud de retiro enviada. Esperando aprobación del administrador.');

    } else {
        $request->validate([
            'medicamento_id' => 'required',
            'area_id'        => 'required',
            'cantidad'       => 'required|integer|min:1',
        ]);

        $medicamento = DB::table('medicamentos')->where('id', $request->medicamento_id)->first();
        if (!$medicamento) {
            return back()->with('error', 'El medicamento seleccionado no existe.');
        }

        if (!empty($medicamento->fecha_vencimiento) && Carbon::parse($medicamento->fecha_vencimiento)->isPast()) {
            return back()->with('error', 'ACCESO DENEGADO: El medicamento se encuentra CADUCADO.');
        }

        DB::table('retiros')->insert([
            'medicamento_id' => $request->medicamento_id,
            'insumo_id'      => null,
            'area_id'        => $request->area_id,
            'cantidad'       => $request->cantidad,
            'estado'         => 'pendiente',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        Auditoria::create([
            'modulo'      => 'Almacén',
            'accion'      => 'Solicitud Retiro Medicamento',
            'descripcion' => "Solicitud pendiente de retiro de {$request->cantidad} unidades de '{$medicamento->nombre_medicamento}'.",
        ]);

        return back()->with('success', 'Solicitud de retiro enviada. Esperando aprobación del administrador.');
    }
}

public function aprobarRetiro($id)
{
    $retiro = DB::table('retiros')->where('id', $id)->where('estado', 'pendiente')->first();
    if (!$retiro) {
        return back()->with('error', 'La solicitud no existe o ya fue procesada.');
    }

    if (!empty($retiro->medicamento_id)) {
        $item = DB::table('medicamentos')->where('id', $retiro->medicamento_id)->first();
        $tabla = 'medicamentos';
        $itemId = $retiro->medicamento_id;
        $nombreItem = $item->nombre_medicamento ?? 'Medicamento';
    } else {
        $item = DB::table('insumos_medicos')->where('id', $retiro->insumo_id)->first();
        $tabla = 'insumos_medicos';
        $itemId = $retiro->insumo_id;
        $nombreItem = $item->nombre_insumo ?? 'Insumo';
    }

    if (!$item || $item->cantidad_stock < $retiro->cantidad) {
        return back()->with('error', 'Stock insuficiente para aprobar esta solicitud.');
    }

    DB::transaction(function () use ($tabla, $itemId, $retiro, $id) {
        DB::table($tabla)->where('id', $itemId)->decrement('cantidad_stock', $retiro->cantidad);
        DB::table('retiros')->where('id', $id)->update([
            'estado' => 'aprobado',
            'updated_at' => now()
        ]);
    });

    Auditoria::create([
        'modulo'      => 'Almacén',
        'accion'      => 'Aprobación de Retiro',
        'descripcion' => "El administrador aprobó el retiro de {$retiro->cantidad} unidades de '{$nombreItem}'.",
    ]);

    return back()->with('success', 'Retiro aprobado y stock descontado con éxito.');
}

public function rechazarRetiro($id)
{
    $retiro = DB::table('retiros')->where('id', $id)->where('estado', 'pendiente')->first();
    if (!$retiro) {
        return back()->with('error', 'La solicitud no existe o ya fue procesada.');
    }

    DB::table('retiros')->where('id', $id)->update([
        'estado' => 'rechazado',
        'updated_at' => now()
    ]);

    Auditoria::create([
        'modulo'      => 'Almacén',
        'accion'      => 'Rechazo de Retiro',
        'descripcion' => "El administrador rechazó la solicitud de retiro ID {$id}.",
    ]);

    return back()->with('success', 'La solicitud de retiro ha sido rechazada.');
}

public function editarYAprobarRetiro(Request $request, $id)
{
    $request->validate([
        'cantidad' => 'required|integer|min:1'
    ]);

    $retiro = DB::table('retiros')->where('id', $id)->where('estado', 'pendiente')->first();
    if (!$retiro) {
        return back()->with('error', 'Solicitud no encontrada.');
    }

    $nuevaCantidad = (int)$request->cantidad;

    if (!empty($retiro->medicamento_id)) {
        $item = DB::table('medicamentos')->where('id', $retiro->medicamento_id)->first();
        $tabla = 'medicamentos';
        $itemId = $retiro->medicamento_id;
        $nombreItem = $item->nombre_medicamento ?? 'Medicamento';
    } else {
        $item = DB::table('insumos_medicos')->where('id', $retiro->insumo_id)->first();
        $tabla = 'insumos_medicos';
        $itemId = $retiro->insumo_id;
        $nombreItem = $item->nombre_insumo ?? 'Insumo';
    }

    if (!$item || $item->cantidad_stock < $nuevaCantidad) {
        return back()->with('error', 'Stock insuficiente para la nueva cantidad.');
    }

    DB::transaction(function () use ($tabla, $itemId, $nuevaCantidad, $id) {
        DB::table($tabla)->where('id', $itemId)->decrement('cantidad_stock', $nuevaCantidad);
        DB::table('retiros')->where('id', $id)->update([
            'cantidad' => $nuevaCantidad,
            'estado' => 'aprobado',
            'updated_at' => now()
        ]);
    });

    Auditoria::create([
        'modulo'      => 'Almacén',
        'accion'      => 'Edición y Aprobación de Retiro',
        'descripcion' => "El administrador ajustó la cantidad a {$nuevaCantidad} y aprobó el retiro de '{$nombreItem}'.",
    ]);

    return back()->with('success', 'Retiro ajustado, aprobado y stock descontado con éxito.');
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
            ->where(DB::raw('LOWER(codigo_lote)'), '=', strtolower($codigo_lote))
            ->orderBy('nombre_medicamento')
            ->get();

        $insumos = DB::table('insumos_medicos')
            ->where(DB::raw('LOWER(codigo_lote)'), '=', strtolower($codigo_lote))
            ->orderBy('nombre_insumo')
            ->get();

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

        // Registro en Bitácora
        Auditoria::create([
            'modulo'      => 'Almacén',
            'accion'      => 'Edición Masiva',
            'descripcion' => "Se actualizaron masivamente " . count($ids) . " registros en la tabla {$tabla}.",
        ]);

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

        // Registro en Bitácora
        Auditoria::create([
            'modulo'      => 'Almacén',
            'accion'      => 'Reporte',
            'descripcion' => "Se generó un reporte PDF de inventario para la categoría: {$categoria}",
        ]);

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

    public function generarActaVencido(Request $request)
    {
        $tipo = $request->get('tipo');
        $id = $request->get('id');
        $cantidad = $request->get('cantidad');
        $areaId = $request->get('area_id');

        $tabla = ($tipo === 'insumo') ? 'insumos_medicos' : 'medicamentos';
        $columnaNombre = ($tipo === 'insumo') ? 'nombre_insumo' : 'nombre_medicamento';

        $item = DB::table($tabla)->where('id', $id)->first();
        $area = DB::table('areas')->where('id', $areaId)->first();

        if (!$item) {
            return redirect()->route('almacen.retiros.index')->with('error', 'Registro no encontrado.');
        }

        $nombreItem = $item->{$columnaNombre} ?? 'Desconocido';
        $codigoLote = $item->codigo_lote ?? 'S/L';
        $fechaVencimiento = $item->fecha_vencimiento ?? 'No especificada';
        $nombreArea = $area->nombre_area ?? 'Área General';

        return view('almacen.acta_vencido_pdf', compact(
            'tipo', 'nombreItem', 'codigoLote', 'fechaVencimiento', 'cantidad', 'nombreArea'
        ));
    }

public function registrarBajaVencidosMasiva(Request $request)
{
    $request->validate([
        'ids' => 'required',
        'tabla' => 'required|in:medicamentos,insumos_medicos',
        'motivo' => 'required|string|max:255',
    ]);

    $ids = json_decode($request->ids, true);
    if (empty($ids)) {
        return back()->with('error', 'No se seleccionó ningún registro para dar de baja.');
    }

    $tabla = $request->tabla;
    $tipoItem = ($tabla === 'insumos_medicos') ? 'insumo' : 'medicamento';
    $columnaNombre = ($tabla === 'insumos_medicos') ? 'nombre_insumo' : 'nombre_medicamento';
    $idColumnaRetiro = ($tabla === 'insumos_medicos') ? 'insumo_id' : 'medicamento_id';

    // Obtener los elementos seleccionados que tengan stock disponible
    $itemsBaja = DB::table($tabla)
        ->whereIn('id', $ids)
        ->where('cantidad_stock', '>', 0)
        ->get();

    if ($itemsBaja->isEmpty()) {
        return back()->with('error', 'OPERACIÓN RECHAZADA: Ninguno de los elementos seleccionados tiene stock disponible (> 0) para desincorporar.');
    }

    try {
        DB::transaction(function () use ($itemsBaja, $tabla, $tipoItem, $columnaNombre, $idColumnaRetiro, $request) {
            foreach ($itemsBaja as $item) {
                
                // Sanitizar valores vacíos para evitar errores en columnas tipo DATE o restricciones
                $fechaVencimiento = (!empty($item->fecha_vencimiento)) ? $item->fecha_vencimiento : null;
                $codigoLote = (!empty(trim($item->codigo_lote ?? ''))) ? trim($item->codigo_lote) : 'S/L';

                // 1. Insertar el registro en la tabla de desecho / bajas
                DB::table('bajas_inventario')->insert([
                    'tipo_item'         => $tipoItem,
                    'nombre_item'       => $item->{$columnaNombre},
                    'codigo_lote'       => $codigoLote,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'cantidad'          => $item->cantidad_stock,
                    'motivo'            => $request->motivo,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // 2. En lugar de eliminar físicamente (delete()), actualizamos el stock a 0 
                // para preservar el ID y que los retiros sigan encontrando su nombre correctamente.
                DB::table($tabla)->where('id', $item->id)->update([
                    'cantidad_stock' => 0,
                    'updated_at'     => now(),
                ]);

                // 3. Rechazar automáticamente solicitudes pendientes de este ítem si las hubiera
                DB::table('retiros')
                    ->where($idColumnaRetiro, $item->id)
                    ->where('estado', 'pendiente')
                    ->update([
                        'estado' => 'rechazado',
                        'updated_at' => now(),
                    ]);

                // 4. Registrar en la bitácora del sistema
                Auditoria::create([
                    'modulo'      => 'Almacén',
                    'accion'      => 'Baja y Desecho de Inventario',
                    'descripcion' => "Se desincorporaron y enviaron a desecho {$item->cantidad_stock} unidades de '{$item->{$columnaNombre}}' (Lote: {$codigoLote}). Motivo: {$request->motivo}",
                ]);
            }
        });

        return back()->with('success', 'Los elementos seleccionados fueron dados de baja, su stock se actualizó a 0 y se trasladaron formalmente al registro de desecho.');

    } catch (\Exception $e) {
        return back()->with('error', 'Error al procesar la baja de inventario: ' . $e->getMessage());
    }
}

    public function indexDesechos()
    {
        $desechos = DB::table('bajas_inventario')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('almacen.desechos', compact('desechos'));
    }
}