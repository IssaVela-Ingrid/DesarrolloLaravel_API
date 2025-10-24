<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route; 
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; // ✅ 1. IMPORTAMOS LA CLASE SCHEMA (NECESARIA PARA LA CORRECCIÓN)

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ 2. CORRECCIÓN DEL ERROR SQLSTATE 1071 (Specified key was too long)
        // Esto es necesario para que las migraciones, como la de 'sessions', funcionen
        // correctamente en versiones antiguas de MySQL/MariaDB.
        Schema::defaultStringLength(191);
        
        // Mapeo de las rutas API (Tu código original)
        $this->mapApiRoutes();
    }
    
    /**
     * Define las rutas API para la aplicación.
     */
    protected function mapApiRoutes()
    {
        // Laravel cargará 'routes/api.php' con el prefijo '/api' y el middleware 'api' (REST pura)
        Route::prefix('api')
             ->middleware('api')
             ->group(base_path('routes/api.php'));
    }
}
