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
    Schema::table('usuarios', function (Blueprint $table) {
        // Agrega la columna de contraseña si no existe
        $table->string('clave'); 
        // Laravel usa 'users', si tu tabla se llama 'usuarios', debes cambiarlo aquí y en el modelo.
    });
}

/*public function down(): void
{
    Schema::table('usuarios', function (Blueprint $table) {
        $table->dropColumn('clave');
    });
}*/
};
