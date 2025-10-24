<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EstadisticasController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Estas rutas se cargan por el RouteServiceProvider con el middleware 'api',
| que es stateless (sin sesiones ni cookies) y resuelve todos los conflictos.
|
*/

// =========================================================================
// RUTA BASE (http://127.0.0.1:8000/)
// =========================================================================
// Se define fuera del prefijo 'api' para responder a la raíz.
Route::prefix('')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'API REST de Usuarios y Logs funcionando.',
            'info' => 'Usa la ruta /api/auth/login para empezar.'
        ], 200);
    });
});


// =========================================================================
// GRUPO PRINCIPAL DE API (Prefijo /api)
// =========================================================================

Route::prefix('api')->group(function () {
    
    // --- RUTAS DE AUTENTICACIÓN (PÚBLICAS Y PROTEGIDAS) ---
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        
        // PÚBLICAS: Iniciar sesión y Registrar (NO requiere token)
        // URL: POST /api/auth/login
        Route::post('login', 'login');
        // URL: POST /api/auth/register
        Route::post('register', 'register');
    });

    // --- RUTAS PROTEGIDAS (Requieren Token JWT) ---
    Route::middleware('auth:api')->group(function () {
        
        // Autenticación Protegida (Continuación del grupo 'auth')
        Route::controller(AuthController::class)->prefix('auth')->group(function () {
            // URL: POST /api/auth/logout
            Route::post('logout', 'logout');
            // URL: POST /api/auth/refresh
            Route::post('refresh', 'refresh');
        });
        
        // CRUD de Usuarios (User Resource)
        // Rutas protegidas: index, show, update, destroy
        // La ruta POST /api/usuarios (store) es ahora la ruta de Registro/Creación 
        // de Usuario, y debe ser accesible para administradores, NO para registro público.
        Route::apiResource('usuarios', UsuarioController::class)->except(['create', 'edit']);

        // Requisito: Estadísticas
        // URL: GET /api/estadisticas/registros
        Route::get('estadisticas/registros', [EstadisticasController::class, 'getRegistrosStats']);
    });
});
