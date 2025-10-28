<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Convierte una excepción de autenticación en una respuesta.
     *
     * Este método anula el comportamiento predeterminado de Laravel que intenta
     * redirigir a la ruta 'login'. Para APIs con JWT, devuelve un JSON 401.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // CRÍTICO: Si la solicitud espera JSON (típico de APIs) O si el guard fallido es 'api',
        // devolvemos un JSON 401 en lugar de redirigir a 'login'.
        if ($request->expectsJson() || in_array('api', $exception->guards())) {
            return response()->json(['message' => 'No autenticado. Se requiere un token de acceso válido.'], 401);
        }

        // Para solicitudes web normales, se mantiene la redirección.
        return redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}
