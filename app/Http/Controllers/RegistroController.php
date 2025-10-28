<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para gestionar los registros de acciones (logs) en el sistema.
 * * NOTA: La autorización de rol 'admin' se aplica mediante el middleware 'admin'
 * en el archivo de rutas (api.php), por lo que hemos eliminado la comprobación 
 * redundante en el constructor.
 */
class RegistroController extends Controller
{
    /**
     * Constructor. ELIMINADO ya que la protección se hace en api.php.
     */
    /*
    public function __construct()
    {
        // ... (middleware de autenticación y autorización eliminados)
    }
    */


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
            // Esto es redundante si 'clave' está en $hidden del modelo, pero es una buena 
            // práctica para asegurarse si la relación se cargó de forma lazy o eager.
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
