<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario; // CRÍTICO: Usaremos el modelo 'Usuario'
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsuarioSeeder extends Seeder // CRÍTICO: El nombre de la clase debe ser UsuarioSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Crear Usuario Administrador para el Login
        // Incluimos el campo 'telefono' y usamos 'nombre'.
        Usuario::create([ 
            'nombre' => 'Admin Test', // <-- CORREGIDO: Usamos 'nombre'
            'correo' => 'admin@test.com', 
            'clave' => '123456', 
            'telefono' => '5551234567', 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('Usuario Admin (admin@test.com / 123456) creado.');

        // 2. Crear Usuarios de Prueba para Estadísticas (100 usuarios)
        $this->createHistoricalUsers(100);

        $this->command->info('100 usuarios de prueba con historial de registro creados.');
    }

    /**
     * Crea usuarios con fechas de registro aleatorias en los últimos 3 meses.
     * @param int $count
     */
    private function createHistoricalUsers(int $count)
    {
        // Lista de nombres y apellidos para variar los usuarios de prueba
        $names = ['Alejandro', 'Brenda', 'Carlos', 'Diana', 'Eduardo', 'Fernanda', 'Gabriel', 'Hilda'];
        $lastNames = ['Gomez', 'Rodriguez', 'Perez', 'Lopez', 'Martinez', 'Sanchez', 'Ramirez', 'Flores'];

        for ($i = 0; $i < $count; $i++) {
            // Generar una fecha aleatoria entre 3 meses atrás y hoy
            $randomDate = Carbon::now()->subMonths(3)->addDays(rand(0, 90));

            // Generar nombre y apellido rotando sobre el array
            $firstName = $names[$i % count($names)];
            $lastName = $lastNames[$i % count($lastNames)];
            
            // Simular un número de teléfono de prueba con un formato genérico
            $phoneSuffix = str_pad($i, 4, '0', STR_PAD_LEFT); 
            
            Usuario::create([ 
                // Genera nombres como: Alejandro Gomez (1), Brenda Rodriguez (2), etc.
                'nombre' => $firstName . ' ' . $lastName . ' (' . ($i + 1) . ')', // <-- CORREGIDO: Usamos 'nombre'
                'correo' => 'user' . ($i + 1) . '@test.com', // Usamos 'correo'
                'clave' => 'password', // Usamos 'clave'
                'telefono' => '555-555-' . $phoneSuffix, // Teléfono simulado
                'created_at' => $randomDate,
                'updated_at' => $randomDate,
            ]);
        }
    }
}
