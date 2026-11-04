<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personal;
use App\Models\User;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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
            'cedula'       => 'required|numeric|digits_between:1,10|unique:personal,cedula',
            'nombres'      => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'apellidos'    => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'cargo'        => 'required',
            'tipo_usuario' => 'required|in:Admin,Usuario',
            'turno'        => 'required',
            'telefono'     => 'required|numeric|digits_between:1,13',
        ], [
            'cedula.required'       => 'La cédula es obligatoria.',
            'cedula.numeric'        => 'La cédula debe contener solo números.',
            'cedula.digits_between' => 'La cédula no puede exceder los 10 dígitos.',
            'cedula.unique'         => 'Ya existe un registro con esta cédula.',
            
            'nombres.required'      => 'El nombre es obligatorio.',
            'nombres.regex'         => 'El nombre solo debe contener letras.',
            
            'apellidos.required'    => 'Los apellidos son obligatorios.',
            'apellidos.regex'       => 'Los apellidos solo deben contener letras.',
            
            'cargo.required'        => 'Debe seleccionar un cargo.',
            'tipo_usuario.required' => 'Debe seleccionar el tipo de usuario.',
            'turno.required'        => 'Debe seleccionar un turno.',
            
            'telefono.required'     => 'El teléfono es obligatorio.',
            'telefono.numeric'      => 'El teléfono solo debe contener números.',
            'telefono.digits_between' => 'El teléfono no puede exceder los 13 dígitos.',
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
                'usuario'     => Auth::user()?->nombre ?? 'Sistema',
            ]);

            DB::commit();

            return redirect()->route('personal.index', ['tipo' => $p->tipo_usuario])
                             ->with('success', "Personal registrado. Usuario de acceso: {$nombreUsuario} | Contraseña inicial: {$request->cedula}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['db_error' => 'Error al guardar en base de datos: ' . $e->getMessage()])->withInput();
        }
    }

public function toggleStatus($id) {
    try {
        $empleado = Personal::findOrFail($id);
        
        // Invertimos el valor directamente en PostgreSQL usando la expresión nativa NOT activo
        DB::table('personal')->where('id', $id)->update([
            'activo' => DB::raw('NOT activo')
        ]);

        // Refrescamos el modelo para obtener el nuevo estado actualizado
        $empleado->refresh();

        Auditoria::create([
            'modulo'      => 'Personal',
            'accion'      => 'Estado',
            'descripcion' => "Se cambió el estado de: {$empleado->nombres} {$empleado->apellidos}",
            'usuario'     => Auth::user()?->nombre ?? 'Sistema',
        ]);

        $mensaje = $empleado->activo ? 'Personal activado correctamente.' : 'Personal desactivado correctamente.';
        return back()->with('success', $mensaje);
        
    } catch (\Exception $e) {
        return back()->withErrors(['db_error' => 'Error al cambiar estado: ' . $e->getMessage()]);
    }
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
            'usuario'     => Auth::user()?->nombre ?? 'Sistema',
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
            'usuario'     => Auth::user()?->nombre ?? 'Sistema',
        ]);

        $empleado->delete();
        return back()->with('success', 'Registro eliminado correctamente.');
    }
    
    public function exportPdf() {
        $personal = Personal::orderBy('tipo_usuario', 'asc')
                            ->orderBy('activo', 'desc')
                            ->orderBy('nombres', 'asc')
                            ->get();

        Auditoria::create([
            'modulo'      => 'Personal',
            'accion'      => 'Reporte',
            'descripcion' => 'Se generó un reporte en PDF de todo el personal (Admin y Usuarios)',
            'usuario'     => Auth::user()?->nombre ?? 'Sistema',
        ]);

        return view('personal-pdf', compact('personal'));
    }
public function updatePassword(Request $request, $id) {
        $request->validate([
            'current_password'      => 'required|string',
            'user_current_password' => 'required|string',
            'password'              => 'required|string|min:4|confirmed',
        ], [
            'current_password.required'      => 'Debe ingresar su contraseña de administrador.',
            'user_current_password.required' => 'Debe ingresar la contraseña actual del empleado.',
            'password.required'              => 'La nueva contraseña es obligatoria.',
            'password.min'                   => 'La contraseña debe tener al menos 4 caracteres.',
            'password.confirmed'             => 'Las contraseñas no coinciden.',
        ]);

        try {
            $adminActual = Auth::user();

            // 1. Verificamos que la contraseña del administrador actual sea correcta
            if (!Hash::check($request->current_password, $adminActual->password)) {
                return back()->withErrors(['current_password' => 'Su contraseña de administrador es incorrecta.']);
            }

            $empleado = Personal::findOrFail($id);
            
            // 2. Buscamos el usuario de acceso vinculado al personal
            $user = User::where('personal_id', $empleado->id)->first();

            if (!$user) {
                return back()->withErrors(['db_error' => 'No se encontró un usuario de acceso vinculado a este personal.']);
            }

            // 3. NUEVO: Verificamos que la contraseña actual ingresada del empleado coincida con la BD
            if (!Hash::check($request->user_current_password, $user->password)) {
                return back()->withErrors(['user_current_password' => 'La contraseña actual del empleado es incorrecta.']);
            }

            // 4. Actualizamos la contraseña cifrada del empleado
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            Auditoria::create([
                'modulo'      => 'Personal',
                'accion'      => 'Seguridad',
                'descripcion' => "Se actualizó la contraseña de acceso para: {$empleado->nombres} {$empleado->apellidos} (Usuario: {$user->nombre}) por autorización de administrador: {$adminActual->nombre}",
                'usuario'     => $adminActual->nombre ?? 'Sistema',
            ]);

            return back()->with('success', "Contraseña actualizada correctamente para el usuario: {$user->nombre}");

        } catch (\Exception $e) {
            return back()->withErrors(['db_error' => 'Error al actualizar la contraseña: ' . $e->getMessage()]);
        }
    }
}