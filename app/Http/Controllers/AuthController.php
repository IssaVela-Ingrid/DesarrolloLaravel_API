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
            // Asumo que el modelo Usuario usa 'clave' como fillable y no 'password'
            'clave' => Hash::make($request->clave),
            // Valor por defecto en la BD debe ser 'normal', pero lo aseguramos aquí:
            'rol' => 'user', 
        ]);
        
        // Logueo de acción: Registro exitoso
        $this->logAction(
            'register', 
            "Registro exitoso de nuevo usuario. ID: {$usuario->id}. Rol: {$usuario->rol}",
            $usuario->id // El ID del usuario que se acaba de registrar
        );


        // Se puede retornar el token al registrarse automáticamente
        $token = auth()->attempt(['correo' => $request->correo, 'password' => $request->clave]);

        return $this->respondWithToken($token, 'Usuario registrado y logueado exitosamente');
    }

    /**
     * Inicia la sesión del usuario y retorna el token JWT.
     */
    public function login(Request $request)
    {
        // Validación de los campos de login (correo, clave)
        $validator = Validator::make($request->all(), [
            'correo' => 'required|string|email',
            'clave' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // Unprocessable Entity
        }

        // Recuperar el correo y la clave
        $credentials = [
            'correo' => $request->correo,
            // Importante: El campo en la solicitud es 'clave'
            'password' => $request->clave, // JWTAuth espera 'password' en el array de credenciales
        ];

        // Intentar la autenticación con el guard 'api'
        // Si las credenciales son incorrectas, attempt() retorna false.
        if (! $token = auth()->attempt($credentials)) {
            // Logueo de acción: Intento de login fallido
            $this->logAction(
                'login_failed',
                "Intento de inicio de sesión fallido para el correo: {$request->correo}. Clave incorrecta.",
                null // No hay usuario autenticado, por eso es null
            );
            
            // ✅ CORRECCIÓN: Usar un formato JSON estándar con clave:valor.
            // La respuesta anterior era: ['errorCredenciales inválidas']
            return response()->json(['error' => 'Credenciales inválidas'], 401); 
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
    protected function respondWithToken($token, $message = 'Inicio de sesión exitoso')
    {
        return response()->json([
            'message' => $message,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
