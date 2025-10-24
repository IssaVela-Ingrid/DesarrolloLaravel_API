<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Crea la tabla 'sessions' para el manejo de sesiones de Laravel.
     */
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            
            // ✅ AJUSTE CLAVE: Usamos 'id_usuario' como columna,
            // y la relacionamos con la tabla 'usuarios' (que ya existe).
            $table->foreignId('id_usuario')
                  ->nullable()
                  ->index()
                  ->constrained('usuarios') // Hace referencia a la tabla 'usuarios'
                  ->onDelete('cascade'); // Opcional, pero buena práctica si eliminas usuarios

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     * Elimina la tabla 'sessions'.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
