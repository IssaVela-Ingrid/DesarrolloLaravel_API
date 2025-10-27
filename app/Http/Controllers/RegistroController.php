<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para gestionar los registros de acciones (logs) en el sistema.
 * * NOTA: Aplicamos la protección de rol en el constructor para asegurar que
 * solo los administradores puedan acceder al historial de logs.
 */
class RegistroController extends Controller
{
    /**
     * Constructor. Protege la ruta para que solo los administradores accedan.
     */
    public function __construct()
    {
        // 1. Requiere un token JWT válido para acceder.
        $this->middleware('auth:api'); 

        // 2. Autorización: Solo el rol 'admin' puede ver los registros.
        $this->middleware(function ($request, $next) {
            $user = Auth::guard('api')->user();

            if (!$user || $user->rol !== 'admin') {
                 return response()->json(['message' => 'Acceso no autorizado. Se requiere rol de administrador para ver el historial de logs.'], 403);
            }
            return $next($request);
        });
    }


    /**
     * Muestra una lista de los registros de acciones (logs) más recientes.
     * URL: GET /api/registros
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Se recuperan los logs más recientes.
            // Se incluye la relación 'usuario' para obtener la información 
            // del usuario que realizó la acción.
            $registros = Registro::with('usuario')
                                ->orderBy('created_at', 'desc')
                                ->take(50) // Limita a los 50 registros más recientes
                                ->get();
            
            // Ocultamos la 'clave' (contraseña) del usuario antes de devolver los datos.
            $registros->each(function ($registro) {
                if ($registro->usuario) {
                    $registro->usuario->makeHidden('clave');
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Historial de registros de acciones recuperado exitosamente.',
                'data' => $registros
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error al recuperar los registros: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al recuperar los registros. Verifique la conexión a la base de datos y la existencia del modelo: ' . $e->getMessage(),
            ], 500);
        }
    }
}
