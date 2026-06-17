<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Intentamos autenticar con tus columnas de la BD
        if (Auth::attempt(['nombre' => $credentials['nombre'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'));
        }

        return back()->withErrors(['error' => 'Las credenciales introducidas son incorrectas.']);
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}