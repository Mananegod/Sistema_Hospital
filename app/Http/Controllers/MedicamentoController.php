<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use Illuminate\Http\Request;

class MedicamentoController extends Controller
{
    public function index()
    {
        // Traemos los datos ordenados por los más recientes
        $medicamentos = Medicamento::latest()->get();
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
        ]);

        $status = $request->cantidad_stock > 0 ? 'Disponible' : 'Agotado';

        Medicamento::create($request->all() + ['status_disponibilidad' => $status]);

        return redirect('/')->with('success', 'Registrado correctamente');
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
    return redirect()->back()->with('success', 'Medicamento actualizado');
    }
}