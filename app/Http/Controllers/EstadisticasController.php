<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para generar estadísticas y métricas del sistema, 
 * enfocándose en el registro de usuarios.
 * * NOTA: La autorización de rol 'admin' se aplica mediante el middleware 'admin'
 * en el archivo de rutas (api.php), por lo que hemos eliminado la comprobación 
 * redundante en el constructor.
 */
class EstadisticasController extends Controller
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
     * Obtiene estadísticas de registro de usuarios agrupadas por día, semana y mes.
     * URL: GET /api/estadisticas/registro-usuarios
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRegistroStats()
    {
        try {
            // 1. Estadísticas de registro por DÍA (Últimos 7 días)
            // Se agrupa por la fecha de creación.
            $registrosPorDia = Usuario::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(7)) // Filtra los últimos 7 días
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get();

            // 2. Estadísticas de registro por SEMANA (Últimas 8 semanas)
            // Se agrupa por el número de semana del año.
            $registrosPorSemana = Usuario::select(
                DB::raw('YEARWEEK(created_at, 1) as periodo'), // '1' indica que la semana comienza en Lunes
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subWeeks(8)) // Filtra las últimas 8 semanas
            ->groupBy('periodo')
            ->orderBy('periodo', 'asc')
            ->get();

            // 3. Estadísticas de registro por MES (Últimos 6 meses)
            // Se agrupa por Año y Mes para evitar conflictos entre años.
            $registrosPorMes = Usuario::select(
                DB::raw('YEAR(created_at) as anio'),
                DB::raw('MONTH(created_at) as mes'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6)) // Filtra los últimos 6 meses
            ->groupBy('anio', 'mes')
            ->orderBy('anio', 'asc')
            ->orderBy('mes', 'asc')
            ->get();


            return response()->json([
                'status' => 'success',
                'message' => 'Estadísticas de registros de usuarios recuperadas exitosamente.',
                'data' => [
                    'por_dia_ultimos_7' => $registrosPorDia,
                    'por_semana_ultimas_8' => $registrosPorSemana,
                    'por_mes_ultimos_6' => $registrosPorMes,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error al obtener estadísticas de registros: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener estadísticas de registros: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Placeholder para estadísticas globales (asumiendo que es una ruta planificada).
     * URL: GET /api/estadisticas/global
     * @return \Illuminate\Http\JsonResponse
     */
     public function getGlobalStats()
     {
         try {
             $totalUsuarios = Usuario::count();
             $totalAdmins = Usuario::where('rol', 'admin')->count();

             return response()->json([
                 'status' => 'success',
                 'message' => 'Estadísticas globales recuperadas exitosamente.',
                 'data' => [
                     'total_usuarios' => $totalUsuarios,
                     'total_admins' => $totalAdmins,
                     // Agregar otras estadísticas globales aquí (e.g., total_logs, last_login_time, etc.)
                 ]
             ]);

         } catch (\Exception $e) {
            Log::error("Error al obtener estadísticas globales: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener estadísticas globales: ' . $e->getMessage(),
            ], 500);
        }
     }
}
