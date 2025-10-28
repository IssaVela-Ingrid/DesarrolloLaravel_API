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
// GRUPO DE AUTENTICACIÓN (URL: /api/auth/...)
// =========================================================================
Route::controller(AuthController::class)->prefix('auth')->group(function () {
    // --- RUTAS PÚBLICAS (NO requieren token) ---
    // URL: /api/auth/login, /api/auth/register
    Route::post('login', 'login');
    Route::post('register', 'register');

    // --- RUTAS PROTEGIDAS (Requieren Token JWT: 'auth:api') ---
    Route::middleware('auth:api')->group(function () {
        // URL: /api/auth/logout, /api/auth/refresh, /api/auth/me
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
        Route::get('me', 'me');
    });
});


// =========================================================================
// GRUPO DE RUTAS PROTEGIDAS Y ADMINISTRATIVAS (Requieren 'auth:api')
// =========================================================================
Route::middleware(['auth:api'])->group(function () {

    // --- CRUD DE USUARIO (Acceso general al propio perfil) ---
    // El 'store' (crear) se maneja en 'auth/register'.
    // Las rutas de recursos aquí no están explícitamente definidas porque
    // la mayoría de las operaciones CRUD son para el rol 'admin'.
    // Si un usuario necesita ver/editar SU propio perfil, se añadirían rutas aquí.
    // Dejaremos solo las rutas de administrador por simplicidad, asumiendo
    // que el usuario usa /api/auth/me para ver su info.

    // --- GRUPO DE ADMINISTRADOR (Requiere Token JWT y Rol 'admin') ---
    Route::middleware('admin')->group(function () {

        // CRUD de Usuarios (URL: /api/usuarios)
        // Permite a los administradores gestionar usuarios (index, show, update, destroy, store)
        // Nota: Si 'register' es solo para usuarios, el 'store' del resource
        // permite a los admins crear usuarios. Mantenemos el resource completo.
        Route::apiResource('usuarios', UsuarioController::class)->except(['create', 'edit']);


        // Rutas de Registros/Logs (URL: /api/registros)
        Route::get('registros', [RegistroController::class, 'index']);


        // Estadísticas (URL: /api/estadisticas/...)
        Route::controller(EstadisticasController::class)->prefix('estadisticas')->group(function () {
            // URL: /api/estadisticas/global
            // Corregido: Apunta a 'getGlobalStats' (asumiendo este nombre para las globales)
            Route::get('global', 'getGlobalStats'); 
            
            // URL: /api/estadisticas/registros
            // Corregido: Apunta al método existente 'getRegistroStats' en EstadisticasController
            Route::get('registros', 'getRegistroStats');
        });


        // Comunicación (URL: /api/comunicacion/enviar)
        Route::post('comunicacion/enviar', [ComunicacionController::class, 'enviarCorreo']);
    });
});
