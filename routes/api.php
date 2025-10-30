<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\ComunicacionController;
use App\Http\Controllers\RegistroController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Estas rutas se cargan por el RouteServiceProvider con el middleware 'api'
| y ya incluyen el prefijo /api/.
|
*/

// =========================================================================
// RUTA RAÍZ DE LA API (URL: /api)
// =========================================================================
Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API REST de Usuarios, Logs y Comunicación funcionando.',
        'info' => 'Usa la ruta /api/auth/login para empezar.'
    ], 200);
});


// =========================================================================
// GRUPO DE AUTENTICACIÓN Y PROTECCIÓN BÁSICA (URL: /api/auth/...)
// =========================================================================
Route::controller(AuthController::class)->prefix('auth')->group(function () {
    // --- RUTAS PÚBLICAS (NO requieren token) ---
    Route::post('login', 'login');
    Route::post('register', 'register');

    // --- RUTAS PROTEGIDAS (Requieren Token JWT 'auth:api') ---
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
        Route::get('me', 'me'); // Obtener el perfil del usuario autenticado
    });
});


// =========================================================================
// RUTAS PROTEGIDAS DE ADMINISTRADOR (Requieren 'auth:api' Y 'role:admin')
// =========================================================================
// ⚠️ CAMBIO CRÍTICO: Usamos un array con los dos middlewares: 
// 1. 'auth:api': Verifica el token JWT.
// 2. 'role:admin': Llama a RoleMiddleware con el parámetro 'admin'.
Route::middleware(['auth:api', 'permisos:admin'])->group(function () {
    
    // CRUD de Usuarios (URL: /api/usuarios)
    // Permite a los administradores gestionar usuarios (index, show, update, destroy, store)
    Route::apiResource('usuarios', UsuarioController::class)->except(['create', 'edit', 'show']);

    // Rutas de Registros/Logs (URL: /api/registros)
    Route::get('registros', [RegistroController::class, 'index']);

    // Estadísticas (URL: /api/estadisticas/...)
    Route::controller(EstadisticasController::class)->prefix('estadisticas')->group(function () {
        Route::get('global', 'getGlobalStats'); 
        Route::get('registros', 'getRegistroStats');
    });

    // Comunicación (URL: /api/comunicacion/enviar)
    Route::post('comunicacion/enviar', [ComunicacionController::class, 'enviarCorreo']);
    
});