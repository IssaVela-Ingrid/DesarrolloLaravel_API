<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar rutas web para tu aplicación. Estas
| rutas son cargadas por el RouteServiceProvider dentro de un grupo que
| contiene el middleware "web". ¡Ahora crea algo genial!
|
*/

// Ruta de la página de bienvenida por defecto. 
// Esta ruta responde a la URL: http://127.0.0.1:8000/
Route::get('/', function () {
    // Retorna una respuesta JSON simple, indicando que la API está operativa, 
    // lo cual es útil cuando la web principal es la API misma.
    return response()->json([
        'status' => 'success',
        'message' => 'API REST de Usuarios y Logs funcionando.',
        'info' => 'Usa la ruta /api/auth/register o /api/auth/login para empezar.'
    ], 200);
});
