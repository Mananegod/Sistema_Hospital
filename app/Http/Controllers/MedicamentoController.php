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
    // 2. VALIDACIÓN: Evitar fechas desubicadas (Obligatorio desde hoy hasta el 31 de Diciembre de 2036)
    $request->validate([
        'nombre_medicamento' => 'required',
        'cantidad_stock'     => 'required|integer',
        'area_destino'       => 'required',
        'fecha_vencimiento'  => 'required|date|before_or_equal:2036-12-31',
    ], [
        'nombre_medicamento.required' => 'El nombre del medicamento es obligatorio.',
        'cantidad_stock.required'     => 'La cantidad en stock es obligatoria.',
        'cantidad_stock.integer'      => 'La cantidad debe ser un número entero.',
        'area_destino.required'       => 'Debe seleccionar un área de destino.',
        'fecha_vencimiento.required'  => 'La fecha de vencimiento es obligatoria.',
        'fecha_vencimiento.date'      => 'La fecha de vencimiento no es válida.',
        'fecha_vencimiento.before_or_equal' => 'La fecha de vencimiento no puede ser mayor al año 2036.',
    ]);

    $status = $request->cantidad_stock > 0 ? 'Disponible' : 'Agotado';

    // 1. MAYÚSCULAS: Convertimos el nombre y el lote a mayúsculas usando mb_strtoupper (soporta eñes y acentos)
    $nombreMayusculas = mb_strtoupper($request->nombre_medicamento, 'UTF-8');
    
    // Capturamos el lote del request (o el por defecto) y lo pasamos a mayúsculas
    $loteInput = $request->codigo_lote ?? 'LOTE-NUEVO';
    $loteMayusculas = mb_strtoupper($loteInput, 'UTF-8');

    // Inserción en la base de datos con los datos limpios
    \App\Models\Medicamento::create([
        'nombre'                => $nombreMayusculas,
        'nombre_medicamento'    => $nombreMayusculas,
        'cantidad_stock'        => $request->cantidad_stock,
        'area_destino'          => $request->area_destino,
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

    // 1. VALIDACIÓN: Obligatorio y fecha máxima año 2036
    $request->validate([
        'nombre_medicamento' => 'required',
        'cantidad_stock'     => 'required|integer',
        'area_destino'       => 'required',
        'fecha_vencimiento'  => 'required|date|before_or_equal:2036-12-31',
    ], [
        'nombre_medicamento.required' => 'El nombre del medicamento es obligatorio.',
        'cantidad_stock.required'     => 'La cantidad en stock es obligatoria.',
        'cantidad_stock.integer'      => 'La cantidad debe ser un número entero.',
        'area_destino.required'       => 'Debe seleccionar un área de destino.',
        'fecha_vencimiento.required'  => 'La fecha de vencimiento es obligatoria.',
        'fecha_vencimiento.date'      => 'La fecha de vencimiento no es válida.',
        'fecha_vencimiento.before_or_equal' => 'La fecha de vencimiento no puede ser mayor al año 2036.',
    ]);

    $status = $request->cantidad_stock > 0 ? 'Disponible' : 'Agotado';

    // 2. FORMATEO A MAYÚSCULAS (Soporta acentos y Ñ)
    $nombreMayusculas = mb_strtoupper($request->nombre_medicamento, 'UTF-8');
    
    $loteInput = $request->codigo_lote ?? 'LOTE-NUEVO';
    $loteMayusculas = mb_strtoupper($loteInput, 'UTF-8');

    // 3. ACTUALIZACIÓN MANUAL MAPEANDO LAS COLUMNAS CORRECTAS DE LA BD
    $medicamento->update([
        'nombre'                => $nombreMayusculas,
        'nombre_medicamento'    => $nombreMayusculas,
        'cantidad_stock'        => $request->cantidad_stock,
        'area_destino'          => $request->area_destino,
        'fecha_vencimiento'     => $request->fecha_vencimiento,
        'status_disponibilidad' => $status,
        'codigo_lote'           => $loteMayusculas,
    ]);

    // Redirecciona de vuelta con el mensaje de éxito
    return redirect()->route('medicamentos.index')->with('success', 'Medicamento actualizado correctamente');
}
}