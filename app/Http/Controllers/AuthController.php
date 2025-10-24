<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Importamos la Facade Log
use App\Models\Usuario; // Importamos el modelo Usuario
use App\Http\Traits\LogActionTrait; // Importamos el Trait

class AuthController extends Controller
{
    // Usamos el Trait para heredar el método logAction()
    use LogActionTrait;

    /**
     * Constructor. Protege la mayoría de los métodos con el middleware 'auth:api',
     * excepto el 'login' y 'register' que deben ser accesibles públicamente.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Registra un nuevo usuario en el sistema.
     * URL: POST /api/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'correo' => 'required|email|unique:usuarios,correo',
            'clave' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            // Hash de la contraseña antes de crear
            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'clave' => bcrypt($request->clave), // USAR bcrypt para el hash
            ]);

            // Intentar generar token para el usuario recién registrado
            $token = Auth::guard('api')->login($usuario);

            // Registrar la acción (solo id_usuario)
            $this->logAction($usuario->id);

            return $this->respondWithToken($token);

        } catch (\Exception $e) {
            Log::error("Error al registrar usuario: " . $e->getMessage());
            return response()->json(['error' => 'No se pudo completar el registro.'], 500);
        }
    }


    /**
     * Inicia sesión de un usuario y retorna un token JWT.
     * URL: POST /api/login
     */
    public function login(Request $request)
    {
        // 1. Validar la petición de entrada
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email',
            'clave' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Intentar autenticar y generar el token
        // NOTA: 'password' es el campo esperado internamente por Laravel Auth/JWT.
        $credentials = [
            'correo' => $request->correo,
            'password' => $request->clave, 
        ];

        // Autenticar y generar token
        $token = Auth::guard('api')->attempt($credentials);

        if (!$token) {
            // Error de credenciales (correo o clave incorrectos)
            return response()->json(['error' => 'No autorizado. Credenciales incorrectas.'], 401);
        }

        // 3. Autenticación exitosa
        $user = Auth::guard('api')->user();

        // Registrar el inicio de sesión
        $this->logAction($user->id);

        // Retornar la respuesta con el token y el tipo de token
        return $this->respondWithToken($token);
    }

    /**
     * Cierra la sesión del usuario (invalida el token).
     * URL: POST /api/logout
     */
    public function logout()
    {
        $user = Auth::guard('api')->user();
        if ($user) {
             // Registrar el cierre de sesión
             $this->logAction($user->id);
        }
        
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }

    /**
     * Refresca un token JWT expirado para obtener uno nuevo.
     * URL: POST /api/refresh
     */
    public function refresh()
    {
        // Genera un nuevo token a partir del token actual y lo retorna
        $newToken = Auth::guard('api')->refresh();
        return $this->respondWithToken($newToken);
    }

    /**
     * Retorna la estructura de respuesta con el token.
     *
     * @param  string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            // El tiempo de vida del token está definido en config/jwt.php (default: 60 minutos)
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => Auth::guard('api')->user(),
        ]);
    }

    // Se eliminó la función logAction() duplicada, ahora es heredada del Trait.
}
