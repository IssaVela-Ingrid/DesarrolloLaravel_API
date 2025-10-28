<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Registro extends Model
{
    use HasFactory;

    // Nombre de la tabla explícitamente definido
    protected $table = 'registros';

    // ✅ CORRECCIÓN CRÍTICA: Ajustamos los nombres de los campos
    // para que coincidan con las columnas reales en la base de datos (accion y detalle).
    protected $fillable = [
        'id_usuario',    // Clave foránea al usuario que realizó la acción
        'accion',        // Corregido: Tipo de acción (ej: 'login_success', 'user_created')
        'detalle',       // Corregido: Detalle de la acción (ej: 'Inicio de sesión exitoso. Usuario ID: 5')
    ];

    /**
     * Relación: Un registro pertenece a un usuario (Usuario).
     */
    public function usuario()
    {
        // Define la relación belongsTo apuntando al modelo Usuario
        // y especificando 'id_usuario' como la clave foránea.
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
