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
     * Constructor. Aplica middlewares de seguridad y autorización.
     */
    public function __construct()
    {
        // 1. Requiere un token JWT válido para acceder a cualquier método.
        $this->middleware('auth:api');

        // 2. Autorización: Solo el rol 'admin' puede usar esta función de comunicación.
        $this->middleware(function ($request, $next) {
            $user = Auth::guard('api')->user();

            // Si no hay usuario o el rol no es 'admin', se deniega el acceso.
            if (!$user || $user->rol !== 'admin') {
                 return response()->json(['message' => 'Acceso no autorizado. Se requiere rol de administrador para enviar correos.'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Simula el envío de un correo electrónico a un usuario específico.
     * Esta funcionalidad debe ser usada por un administrador para notificar.
     * URL: POST /api/comunicacion/send-email (Protegido y Autorizado a 'admin')
     */
    public function sendEmail(Request $request)
    {
        // El usuario ya fue verificado como 'admin' en el constructor
        $usuarioRemitenteId = Auth::guard('api')->id();

        // 1. Validación de la solicitud
        $validator = Validator::make($request->all(), [
            // id_usuario_destino debe ser requerido y existir en la tabla de usuarios
            'id_usuario_destino' => 'required|integer|exists:usuarios,id',
            'asunto' => 'required|string|max:255',
            'cuerpo_mensaje' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            // Obtener el usuario al que se le enviará el correo
            $usuarioDestino = Usuario::find($request->id_usuario_destino);

            // 2. Lógica de Envío de Correo (Actualizar esto para usar Mail::send en producción)
            /*
            // DESCOMENTAR en producción: Asumiendo que has de tener una clase NotificacionUsuario (Mailable) configurada.
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
            $this->logAction('email_sent', 'Email enviado al usuario ID: ' . $request->id_usuario_destino, $usuarioRemitenteId);

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
