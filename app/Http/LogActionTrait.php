<?php

namespace App\Http\Traits;

use App\Models\Registro;
use Illuminate\Support\Facades\Log;

trait LogActionTrait
{
    /**
     * Registra una acción en la tabla 'registros' usando el ID del usuario.
     * Esta función es reusable a través de cualquier controlador que use este Trait.
     *
     * @param int $userId ID del usuario que realiza la acción
     */
    protected function logAction($userId)
    {
        // 1. Validar que el ID sea numérico
        if (!is_numeric($userId)) {
             Log::warning("Intento de registrar acción con userId no válido: " . $userId);
             return;
        }

        try {
            // 2. Crear el registro
            Registro::create([
                'id_usuario' => $userId,
            ]);
        } catch (\Exception $e) {
            // 3. Registrar error si falla la creación
            Log::error("Error al registrar la acción para el usuario ID {$userId}: " . $e->getMessage());
        }
    }
}
