<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            // Verificamos si es un usuario con perfil 'Usuario'
            $esUsuarioEstandar = $user->personal && $user->personal->tipo_usuario === 'Usuario';

            // Opcional: Si quieres que el usuario 'Admin' maestro (que no está en personal) sea tratado como admin total, lo dejamos pasar.
            // Si tiene registro en personal y es de tipo 'Usuario', le aplicamos las restricciones:
            if ($esUsuarioEstandar) {
                $routeName = $request->route() ? $request->route()->getName() : '';

                // Rutas permitidas estrictamente para el rol Usuario
                $rutasPermitidas = [
                    'home',
                    'estadisticas.index',
                    'retiros.index',
                    'almacen.retiros',
                    'almacen.retiros.store',
                    'almacen.retiros.pdf',
                    'logout',
                    'notificaciones.index'
                ];

                if (!in_array($routeName, $rutasPermitidas)) {
                    return redirect()->route('home')->withErrors(['error' => 'No tienes permisos para acceder a este módulo.']);
                }
            }
        }

        return $next($request);
    }
}