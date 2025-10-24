<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Registro;
use App\Models\Usuario;
// En un proyecto real, se importarían las clases Mailables, por ejemplo:
// use App\Mail\NotificacionUsuario; 

class ComunicacionController extends Controller
{
    /**
     * Constructor. Todos los métodos de comunicación deben ser protegidos para 
     * evitar abusos, ya que la acción de envío de correos debe ser autorizada.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Registra una acción en la tabla 'registros'.
     * NOTA: Esta función es duplicada en varios controladores (debería ser un Trait).
     *
     * @param int $userId ID del usuario que realiza la acción
     */
    protected function logAction($userId)
    {
        if (!is_numeric($userId)) {
             \Log::warning("Intento de registrar acción con userId no válido: " . $userId);
             return;
        }

        try {
            Registro::create([
                'id_usuario' => $userId,
            ]);
        } catch (\Exception $e) {
            \Log::error("Error al registrar la acción para el usuario ID {$userId}: " . $e->getMessage());
        }
    }

    /**
     * Simula el envío de un correo electrónico a un usuario específico.
     * Esta funcionalidad debe ser usada por un administrador para notificar.
     * * URL: POST /api/comunicacion/send-email (Protegido)
     */
    public function sendEmail(Request $request)
    {
        // 1. Validar la petición
        $validator = Validator::make($request->all(), [
            'id_usuario_destino' => 'required|exists:usuarios,id',
            'asunto' => 'required|string|max:255',
            'cuerpo_mensaje' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $usuarioDestino = Usuario::find($request->id_usuario_destino);
        $usuarioRemitenteId = Auth::guard('api')->id();

        try {
            // 2. Lógica de Envío de Correo (Simulación)
            /*
            Mail::to($usuarioDestino->correo)->send(new NotificacionUsuario(
                $request->asunto, 
                $request->cuerpo_mensaje
            ));
            */
            
            // Simulación de éxito
            Log::info("Email simulado enviado a {$usuarioDestino->correo}. Asunto: {$request->asunto}");
            
            // 3. Registrar la acción: El administrador que envió la comunicación
            $this->logAction($usuarioRemitenteId);

            return response()->json([
                'message' => 'Correo electrónico enviado (simulado) exitosamente',
                'destino' => $usuarioDestino->correo,
                'remitente_id' => $usuarioRemitenteId,
            ]);

        } catch (\Exception $e) {
            Log::error("Error al enviar correo al usuario ID {$request->id_usuario_destino}: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al intentar enviar el correo.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}
