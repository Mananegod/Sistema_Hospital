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

        $inventario = DB::table('medicamentos')
            ->select(
                'id as medicamento_id',
                'nombre_medicamento as medicamento',
                'presentacion',
                'cantidad_stock as stock_actual',
                'stock_minimo',
                'area_destino'
            )
            ->when($request->area_id, function ($query, $area_id) {
                $areaDestino = DB::table('areas')->where('id', $area_id)->value('nombre_area');
                return $areaDestino ? $query->where('area_destino', $areaDestino) : $query;
            })
            ->orderBy('nombre_medicamento')
            ->paginate(50)
            ->appends($request->query());

        return view('almacen.index', compact('inventario', 'areas'));
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

        if (! $areaDestino) {
            return back()->with('error', 'El área destino seleccionada no es válida.');
        }

        $medicamento = DB::table('medicamentos')
            ->where('id', $request->medicamento_id)
            ->where('area_destino', $areaDestino) 
            ->first();

        if (! $medicamento) {
            return back()->with('error', 'El medicamento seleccionado no pertenece al área destino indicada.');
        }

        DB::table('medicamentos')
            ->where('id', $request->medicamento_id)
            ->increment('cantidad_stock', $request->cantidad);

        return back()->with('success', "Se han sumado {$request->cantidad} unidades a {$medicamento->nombre_medicamento}.");
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

            $import = new MedicamentosImport($request->area_id);

            Excel::import(
                $import,
                $request->file('archivo')
            );

            return redirect()->route('almacen.index')->with('success', '¡Inventario actualizado con éxito!');
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

    return back()->with('success', "¡Éxito! Se ha actualizado la fecha de vencimiento de {$afectados} medicamentos en el área '{$areaDestino}'.");
}
}