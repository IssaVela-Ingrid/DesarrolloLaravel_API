<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  USAR NOMBRE DE TABLA EN ESPAÑOL
        Schema::create('registros', function (Blueprint $table) { 
            $table->id();
            
            //  CLAVE FORÁNEA APUNTANDO A 'usuarios'
            $table->foreignId('id_usuario')
                  ->constrained('usuarios') 
                  ->onDelete('cascade'); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        //  USAR NOMBRE DE TABLA EN ESPAÑOL
        Schema::dropIfExists('registros'); 
    }
};