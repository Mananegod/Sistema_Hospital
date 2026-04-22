<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use Illuminate\Http\Request;

class MedicamentoController extends Controller
{
    public function index()
    {
        $medicamentos = Medicamento::all();
        return view('welcome', compact('medicamentos'));
    }

    public function store(Request $request)
    {
          $request->validate([
        'codigo_lote' => 'required',
        'nombre_medicamento' => 'required',
        'cantidad_stock' => 'required|integer',
        'area_destino' => 'required',
        'fecha_vencimiento' => 'required|date',
    ], [
        'codigo_lote.required' => 'El código de lote es obligatorio.',
        'nombre_medicamento.required' => 'El nombre del medicamento es obligatorio.',
        'cantidad_stock.required' => 'La cantidad en stock es obligatoria.',
        'cantidad_stock.integer' => 'La cantidad debe ser un número entero.',
        'area_destino.required' => 'Debe seleccionar un área de destino.',
        'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
        'fecha_vencimiento.date' => 'La fecha de vencimiento no es válida.',
    ]);

        $status = $request->cantidad_stock > 0 ? 'Disponible' : 'Agotado';

        Medicamento::create($request->all() + ['status_disponibilidad' => $status]);

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