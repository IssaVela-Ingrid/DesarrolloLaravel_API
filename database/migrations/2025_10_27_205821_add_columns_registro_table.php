<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        // Agrega las columnas 'accion' y 'detalle' a la tabla 'registros'.
        Schema::table('registros', function (Blueprint $table) {
            // Columna para el tipo de acción (e.g., 'login', 'create_user', 'update_profile')
            $table->string('accion', 100)->after('id_usuario');

            // Columna para el mensaje detallado del log
            $table->text('detalle')->nullable()->after('accion');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        // Define cómo revertir los cambios (eliminar las columnas).
        Schema::table('registros', function (Blueprint $table) {
            $table->dropColumn(['accion', 'detalle']);
        });
    }
};
