<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use Illuminate\Http\Request;

class MedicamentoController extends Controller
{
    public function index()
    {
        $medicamentos = Medicamento::all();
        $areas = \DB::table('areas')->get();
     return view('welcome', compact('medicamentos', 'areas'));
    }

    public function store(Request $request)
{
    $request->validate([
        'nombre_medicamento' => 'required',
        'cantidad_stock' => 'required|integer',
        'area_destino' => 'required',
        'fecha_vencimiento' => 'required|date',
    ], [
        'nombre_medicamento.required' => 'El nombre del medicamento es obligatorio.',
        'cantidad_stock.required' => 'La cantidad en stock es obligatoria.',
        'cantidad_stock.integer' => 'La cantidad debe ser un número entero.',
        'area_destino.required' => 'Debe seleccionar un área de destino.',
        'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
        'fecha_vencimiento.date' => 'La fecha de vencimiento no es válida.',
    ]);

    $status = $request->cantidad_stock > 0 ? 'Disponible' : 'Agotado';

    // ASIGNACIÓN MANUAL PARA EVITAR EL ERROR NOT NULL
    // Aquí forzamos que el dato de 'nombre_medicamento' llene la columna 'nombre'
    \App\Models\Medicamento::create([
        'nombre'             => $request->nombre_medicamento, // <--- Esto arregla el error
        'nombre_medicamento' => $request->nombre_medicamento,
        'cantidad_stock'     => $request->cantidad_stock,
        'area_destino'       => $request->area_destino,
        'fecha_vencimiento'  => $request->fecha_vencimiento,
        'status_disponibilidad' => $status
    ]);

    return redirect()->route('medicamentos.index')->with('success', 'Medicamento registrado correctamente');
}

    public function destroy($id)
    {
        $medicamento = Medicamento::findOrFail($id);
        $medicamento->delete();
        return redirect()->back()->with('success', 'Medicamento eliminado correctamente');
    }

    public function update(Request $request, $id)
    {
        $medicamento = Medicamento::findOrFail($id);
        $medicamento->update($request->all());
        return redirect()->back()->with('success', 'Medicamento actualizado correctamente');
    }
}