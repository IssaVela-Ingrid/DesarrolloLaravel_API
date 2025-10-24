<?php

namespace App\Http\Controllers;

// Importamos el Trait para heredar la funcionalidad de logAction()
use App\Http\Traits\LogActionTrait; 
use App\Models\Registro;
use App\Models\Usuario; // Importamos el modelo Usuario para contar el total de usuarios (asumiendo que es el modelo User/Usuario real)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Aseguramos el uso de Log

class EstadisticasController extends Controller
{
    // Usamos el Trait para heredar el método logAction()
    use LogActionTrait;

    /**
     * Constructor. Todos los métodos de estadísticas deben ser protegidos.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // ELIMINAMOS la función logAction() duplicada. Ahora se hereda del Trait.
    // protected function logAction($userId) { ... }
    
    /**
     * Genera estadísticas globales (KPIs de alto nivel).
     * Incluye el conteo total de usuarios y el conteo total de registros de acciones.
     * URL: GET /api/estadisticas/global (Protegido)
     */
    public function getGlobalStats()
    {
        // Registrar la acción
        $this->logAction(Auth::guard('api')->id());

        // 1. Obtener conteo total de usuarios
        $totalUsuarios = Usuario::count();

        // 2. Obtener conteo total de registros/acciones
        $totalRegistros = Registro::count();

        // 3. Retornar el resultado estructurado
        return response()->json([
            'message' => 'Estadísticas globales obtenidas exitosamente',
            'data' => [
                'total_usuarios' => $totalUsuarios,
                'total_registros_acciones' => $totalRegistros,
            ]
        ]);
    }


    /**
     * Genera estadísticas de usuarios registrados por día, semana y mes.
     * La lógica se basa en la tabla 'registros' y sus timestamps de creación.
     * URL: GET /api/estadisticas/registros (Protegido)
     */
    public function getRegistrosStats(Request $request)
    {
        // 1. Obtener la fecha y hora actual para los cálculos
        $now = now(); 
        
        // Registrar la acción al inicio, ya que el cálculo puede ser costoso
        $this->logAction(Auth::guard('api')->id());

        // =========================================================================
        // 2. CÁLCULO DE REGISTROS POR DÍA (Últimos 7 Días)
        // =========================================================================
        $registrosPorDia = Registro::select(
             DB::raw('DATE(created_at) as fecha'),
             DB::raw('COUNT(id) as cantidad')
           )
           ->where('created_at', '>=', $now->copy()->subDays(7)) // Filtra los últimos 7 días
           ->groupBy('fecha')
           ->orderBy('fecha', 'asc')
           ->get();

        // =========================================================================
        // 3. CÁLCULO DE REGISTROS POR SEMANA (Últimas 4 Semanas)
        // =========================================================================
        // Nota: Se utiliza YEARWEEK(created_at, 1) para agrupar por semana del año
        $registrosPorSemana = Registro::select(
             DB::raw('YEARWEEK(created_at, 1) as semana'), // Agrupa por el número de semana
             DB::raw('COUNT(id) as cantidad')
           )
           ->where('created_at', '>=', $now->copy()->subWeeks(4)) // Filtra las últimas 4 semanas
           ->groupBy('semana')
           ->orderBy('semana', 'asc')
           ->get();


        // =========================================================================
        // 4. CÁLCULO DE REGISTROS POR MES (Últimos 6 Meses)
        // =========================================================================
        // Nota: Se utiliza DATE_FORMAT para agrupar por el año y mes
        $registrosPorMes = Registro::select(
             DB::raw("DATE_FORMAT(created_at, '%Y-%m') as mes"),
             DB::raw('COUNT(id) as cantidad')
           )
           ->where('created_at', '>=', $now->copy()->subMonths(6)) // Filtra los últimos 6 meses
           ->groupBy('mes')
           ->orderBy('mes', 'asc')
           ->get();
        
        // 5. Retornar el resultado estructurado
        return response()->json([
            'message' => 'Estadísticas de registros obtenidas exitosamente',
            'data' => [
                'por_dia' => $registrosPorDia,
                'por_semana' => $registrosPorSemana,
                'por_mes' => $registrosPorMes,
            ]
        ]);
    }
}
