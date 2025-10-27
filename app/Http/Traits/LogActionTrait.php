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
     * * @param string $accion Nombre de la acción (ej: 'login', 'create_user').
     * @param string|null $mensaje Mensaje detallado para el registro.
     * @return void
     */
    protected function logAction(string $accion, ?string $mensaje = null): void
    {
        try {
            $userId = Auth::check() ? Auth::id() : null;

            Registro::create([
                'accion' => $accion,
                'id_usuario' => $userId, // Usamos 'usuario_id' como clave foránea estándar
                'mensaje' => $mensaje,
                'fecha_hora' => Carbon::now(),
            ]);

        } catch (\Exception $e) {
            // Si falla el registro en DB, solo lo logueamos para no romper la app.
            Log::error("Error al registrar la acción '$accion': " . $e->getMessage());
        }
    }
}
