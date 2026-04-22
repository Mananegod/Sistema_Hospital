<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class AuthController extends Controller
{
    public function showLogin() {
        return view('login');
    }

    public function login(Request $request) {
    $credentials = $request->validate([
        'nombre' => 'required',
        'password' => 'required',
    ]);

    if ($request->nombre == 'Admin' && $request->password == '1234') {
        // CAMBIO AQUÍ: Ahora te lleva a la página HOME
        return redirect()->route('home');
    }

    return back()->withErrors(['error' => 'Credenciales incorrectas']);
}
}