<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Http\Traits\LogActionTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Usamos el Trait para heredar el método logAction()
    use LogActionTrait;

    /**
     * Crea un nuevo AuthController.
     * El middleware 'auth:api' se aplica a todos los métodos excepto 'login' y 'register'.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Registra un nuevo Usuario.
     */
    public function register(Request $request)
    {
        // Validación con campos en español, asumiendo que el Body JSON usa estos nombres.
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255|unique:usuarios,correo', 
            'clave' => 'required|string|min:6|confirmed', // Debe venir 'clave_confirmation'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Creación del usuario con campos en español
        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'clave' => Hash::make($request->clave),
            'rol' => 'user', // Rol por defecto
        ]);

        // LOGUEO DE ACCIÓN: Registro de nuevo usuario
        $this->logAction(
            'register', 
            "El usuario #{$usuario->id} con email '{$usuario->correo}' ha sido registrado.",
            $usuario->id 
        );

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $usuario
        ], 201); // 201 Created es apropiado
    }

    /**
     * Obtiene un JWT Token con las credenciales dadas.
     */
    public function login(Request $request)
    {
        // Validación con campos en español (correo, clave)
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email',
            'clave' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // CRÍTICO: auth()->attempt() requiere un array de credenciales.
        // Debemos mapear los campos del Request a los nombres de columna reales de la tabla 'usuarios'.
        $credentials = [
            'correo' => $request->correo,
            'password' => $request->clave, // 'password' es el key que usa auth() para comparar con getAuthPassword()
        ];

        if (! $token = auth()->attempt($credentials)) {
            // LOGUEO DE ACCIÓN: Intento de login fallido
            $this->logAction(
                'login_fail', 
                "Intento de inicio de sesión fallido para el email: {$request->correo}."
            );
            return response()->json(['error' => 'No autorizado. Credenciales inválidas'], 401);
        }

        // LOGUEO DE ACCIÓN: Login exitoso
        $this->logAction(
            'login_success', 
            "Inicio de sesión exitoso. Usuario ID: " . auth()->user()->id
        );

        return $this->respondWithToken($token);
    }

    /**
     * Cierra la sesión del usuario (invalida el token).
     */
    public function logout()
    {
        // LOGUEO DE ACCIÓN: Cierre de sesión
        $this->logAction(
            'logout', 
            "Cierre de sesión. Usuario ID: " . auth()->user()->id
        );
        
        auth()->logout();

        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }

    /**
     * Refresca el token.
     */
    public function refresh()
    {
        // LOGUEO DE ACCIÓN: Token refrescado
         $this->logAction(
            'token_refresh', 
            "Token refrescado exitosamente. Usuario ID: " . auth()->user()->id
        );
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Obtiene el usuario autenticado.
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Obtiene la estructura del token.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
