<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\Auditoria;

class PacienteController extends Controller
{
    public function index() {
        // Obtenemos pacientes activos ordenados por los más recientes
        $pacientes = Paciente::where('de_alta', false)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('pacientes', compact('pacientes'));
    }

    public function store(Request $request) {
        // Validación ampliada para incluir requisitos del F15
        $request->validate([
            'cedula' => 'required|unique:pacientes,cedula',
            'nombres' => 'required|string|max:255',
            'servicio' => 'required',
            'servicio_cod' => 'nullable|string',
            'n_comprobante' => 'nullable|string',
            'fecha_ingreso' => 'required|date'
        ]);

        // Creación del registro con todos los datos del request
        $p = Paciente::create($request->all());

        // Registro detallado en la Bitácora
        Auditoria::create([
            'modulo' => 'Pacientes',
            'accion' => 'Ingreso',
            'descripcion' => "Ingreso de paciente: {$p->nombres}. Servicio: {$p->servicio} (Cod: {$p->servicio_cod}). Comprobante: {$p->n_comprobante}",
        ]);

        return back()->with('success', 'Paciente e ingreso F15 registrados correctamente');
    }

    public function update(Request $request, $id) {
        $paciente = Paciente::findOrFail($id);
        
        // Actualizamos los datos (incluyendo sala_cama, tratamiento, etc)
        $paciente->update($request->all());
        
        Auditoria::create([
            'modulo' => 'Pacientes',
            'accion' => 'Actualización',
            'descripcion' => "Se actualizaron datos médicos/ubicación de: {$paciente->nombres}",
        ]);

        return back()->with('success', 'Información actualizada');
    }
}