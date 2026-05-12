<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personal;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;

class PersonalController extends Controller
{
    public function index() {
        $personal = Personal::orderBy('activo', 'desc')
                            ->orderBy('nombres', 'asc')
                            ->get();
        return view('personal', compact('personal'));
    }

    public function store(Request $request) {
        $request->validate([
        'cedula' => 'required|unique:personal,cedula',
        'nombres' => 'required|string|max:255',
        'apellidos' => 'required|string|max:255',
        'cargo' => 'required',
        'turno' => 'required',
        'telefono' => 'required',
    ], [
        'cedula.required' => 'La cédula es obligatoria.',
        'cedula.unique' => 'Ya existe un registro con esta cédula.',
        'nombres.required' => 'El nombre es obligatorio.',
        'apellidos.required' => 'Los apellidos son obligatorios.',
        'cargo.required' => 'Debe seleccionar un cargo.',
        'turno.required' => 'Debe seleccionar un turno.',
        'telefono.required' => 'El teléfono es obligatorio.',
    ]);

        try {
            DB::beginTransaction();

            $p = new Personal();
            $p->cedula = $request->cedula;
            $p->nombres = $request->nombres;
            $p->apellidos = $request->apellidos;
            $p->cargo = $request->cargo;
            $p->especialidad = $request->especialidad ?? 'General';
            $p->turno = $request->turno;
            $p->telefono = $request->telefono;
            $p->activo = true; 
            $p->save();

            Auditoria::create([
                'modulo' => 'Personal',
                'accion' => 'Registro',
                'descripcion' => "Se registró a: {$p->nombres} {$p->apellidos} (C.I. {$p->cedula})",
                'usuario' => 'Admin'
            ]);

            DB::commit();
            return redirect()->route('personal.index')->with('success', '👤 Personal registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['db_error' => 'Error al guardar en base de datos: ' . $e->getMessage()])->withInput();
        }
    }

    public function toggleStatus($id) {
        $empleado = Personal::findOrFail($id);
        $empleado->activo = !$empleado->activo;
        $empleado->save();

        Auditoria::create([
            'modulo' => 'Personal',
            'accion' => 'Estado',
            'descripcion' => "Se cambió el estado de: {$empleado->nombres} {$empleado->apellidos}",
        ]);

        $mensaje = $empleado->activo ? '🟢 Personal activado correctamente.' : '🔴 Personal desactivado correctamente.';
        return back()->with('success', $mensaje);
    }

    public function update(Request $request, $id) {
        $request->validate([
            'nombres' => 'required',
            'apellidos' => 'required',
            'cargo' => 'required',
        ]);

        $empleado = Personal::findOrFail($id);
        $empleado->update($request->all());

        Auditoria::create([
            'modulo' => 'Personal',
            'accion' => 'Edición',
            'descripcion' => "Se actualizaron los datos de: {$empleado->nombres} {$empleado->apellidos}",
        ]);

        return back()->with('success', 'Datos actualizados correctamente.');
    }

    public function bitacora() {
        $registros = Auditoria::orderBy('created_at', 'desc')->get();
        return view('bitacora', compact('registros'));
    }

    public function destroy($id) {
        $empleado = Personal::findOrFail($id);
        
        Auditoria::create([
            'modulo' => 'Personal',
            'accion' => 'Borrado',
            'descripcion' => "Se eliminó del sistema a: {$empleado->nombres} {$empleado->apellidos}",
        ]);

        $empleado->delete();
        return back()->with('success', 'Registro eliminado correctamente.');
    }
}