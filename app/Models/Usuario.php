<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; 
use App\Models\Registro; 

// Renombrado de User a Usuario
class Usuario extends User implements JWTSubject 
{
    use HasFactory, Notifiable;

    // Nombre de la tabla: 'usuarios'
    protected $table = 'usuarios'; 

    protected $fillable = [
        'nombre', // Corregido para usar 'nombre' como se ve en la base de datos
        'correo',
        'clave', // Usa 'clave' para la contraseña
    ];

    protected $hidden = [
        'clave', // Oculta 'clave'
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime', // ELIMINADO: Esta columna no existe en la tabla 'usuarios'.
            'clave' => 'hashed', // Asegura que 'clave' se hashee al guardar
        ];
    }
    
    // ------------------------------------------------------------------
    // Métodos de Autenticación
    // ------------------------------------------------------------------
    
    /**
     * Define el campo de contraseña utilizado para la autenticación.
     * (¡Necesario para usar 'clave' en lugar del default 'password'!)
     * @return string
     */
    public function getAuthPassword()
    {
        // Indica que 'clave' es el campo de contraseña
        return $this->clave;
    }
    
    // ------------------------------------------------------------------
    // Métodos de JWTSubject
    // ------------------------------------------------------------------
    
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    
    // Relación con Registros
    public function registros()
    {
        // El modelo Registro usa 'id_usuario' como clave foránea
        return $this->hasMany(Registro::class, 'id_usuario');
    }
}
