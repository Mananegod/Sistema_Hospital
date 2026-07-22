<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'nombre'   => 'required|string',
                'password' => 'required|string',
            ]);

            // Carga la relación 'personal' para validar el estatus activo y el tipo de usuario
            $user = User::with('personal')
                        ->where('nombre', 'ilike', $credentials['nombre'])
                        ->first();

            if ($user && Auth::attempt(['nombre' => $user->nombre, 'password' => $credentials['password']])) {
                
                // Verificar si el personal asociado fue desactivado
                if ($user->personal && !$user->personal->activo) {
                    Auth::logout();
                    return back()->withErrors(['error' => 'Su cuenta se encuentra inactiva. Contacte al administrador.']);
                }

                $request->session()->regenerate();

                // Si es un usuario estándar (No Admin), redirigir directamente al módulo de Retiros
                if ($user->personal && $user->personal->tipo_usuario === 'Usuario') {
                    return redirect()->route('retiros.index');
                }

                return redirect()->intended(route('home'));
            }

            return back()->withErrors(['error' => 'Las credenciales introducidas son incorrectas.']);

        } catch (QueryException $e) {
            Log::error('ERROR DE BASE DE DATOS EN LOGIN: ' . $e->getMessage());
            return back()->withErrors(['error' => 'No se pudo conectar con la base de datos. Intente más tarde.']);
        } catch (\Throwable $th) {
            Log::error('ERROR FATAL EN LOGIN: ' . $th->getMessage());
            return back()->withErrors(['error' => 'ERROR FATAL DEL SERVIDOR: ' . $th->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $cookieSession = cookie()->forget(config('session.cookie'));
            $cookieXsrf = cookie()->forget('XSRF-TOKEN');

            return redirect()->route('login')->withCookies([$cookieSession, $cookieXsrf]);
        } catch (\Throwable $th) {
            return redirect()->route('login')->withErrors(['error' => 'Error al cerrar sesión: ' . $th->getMessage()]);
        }
    }
}