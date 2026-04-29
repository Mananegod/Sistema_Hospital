<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MedicamentosImport;

class AlmacenController extends Controller
{
    public function index(Request $request)
    {
        $areas = DB::table('areas')->get();

        // Necesario para el select del formulario lateral
        $todosLosMedicamentos = DB::table('medicamentos')->orderBy('nombre', 'asc')->get();

        $inventario = DB::table('inventarios')
            ->join('medicamentos', 'inventarios.medicamento_id', '=', 'medicamentos.id')
            ->join('areas', 'inventarios.area_id', '=', 'areas.id')
            ->select(
                'medicamentos.id as medicamento_id',
                'medicamentos.nombre as medicamento',
                'medicamentos.presentacion',
                'areas.nombre_area as area',
                'inventarios.stock_actual',
                'medicamentos.stock_minimo'
            )
            ->when($request->area_id, function ($query, $area_id) {
                return $query->where('inventarios.area_id', $area_id);
            })
            ->get();

        return view('almacen.index', compact('inventario', 'areas', 'todosLosMedicamentos'));
    }

    public function entradaRapida(Request $request)
    {
        $request->validate([
            'medicamento_id' => 'required|exists:medicamentos,id',
            'cantidad' => 'required|integer|min:1',
            'area_id' => 'required|exists:areas,id'
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('inventarios')->updateOrInsert(
                    ['medicamento_id' => $request->medicamento_id, 'area_id' => $request->area_id],
                    [
                        'stock_actual' => DB::raw("stock_actual + " . (int)$request->cantidad), 
                        'updated_at' => now()
                    ]
                );

                DB::table('movimientos')->insert([
                    'medicamento_id' => $request->medicamento_id,
                    'area_id'        => $request->area_id,
                    'cantidad'       => $request->cantidad,
                    'created_at'     => now(),
                    'updated_at'     => now()
                ]);
            });

            return back()->with('success', 'Stock actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function importarExcel(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls,csv',
            'area_id' => 'required|exists:areas,id'
        ]);

        try {
            Excel::import(new MedicamentosImport($request->area_id), $request->file('archivo'));
            return back()->with('success', '¡Inventario actualizado con éxito!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error en la importación: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la vista principal de retiros con el historial del día.
     */
    public function indexRetiros()
    {
        // 1. Obtenemos las áreas para el select
        $areas = DB::table('areas')->get();

        // 2. Obtenemos todos los medicamentos para el select
        $todosLosMedicamentos = DB::table('medicamentos')->orderBy('nombre', 'asc')->get();

        // 3. Obtenemos los retiros realizados hoy para la tabla
        $ultimosRetiros = DB::table('movimientos')
            ->join('medicamentos', 'movimientos.medicamento_id', '=', 'medicamentos.id')
            ->join('areas', 'movimientos.area_id', '=', 'areas.id')
            ->select(
                'medicamentos.nombre',
                'areas.nombre_area',
                'movimientos.cantidad',
                'movimientos.created_at'
            )
            ->where('movimientos.tipo_movimiento', 'SALIDA')
            ->whereDate('movimientos.created_at', now()->toDateString())
            ->orderBy('movimientos.created_at', 'desc')
            ->get();

        return view('almacen.retiros', compact('areas', 'todosLosMedicamentos', 'ultimosRetiros'));
    }
}