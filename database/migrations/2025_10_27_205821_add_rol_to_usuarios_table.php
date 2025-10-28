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
        // Usa la tabla 'usuarios'
        Schema::table('usuarios', function (Blueprint $table) {
            // Añadimos el campo 'rol' después de 'clave', por defecto 'user'
            $table->enum('rol', ['admin', 'user'])->default('user')->after('clave');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Elimina la columna 'rol' si se hace rollback
            $table->dropColumn('rol');
        });
    }
};
