<?php

namespace App\Http\Traits; // ESTE DEBE SER EL NAMESPACE EXACTO

use App\Models\Registro;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
// Eliminado: use Illuminate\Support\Carbon; (ya no es necesario)

trait LogActionTrait
{
    /**
     * Registra una acción importante en la base de datos.
     *
     * @param string $accion Nombre de la acción (ej: 'login', 'create_user').
     * @param string|null $mensaje Mensaje detallado para el registro (se guarda en columna 'detalle').
     * @param int|string|null $userIdOverride ID del usuario a registrar. Si es null, usa Auth::id().
     * @return void
     */
    protected function logAction(
        string $accion,
        ?string $mensaje = null,
        int|string|null $userIdOverride = null
    ): void
    {
        try {
            // Si se pasa un ID, úsalo. Si no, usa el ID del usuario autenticado (si lo hay).
            $userId = $userIdOverride ?? (Auth::check() ? Auth::id() : null);

            // Evitar crear un log si no hay acción definida
            if (empty($accion)) {
                Log::warning("Intento de loguear una acción sin nombre.");
                return;
            }

            Registro::create([
                'accion' => $accion,
                // Usamos 'id_usuario' como clave foránea
                'id_usuario' => $userId,
                // AJUSTE CLAVE: Usamos 'detalle' en lugar de 'mensaje' para coincidir con la migración
                'detalle' => $mensaje,
            ]);

        } catch (\Exception $e) {
            // Si falla el registro en DB, solo lo logueamos para no romper la app.
            // Es vital que el sistema funcione incluso si el log falla.
            Log::error("Error al registrar la acción '$accion': " . $e->getMessage());
        }
    }
}
