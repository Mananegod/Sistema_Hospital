<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EpidemiologiaController extends Controller
{
    public function index()
    {
        $casos = DB::table('casos_epidemiologicos')->orderBy('id', 'desc')->get();
        $sectores = DB::table('sectores')->orderBy('nombre_sector', 'asc')->get();
        
        return view('epidemiologia', compact('casos', 'sectores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_paciente'    => 'required|regex:/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/|max:25',
            'cedula_paciente'    => 'required|numeric|max_digits:10',
            'patologia_cie10'    => 'required',
            'sector_procedencia' => 'required', 
            'fecha_sintomas'     => 'required|date|before_or_equal:2036-12-31',
            'estado_caso'        => 'required|in:SOSPECHOSO,EN ESPERA,CONFIRMADO',
            'observaciones'      => 'required',
        ], [
            'nombre_paciente.required'    => 'El nombre del paciente es obligatorio.',
            'cedula_paciente.required'    => 'La cédula del paciente es obligatoria y se requiere algún dato.',
            'cedula_paciente.max_digits'    => 'La cedula no puede ser mayor a 10 digitos',
            'cedula_paciente.numeric'     => 'La cédula debe contener estrictamente números, sin letras ni caracteres.',
            'patologia_cie10.required'    => 'La patología de notificación es obligatoria.',
            'sector_procedencia.required' => 'Debe seleccionar un sector de procedencia.',
            'fecha_sintomas.required'     => 'La fecha de inicio de síntomas es obligatoria.',
            'fecha_sintomas.date'         => 'La fecha de inicio de síntomas no es una fecha válida.',
            'fecha_sintomas.before_or_equal' => 'La fecha de inicio de síntomas no puede ser mayor al año 2036.',
            'estado_caso.required'        => 'Debe definir el estado actual del caso.',
            'observaciones.required'        => 'Es obligatorio incluir observaciones',
        ]);

        $nombre = mb_strtoupper($request->nombre_paciente, 'UTF-8');
        $patologia = mb_strtoupper($request->patologia_cie10, 'UTF-8');

        DB::table('casos_epidemiologicos')->insert([
            'nombre_paciente'    => $nombre,
            'cedula_paciente'    => $request->cedula_paciente,
            'patologia_cie10'    => $patologia,
            'sector_procedencia' => $request->sector_procedencia, 
            'fecha_sintomas'     => $request->fecha_sintomas,
            'estado_caso'        => $request->estado_caso,
            'observaciones'      => $request->observaciones,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return redirect()->back()->with('success', 'Caso epidemiológico registrado correctamente.');
    }

    public function destroy($id)
    {
        DB::table('casos_epidemiologicos')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Ficha epidemiológica eliminada correctamente.');
    }
}