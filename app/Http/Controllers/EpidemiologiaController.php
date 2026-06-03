<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EpidemiologiaController extends Controller
{
    public function index()
    {
        // Traemos todos los casos ordenados por el más reciente
        $casos = DB::table('casos_epidemiologicos')->orderBy('id', 'desc')->get();
        
        // Traemos los sectores alfabéticamente para cargarlos en el select
        $sectores = DB::table('sectores')->orderBy('nombre_sector', 'asc')->get();
        
        return view('epidemiologia', compact('casos', 'sectores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_paciente'    => 'required',
            'patologia_cie10'    => 'required',
            'sector_procedencia' => 'required', // Validamos que el sector seleccionado llegue
            'fecha_sintomas'     => 'required|date',
            'estado_caso'        => 'required|in:SOSPECHOSO,PROBABLE,CONFIRMADO',
        ], [
            'nombre_paciente.required'    => 'El nombre del paciente es obligatorio.',
            'patologia_cie10.required'    => 'La patología de notificación es obligatoria.',
            'sector_procedencia.required' => 'Debe seleccionar un sector de procedencia.',
            'fecha_sintomas.required'     => 'La fecha de inicio de síntomas es obligatoria.',
            'estado_caso.required'        => 'Debe definir el estado actual del caso.',
        ]);

        $nombre = mb_strtoupper($request->nombre_paciente, 'UTF-8');
        $patologia = mb_strtoupper($request->patologia_cie10, 'UTF-8');

        DB::table('casos_epidemiologicos')->insert([
            'nombre_paciente'    => $nombre,
            'cedula_paciente'    => $request->cedula_paciente,
            'patologia_cie10'    => $patologia,
            'sector_procedencia' => $request->sector_procedencia, // Se almacena directo en mayúsculas desde el select
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