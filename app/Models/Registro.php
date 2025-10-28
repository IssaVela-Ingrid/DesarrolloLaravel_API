<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Registro extends Model
{
    use HasFactory;

    // Nombre de la tabla explícitamente definido
    protected $table = 'registros';

    // ¡ACTUALIZACIÓN CRÍTICA!
    // Ahora incluimos los campos para el tipo de acción y la descripción
    protected $fillable = [
        'id_usuario',    // Clave foránea al usuario que realizó la acción
        'tipo_accion',   // Nuevo: Tipo de acción (ej: 'login_success', 'user_created', 'update_user')
        'descripcion',   // Nuevo: Detalle de la acción (ej: 'Inicio de sesión exitoso. Usuario ID: 5')
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
