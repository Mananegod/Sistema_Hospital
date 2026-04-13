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
        // 1. Validamos que lleguen todos los campos del formulario
        $request->validate([
            'codigo_lote' => 'required',
            'nombre_medicamento' => 'required',
            'cantidad_stock' => 'required|integer',
            'area_destino' => 'required',
            'fecha_vencimiento' => 'required|date',
        ]);

        // 2. Determinamos el estado automáticamente
        $status = $request->cantidad_stock > 0 ? 'Disponible' : 'Agotado';

        // 3. Insertamos en PostgreSQL
        Medicamento::create($request->all() + ['status_disponibilidad' => $status]);

        return redirect('/')->with('success', 'Registrado correctamente');
    }
}