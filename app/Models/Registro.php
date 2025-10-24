<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Registro extends Model
{
    use HasFactory;
    
    // Nombre de la tabla explícitamente definido
    protected $table = 'registros'; 

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'id_usuario', // Clave foránea al usuario. Solo se guarda la clave foránea.
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
