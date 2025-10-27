<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Maneja una petición entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string $role El rol requerido (ej: 'admin', 'user')
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // 1. Verificar si el usuario está autenticado
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'message' => 'No autorizado. Se requiere un token JWT válido.',
            ], 401);
        }

        $user = Auth::guard('api')->user();

        // 2. Comprobar si el usuario tiene el rol requerido
        // NOTA: Asumo que tienes una columna 'rol' en la tabla 'usuarios'.
        if ($user->rol !== $role) {
            // El usuario no tiene el rol necesario
            return response()->json([
                'message' => 'Prohibido. No tienes los permisos de ' . strtoupper($role) . ' para acceder a este recurso.',
            ], 403);
        }

        // 3. Si tiene el rol, permite el acceso a la ruta
        return $next($request);
    }
}
