<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario; // Usamos el modelo 'Usuario'
use Illuminate\Support\Facades\Hash; // CRÍTICO: Importar Hash para encriptar la clave
use Carbon\Carbon;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Crear Usuario Administrador para el Login
        Usuario::create([ 
            'nombre' => 'Admin Principal',
            'correo' => 'admin@test.com', 
            'clave' => Hash::make('123456'), // 🔑 CRÍTICO: Encriptar la clave
            'telefono' => '5551234567', 
            'rol' => 'admin', // 🎯 Asignar el rol de administrador
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('Usuario Admin (admin@test.com / 123456) y rol "admin" creado.');

        // 2. Crear Usuarios de Prueba (100 usuarios)
        $this->createHistoricalUsers(100);

        $this->command->info('100 usuarios de prueba con rol "user" y historial de registro creados.');
    }

    /**
     * Crea usuarios con fechas de registro aleatorias en los últimos 3 meses.
     * @param int $count
     */
    private function createHistoricalUsers(int $count)
    {
        // ... (Tu lógica para generar datos aleatorios)
        $names = ['Alejandro', 'Brenda', 'Carlos', 'Diana', 'Eduardo', 'Fernanda', 'Gabriel', 'Hilda'];
        $lastNames = ['Gomez', 'Rodriguez', 'Perez', 'Lopez', 'Martinez', 'Sanchez', 'Ramirez', 'Flores'];

        for ($i = 0; $i < $count; $i++) {
            $randomDate = Carbon::now()->subMonths(3)->addDays(rand(0, 90));
            $firstName = $names[$i % count($names)];
            $lastName = $lastNames[$i % count($lastNames)];
            $phoneSuffix = str_pad($i, 4, '0', STR_PAD_LEFT); 
            
            Usuario::create([ 
                'nombre' => $firstName . ' ' . $lastName . ' (' . ($i + 1) . ')',
                'correo' => 'user' . ($i + 1) . '@test.com',
                'clave' => Hash::make('password'), // 🔑 Encriptar la clave
                'telefono' => '555-555-' . $phoneSuffix, 
                'rol' => 'user', // 🎯 Asignar el rol de usuario estándar
                'created_at' => $randomDate,
                'updated_at' => $randomDate,
            ]);
        }
    }
}