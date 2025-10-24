<?php

/*
|--------------------------------------------------------------------------
| JWT Authentication Configuration
|--------------------------------------------------------------------------
|
| Este archivo contiene la configuración para el paquete tymon/jwt-auth.
| Debes configurarlo para que sea consistente con tus modelos de usuario.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | JWT Secret
    |--------------------------------------------------------------------------
    |
    | La clave secreta para firmar los tokens. Laravel necesita un valor
    | en el .env (JWT_SECRET).
    |
    */

    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JWT Token Time-to-Live (TTL)
    |--------------------------------------------------------------------------
    |
    | Define la duración del token en minutos.
    |
    */

    'ttl' => env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | JWT Refresh Time-to-Live
    |--------------------------------------------------------------------------
    |
    | Define la duración máxima para refrescar un token expirado.
    |
    */

    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    /*
    |--------------------------------------------------------------------------
    | JWT Token Storage
    |--------------------------------------------------------------------------
    |
    | Esto define el lugar donde JWT-Auth guarda los tokens invalidados/bloqueados.
    | Por defecto usa el driver 'cache' de Laravel, que puede causar el error
    | 'Unknown column user_id' si no tienes la tabla 'cache' configurada.
    |
    | **CAMBIO CRÍTICO:** Para deshabilitar esto y evitar el problema, 
    | cambiamos el 'driver' a 'null'.
    |
    */

    'blacklist_enabled' => true,
    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 5),

    'hash_method' => 'sha256', // Método para hashear el JTI (ID del token)
    
    // CRÍTICO: Usamos el driver 'null' para que JWT-Auth no intente 
    // interactuar con la tabla 'cache' o 'database' para almacenar tokens invalidados.
    'storage' => [
        'driver' => 'null', 
        'cache' => [
            'store' => 'file', // Esto se ignora si 'driver' es 'null'
            'key' => 'jwt',
            'lifetime' => 20160,
        ],
        'database' => [
            'connection' => null, // Esto se ignora si 'driver' es 'null'
            'table' => 'jwt_tokens',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT Identity
    |--------------------------------------------------------------------------
    |
    | Configuración para identificar al usuario dentro del token.
    |
    */

    'user' => [
        'provider' => 'usuarios', // CRÍTICO: Debe coincidir con el provider en config/auth.php
        'identifier' => 'id', // El campo en tu modelo Usuario que usa JWT
    ],

    // ... otras configuraciones (middleware, claims, etc.)
    
    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ],

    'persistent_claims' => [
        // 'user_id',
    ],

    'lock_subject' => true,

    'leeway' => 0,

    'delimiter' => '.',
    
    'payload_factory' => 'Tymon\JWTAuth\PayloadGenerators\Laravel',

    'default_claim_values' => [
        // 'aud' => 'http://example.com',
    ],

];
