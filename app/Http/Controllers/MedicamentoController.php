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
        'cantidad_stock'     => 'required|integer',
        'fecha_vencimiento'  => 'required|date|before_or_equal:2036-12-31',
    ], [
        'nombre_medicamento.required' => 'El nombre del medicamento es obligatorio.',
        'cantidad_stock.required'     => 'La cantidad en stock es obligatoria.',
        'cantidad_stock.integer'      => 'La cantidad debe ser un número entero.',
        'fecha_vencimiento.required'  => 'La fecha de vencimiento es obligatoria.',
        'fecha_vencimiento.date'      => 'La fecha de vencimiento no es válida.',
        'fecha_vencimiento.before_or_equal' => 'La fecha de vencimiento no puede ser mayor al año 2036.',
    ]);

    $nombreMayusculas = mb_strtoupper($request->nombre_medicamento, 'UTF-8');
    $loteInput = $request->codigo_lote ?? 'LOTE-NUEVO';
    $loteMayusculas = mb_strtoupper($loteInput, 'UTF-8');

    $existente = \App\Models\Medicamento::where('nombre_medicamento', $nombreMayusculas)->first();

    if ($existente && !$request->has('confirmar_incremento')) {
        return redirect()->back()->with('duplicado_detectado', [
            'id'                 => $existente->id,
            'nombre_medicamento' => $nombreMayusculas,
            'cantidad_stock'     => $request->cantidad_stock,
            'fecha_vencimiento'  => $request->fecha_vencimiento,
            'codigo_lote'        => $loteMayusculas,
        ]);
    }

    if ($existente && $request->has('confirmar_incremento')) {
        $nuevoStock = $existente->cantidad_stock + $request->cantidad_stock;
        $status = $nuevoStock > 0 ? 'Disponible' : 'Agotado';

        $existente->update([
            'cantidad_stock'        => $nuevoStock,
            'fecha_vencimiento'     => $request->fecha_vencimiento,
            'status_disponibilidad' => $status,
            'codigo_lote'           => $loteMayusculas,
        ]);

        return redirect()->route('medicamentos.index')->with('success', 'Stock incrementado exitosamente.');
    }

    $status = $request->cantidad_stock > 0 ? 'Disponible' : 'Agotado';

    \App\Models\Medicamento::create([
        'nombre'                => $nombreMayusculas,
        'nombre_medicamento'    => $nombreMayusculas,
        'cantidad_stock'        => $request->cantidad_stock,
        'fecha_vencimiento'     => $request->fecha_vencimiento,
        'status_disponibilidad' => $status,
        'codigo_lote'           => $loteMayusculas,
        'stock_minimo'          => $request->stock_minimo ?? 10,
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

        $request->validate([
            'nombre_medicamento' => 'required',
            'cantidad_stock'     => 'required|integer',
            'fecha_vencimiento'  => 'required|date|before_or_equal:2036-12-31',
        ], [
            'nombre_medicamento.required' => 'El nombre del medicamento es obligatorio.',
            'cantidad_stock.required'     => 'La cantidad en stock es obligatoria.',
            'cantidad_stock.integer'      => 'La cantidad debe ser un número entero.',
            'fecha_vencimiento.required'  => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.date'      => 'La fecha de vencimiento no es válida.',
            'fecha_vencimiento.before_or_equal' => 'La fecha de vencimiento no puede ser mayor al año 2036.',
        ]);

        $status = $request->cantidad_stock > 0 ? 'Disponible' : 'Agotado';
        $nombreMayusculas = mb_strtoupper($request->nombre_medicamento, 'UTF-8');
        $loteInput = $request->codigo_lote ?? 'LOTE-NUEVO';
        $loteMayusculas = mb_strtoupper($loteInput, 'UTF-8');

        $medicamento->update([
            'nombre'                => $nombreMayusculas,
            'nombre_medicamento'    => $nombreMayusculas,
            'cantidad_stock'        => $request->cantidad_stock,
            'fecha_vencimiento'     => $request->fecha_vencimiento,
            'status_disponibilidad' => $status,
            'codigo_lote'           => $loteMayusculas,
        ]);

        return redirect()->route('medicamentos.index')->with('success', 'Medicamento actualizado correctamente');
    }
}