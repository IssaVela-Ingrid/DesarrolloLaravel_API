<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        // Establecemos 'api' como el guard por defecto para que funcione con JWT
        'guard' => 'api',
        'passwords' => 'usuarios', // Usaremos la clave 'usuarios' para resets
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here, which uses the session storage and the Eloquent user provider.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        // Guard por defecto (si lo usaras para web con sesiones)
        'web' => [
            'driver' => 'session',
            'provider' => 'usuarios', // Apuntamos al provider 'usuarios'
        ],
        
        // Guard para la API (usando JWT)
        'api' => [
            'driver' => 'jwt', // Usamos el driver JWT (Proporcionado por JWT-Auth)
            'provider' => 'usuarios', // Apuntamos al provider 'usuarios'
            'hash' => false, // JWT ya no necesita hash a nivel de guard
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms to authenticate the requests.
    |
    | If you have multiple user tables or models, you may configure multiple
    | sources. The default has been set up for you.
    |
    */

    'providers' => [
        'usuarios' => [ // Cambiamos 'users' por 'usuarios'
            'driver' => 'eloquent',
            // CRÍTICO: Apuntamos al modelo Usuario
            'model' => App\Models\Usuario::class, 
        ],
        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify how the password reset functionality should be able to
    | reset passwords. You may set a number of providers and password
    | tables throughout the application.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security measure keeps tokens from lingering
    | indefinitely and and reduces the chance of token theft.
    |
    */

    'passwords' => [
        'usuarios' => [ // Cambiamos 'users' por 'usuarios'
            'provider' => 'usuarios',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | entire authentication session. By default, the timeout lasts for
    | three hours.
    |
    */

    'password_timeout' => 10800,

];
