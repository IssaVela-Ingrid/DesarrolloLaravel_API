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
 */
class EstadisticasController extends Controller
{
    /**
     * Constructor. Aplica middlewares de seguridad y autorización.
     */
    public function __construct()
    {
        // 1. Requiere un token JWT válido para acceder a cualquier método.
        $this->middleware('auth:api'); 

        // 2. Autorización: Solo el rol 'admin' puede ver las estadísticas.
        $this->middleware(function ($request, $next) {
            $user = Auth::guard('api')->user();

            // Si no hay usuario o el rol no es 'admin', se deniega el acceso.
            if (!$user || $user->rol !== 'admin') {
                 return response()->json(['message' => 'Acceso no autorizado. Se requiere rol de administrador para ver las estadísticas.'], 403);
            }
            return $next($request);
        });
    }


    /**
     * Obtiene estadísticas de registro de usuarios agrupadas por día, semana y mes.
     * URL: GET /api/estadisticas/registro-usuarios
     * * @return \Illuminate\Http\JsonResponse
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
            // NOTA: YEARWEEK() es una función de MySQL. Esto funciona con MySQL/PostgreSQL.
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
}
