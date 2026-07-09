<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter; 
use Illuminate\Support\Str; 
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin() {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('login');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'nombre'   => 'required|string',
            'password' => 'required|string',
        ]);

       
        $throttleKey = Str::lower($credentials['nombre']) . '|' . $request->ip();

        
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {//los intentos hasta que falle
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors(['error' => "Demasiados intentos de inicio de sesión. Por seguridad, intente de nuevo en {$seconds} segundos."]);
        }

        
        $user = User::whereRaw('LOWER(nombre) = ?', [strtolower($credentials['nombre'])])->first();

       
        if ($user && Auth::attempt(['nombre' => $user->nombre, 'password' => $credentials['password']])) {
            
           
            RateLimiter::clear($throttleKey);
            
            $request->session()->regenerate();
            return redirect()->intended(route('home'));
        }

       
        RateLimiter::hit($throttleKey, 10); // SEGUNDOS DE REINTENTO

        return back()->withErrors(['error' => 'Las credenciales introducidas son incorrectas.']);
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}