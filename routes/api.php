<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\ComunicacionController;
use App\Http\Controllers\RegistroController; // Asegurar que RegistroController esté importado

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Estas rutas se cargan por el RouteServiceProvider con el middleware 'api',
| el cual es stateless y aplica a todas las rutas dentro de este archivo.
| YA INCLUYE el prefijo /api/.
|
*/

// =========================================================================
// RUTA BASE (http://127.0.0.1:8000/) - No usa el prefijo 'api'
// =========================================================================
Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API REST de Usuarios, Logs y Comunicación funcionando.',
        'info' => 'Usa la ruta /api/auth/login para empezar.'
    ], 200);
});


// =========================================================================
// GRUPO PRINCIPAL DE API (El prefijo 'api' es aplicado automáticamente por Laravel)
// Las rutas internas aquí se convierten en: /api/{...}
// =========================================================================

// --- 1. RUTAS PÚBLICAS DE AUTENTICACIÓN (NO requieren token) ---
// URL RESULTANTE: /api/auth/{login, register}
Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
});

// --- 2. RUTAS PROTEGIDAS (Requieren Token JWT: 'auth:api') ---
Route::middleware('auth:api')->group(function () {

    // --- 2.1. Autenticación Protegida ---
    // URL RESULTANTE: /api/auth/{logout, refresh, me}
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
        Route::post('me', 'me');
    });

    // --- 2.2. Rutas de Administrador (Requieren Token JWT y Rol 'admin') ---
    // Todas estas rutas tendrán el middleware 'admin' adicional.
    Route::middleware('admin')->group(function () {

        // CRUD de Usuarios (Resource) - URL RESULTANTE: /api/usuarios
        Route::apiResource('usuarios', UsuarioController::class)->except(['store']);
        
        // Rutas de Registros (Solo index)
        // URL RESULTANTE: /api/registros
        Route::get('registros', [RegistroController::class, 'index']);

        // Estadísticas - URL RESULTANTE: /api/estadisticas
        Route::controller(EstadisticasController::class)->prefix('estadisticas')->group(function () {
            Route::get('global', 'getGlobalStats');
            Route::get('acciones', 'getRegistrosStats');
        });
        
        // Comunicación - URL RESULTANTE: /api/comunicacion/enviar
        Route::post('comunicacion/enviar', [ComunicacionController::class, 'enviarCorreo']);
    });
});
