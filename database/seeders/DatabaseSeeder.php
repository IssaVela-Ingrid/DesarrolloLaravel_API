<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llama a los seeders de tu aplicación. 
        // Hemos cambiado la referencia de User a Usuario.
        $this->call([
            UsuarioSeeder::class, 
            // Si tienes otros seeders, agrégalos aquí.
        ]);
    }
}
