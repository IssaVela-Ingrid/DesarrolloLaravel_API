<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
// Importamos el Trait para el logueo de acciones
use App\Http\Traits\LogActionTrait; 
use App\Models\Registro;
use App\Models\Usuario;
// En un proyecto real, se importarían las clases Mailables, por ejemplo:
// use App\Mail\NotificacionUsuario; 

class ComunicacionController extends Controller
{
    // Usamos el Trait para heredar el método logAction()
    use LogActionTrait; 

    /**
     * Constructor. Todos los métodos de comunicación deben ser protegidos para 
     * evitar abusos, ya que la acción de envío de correos debe ser autorizada.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        // NOTA: Se recomienda añadir aquí un middleware de autorización 
        // para asegurar que solo los usuarios 'admin' (rol: admin) puedan usar esta función.
    }

    /**
     * Simula el envío de un correo electrónico a un usuario específico.
     * Esta funcionalidad debe ser usada por un administrador para notificar.
     * URL: POST /api/comunicacion/send-email (Protegido y, idealmente, Autorizado)
     */
    public function sendEmail(Request $request)
    {
        // 1. Validación de la solicitud
        $validator = Validator::make($request->all(), [
            // id_usuario_destino debe ser requerido y existir en la tabla 'usuarios'
            'id_usuario_destino' => 'required|integer|exists:usuarios,id', 
            'asunto' => 'required|string|max:255',
            'cuerpo_mensaje' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Recuperar el usuario destino
        $usuarioDestino = Usuario::find($request->id_usuario_destino);

        // Verificación redundante, pero útil si la validación 'exists' fuera omitida
        if (!$usuarioDestino) {
             return response()->json(['message' => 'El usuario destino no existe.'], 404);
        }

        // Obtener el ID del usuario autenticado (el remitente/admin)
        $usuarioRemitenteId = Auth::guard('api')->id();

        try {
            // 2. Lógica de Envío de Correo (Simulación)
            /*
            // --- CÓDIGO REAL PARA ENVÍO DE CORREO ---
            // Asegúrate de tener una clase NotificacionUsuario (Mailable) configurada.
            Mail::to($usuarioDestino->correo)->send(new NotificacionUsuario(
                $request->asunto, 
                $request->cuerpo_mensaje,
                Usuario::find($usuarioRemitenteId)->nombre // Para saber quién envía
            ));
            */
            
            // --- SIMULACIÓN DE ÉXITO ---
            Log::info("Email simulado enviado a {$usuarioDestino->correo}. Asunto: {$request->asunto}");
            
            // 3. Registrar la acción usando el Trait (LogActionTrait)
            // Aquí logueamos la acción con el ID del usuario que la ejecuta (el admin)
            $this->logAction($usuarioRemitenteId, 'email_sent', 'Email enviado al usuario ID: ' . $request->id_usuario_destino);

            return response()->json([
                'message' => 'Correo electrónico enviado (simulado) exitosamente',
                'destino' => $usuarioDestino->correo,
                'remitente_id' => $usuarioRemitenteId,
            ]);

        } catch (\Exception $e) {
            // Manejo de errores de envío (SMTP, configuración de mail, etc.)
            Log::error("Error al enviar correo al usuario ID {$request->id_usuario_destino}: " . $e->getMessage());
            return response()->json([
                'message' => 'Error del servidor al intentar enviar el correo. Intente más tarde.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}
