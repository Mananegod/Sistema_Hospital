<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
                'nombre' => 'required|string',
                'password' => 'required|string',
            ]);

            $user = User::where('nombre', 'ilike', $credentials['nombre'])->first();

            if ($user && Auth::attempt(['nombre' => $user->nombre, 'password' => $credentials['password']])) {
             
                $request->session()->regenerate(); 
                return redirect()->intended(route('home'));
            }

            return back()->withErrors(['error' => 'Las credenciales introducidas son incorrectas.']);

        } catch (\Throwable $th) {
            
            Log::error('ERROR EN LOGIN: ' . $th->getMessage());
            
           
            return back()->withErrors(['error' => 'ERROR FATAL DEL SERVIDOR: ' . $th->getMessage() . ' (Línea ' . $th->getLine() . ')']);
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