<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Cambiado a Authenticatable para claridad
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; 
use App\Models\Registro; 

// Renombrado de User a Usuario
class Usuario extends Authenticatable implements JWTSubject 
{
    use HasFactory, Notifiable;

    // Nombre de la tabla: 'usuarios'
    protected $table = 'usuarios'; 
    protected $primaryKey = 'id';
    public $timestamps = true; 

    protected $fillable = [
        'nombre', 
        'correo',
        'clave', // Usa 'clave' para la contraseña
        'rol',   // Campo para la autorización (rol por defecto 'user')
    ];

    protected $hidden = [
        'clave', // Oculta 'clave'
        'remember_token',
    ];

    protected function casts(): array
    {
        // ¡CRÍTICO! ELIMINADO: 'clave' => 'hashed', 
        // JWT/Auth manejan el hash a través de getAuthPassword() y Hash::make en el controlador.
        return [];
    }
    
    // ------------------------------------------------------------------
    // Métodos de Autenticación
    // ------------------------------------------------------------------
    
    /**
     * Define el campo de contraseña utilizado para la autenticación.
     * (¡Necesario para usar 'clave' en lugar del default 'password'!)
     * @return string
     */
    public function getAuthPassword(): string
    {
        // Indica que 'clave' es el campo de contraseña
        return $this->clave;
    }
    
    // ------------------------------------------------------------------
    // Métodos de JWTSubject
    // ------------------------------------------------------------------
    
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        // Añade el ID y el rol al token para que el frontend pueda leerlos
        return [
            'user_id' => $this->id,
            'rol' => $this->rol,
        ];
    }
    
    // ------------------------------------------------------------------
    // Relaciones
    // ------------------------------------------------------------------

    /**
     * Relación con Registros (logs de acciones).
     */
    public function registros()
    {
        // El modelo Registro usa 'id_usuario' como clave foránea
        return $this->hasMany(Registro::class, 'id_usuario');
    }

    // ------------------------------------------------------------------
    // Helpers de Roles
    // ------------------------------------------------------------------

    /**
     * Verifica si el usuario tiene rol de 'admin'.
     */
    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }
}
