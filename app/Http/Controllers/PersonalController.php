<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personal;
use App\Models\User;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PersonalController extends Controller
{
    public function index(Request $request) {
        $tipo = $request->get('tipo', 'Usuario'); 

        $personal = Personal::where('tipo_usuario', $tipo)
                            ->orderBy('activo', 'desc')
                            ->orderBy('nombres', 'asc')
                            ->get();

        return view('personal', compact('personal', 'tipo'));
    }

    public function store(Request $request) {
        $request->validate([
            'cedula'       => 'required|unique:personal,cedula',
            'nombres'      => 'required|string|max:255',
            'apellidos'    => 'required|string|max:255',
            'cargo'        => 'required',
            'tipo_usuario' => 'required|in:Admin,Usuario',
            'turno'        => 'required',
            'telefono'     => 'required',
        ], [
            'cedula.required'       => 'La cédula es obligatoria.',
            'cedula.unique'         => 'Ya existe un registro con esta cédula.',
            'nombres.required'      => 'El nombre es obligatorio.',
            'apellidos.required'    => 'Los apellidos son obligatorios.',
            'cargo.required'        => 'Debe seleccionar un cargo.',
            'tipo_usuario.required' => 'Debe seleccionar el tipo de usuario.',
            'turno.required'        => 'Debe seleccionar un turno.',
            'telefono.required'     => 'El teléfono es obligatorio.',
        ]);

        try {
            DB::beginTransaction();

            
            $p = Personal::create([
                'cedula'       => $request->cedula,
                'nombres'      => $request->nombres,
                'apellidos'    => $request->apellidos,
                'cargo'        => $request->cargo,
                'tipo_usuario' => $request->tipo_usuario,
                'especialidad' => $request->especialidad ?? 'General',
                'turno'        => $request->turno,
                'telefono'     => $request->telefono,
                'activo'       => DB::raw('true'),
            ]);

            
            $primerNombre = Str::slug(explode(' ', trim($request->nombres))[0]);
            $primerApellido = Str::slug(explode(' ', trim($request->apellidos))[0]);
            $nombreUsuario = strtolower("{$primerNombre}.{$primerApellido}");

            
            $count = User::where('nombre', 'ilike', "{$nombreUsuario}%")->count();
            if ($count > 0) {
                $nombreUsuario = "{$nombreUsuario}{$count}";
            }

            
            User::create([
                'nombre'      => $nombreUsuario,
                'password'    => Hash::make($request->cedula), 
                'personal_id' => $p->id,
            ]);

            Auditoria::create([
                'modulo'      => 'Personal',
                'accion'      => 'Registro',
                'descripcion' => "Se registró a: {$p->nombres} {$p->apellidos} (Usuario: {$nombreUsuario})",
                'usuario'     => 'Admin'
            ]);

            DB::commit();

            return redirect()->route('personal.index', ['tipo' => $p->tipo_usuario])
                             ->with('success', "👤 Personal registrado. Usuario de acceso: {$nombreUsuario} | Contraseña inicial: {$request->cedula}");

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
            'modulo'      => 'Personal',
            'accion'      => 'Estado',
            'descripcion' => "Se cambió el estado de: {$empleado->nombres} {$empleado->apellidos}",
        ]);

        $mensaje = $empleado->activo ? 'Personal activado correctamente.' : 'Personal desactivado correctamente.';
        return back()->with('success', $mensaje);
    }

    public function update(Request $request, $id) {
        $request->validate([
            'nombres'      => 'required',
            'apellidos'    => 'required',
            'cargo'        => 'required',
            'tipo_usuario' => 'required|in:Admin,Usuario',
        ]);

        $empleado = Personal::findOrFail($id);
        $empleado->update($request->all());

        Auditoria::create([
            'modulo'      => 'Personal',
            'accion'      => 'Edición',
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
            'modulo'      => 'Personal',
            'accion'      => 'Borrado',
            'descripcion' => "Se eliminó del sistema a: {$empleado->nombres} {$empleado->apellidos}",
        ]);

        $empleado->delete();
        return back()->with('success', 'Registro eliminado correctamente.');
    }
}