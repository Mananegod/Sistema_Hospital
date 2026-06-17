<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthHospital
{
    public function handle(Request $request, Closure $next)
    {
        // Si no está logueado en el sistema, rebota
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['error' => 'Debe iniciar sesión para acceder al sistema.']);
        }

        return $next($request);
    }
}