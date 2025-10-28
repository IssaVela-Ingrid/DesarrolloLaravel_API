<?php

namespace App\Http\Traits; // ESTE DEBE SER EL NAMESPACE EXACTO

use App\Models\Registro;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

trait LogActionTrait
{
    /**
     * Registra una acción importante en la base de datos.
     * * @param string|int|null $userIdOverride ID del usuario a registrar. Si es null, usa Auth::id().
     * @param string $accion Nombre de la acción (ej: 'login', 'create_user').
     * @param string|null $mensaje Mensaje detallado para el registro.
     * @return void
     */
    protected function logAction(
        string $accion,
        ?string $mensaje = null,
        int|string|null $userIdOverride = null // Acepta int, string o null
    ): void
    {
        try {
            // Si se pasa un ID, úsalo. Si no, usa el ID del usuario autenticado (si lo hay).
            $userId = $userIdOverride ?? (Auth::check() ? Auth::id() : null);

            // Evitar crear un log si no hay acción definida (aunque $accion es requerido)
            if (empty($accion)) {
                 Log::warning("Intento de loguear una acción sin nombre.");
                 return;
            }

            Registro::create([
                'accion' => $accion,
                // Usamos 'id_usuario' como clave foránea
                'id_usuario' => $userId,
                'mensaje' => $mensaje,
                // La fecha_hora es redundante ya que created_at lo maneja
                // 'fecha_hora' => Carbon::now(),
            ]);

        } catch (\Exception $e) {
            // Si falla el registro en DB, solo lo logueamos para no romper la app.
            // Es vital que el sistema funcione incluso si el log falla.
            Log::error("Error al registrar la acción '$accion': " . $e->getMessage());
        }
    }
}
