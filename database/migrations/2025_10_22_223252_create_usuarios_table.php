<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id(); 
            $table->string('nombre'); 
            $table->string('correo')->unique(); 
            $table->string('telefono');
            $table->string('clave');
            
            // ✅ CORRECCIÓN: Añadimos la columna 'rol' aquí
            // Usamos 'enum' para restringir los valores a 'admin' o 'user',
            // y 'default('user')' para asignar el rol estándar por defecto.
            $table->enum('rol', ['admin', 'user'])->default('user');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};