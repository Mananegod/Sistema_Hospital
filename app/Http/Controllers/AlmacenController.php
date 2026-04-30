<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MedicamentosImport;
use Illuminate\Support\Facades\Schema;

class AlmacenController extends Controller
{
    public function index(Request $request)
    {
        $areas = DB::table('areas')->get();
        $todosLosMedicamentos = DB::table('medicamentos')->orderBy('nombre_medicamento', 'asc')->get();

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
                return $query->where('area_destino', $area_id);
            })
            ->get();

        return view('almacen.index', compact('inventario', 'areas', 'todosLosMedicamentos'));
    }

    /**
     * Método para sumar stock manualmente (Entrada Rápida)
     */
    public function entradaRapida(Request $request)
    {
        $request->validate([
            'medicamento_id' => 'required',
            'area_id' => 'required',
            'cantidad' => 'required|integer|min:1'
        ]);

        // Buscamos el medicamento que coincida con el ID y el Área seleccionada
        $medicamento = DB::table('medicamentos')
            ->where('id', $request->medicamento_id)
            ->where('area_destino', $request->area_id)
            ->first();

        // Si no existe el medicamento en esa área, lanzamos error
        if (!$medicamento) {
            return back()->with('error', 'El medicamento seleccionado no pertenece al área destino indicada.');
        }

        // Si existe, sumamos el stock
        DB::table('medicamentos')
            ->where('id', $request->medicamento_id)
            ->increment('cantidad_stock', $request->cantidad);

        return back()->with('success', "Se han sumado {$request->cantidad} unidades a {$medicamento->nombre_medicamento}.");
    }

    public function importarExcel(Request $request)
    {
        $request->validate([
            'area_id' => 'required',
            'archivo' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new MedicamentosImport($request->area_id), $request->file('archivo'));
            return back()->with('success', '¡Inventario actualizado con éxito!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error en la importación: ' . $e->getMessage());
        }
    }

    public function indexRetiros()
    {
        $areas = DB::table('areas')->get();
        $todosLosMedicamentos = DB::table('medicamentos')->orderBy('nombre_medicamento', 'asc')->get();

        $ultimosRetiros = [];
        if (Schema::hasTable('movimientos')) {
            $ultimosRetiros = DB::table('movimientos')
                ->join('medicamentos', 'movimientos.medicamento_id', '=', 'medicamentos.id')
                ->join('areas', 'movimientos.area_id', '=', 'areas.id')
                ->select(
                    'medicamentos.nombre_medicamento as nombre',
                    'areas.nombre_area',
                    'movimientos.cantidad',
                    'movimientos.created_at'
                )
                ->where('movimientos.tipo_movimiento', 'SALIDA')
                ->whereDate('movimientos.created_at', now()->toDateString())
                ->orderBy('movimientos.created_at', 'desc')
                ->get();
        }

        return view('almacen.retiros', compact('areas', 'todosLosMedicamentos', 'ultimosRetiros'));
    }
}