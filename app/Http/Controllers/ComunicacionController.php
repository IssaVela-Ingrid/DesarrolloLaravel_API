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

/**
 * Controlador para la funcionalidad de comunicación (envío de correos).
 * * NOTA: La autorización de rol 'admin' se aplica mediante el middleware 'admin'
 * en el archivo de rutas (api.php), por lo que hemos eliminado la comprobación 
 * redundante en el constructor.
 */
class ComunicacionController extends Controller
{
    // Usamos el Trait para heredar el método logAction()
    use LogActionTrait;

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
     * Simula el envío de un correo electrónico a un usuario específico.
     * Esta funcionalidad debe ser usada por administradores.
     * URL: POST /api/comunicacion/enviar
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function enviarCorreo(Request $request)
    {
        // 1. Validación
        $validator = Validator::make($request->all(), [
            'id_usuario_destino' => 'required|exists:usuarios,id',
            'asunto' => 'required|string|max:255',
            'cuerpo_mensaje' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            // El usuario que ejecuta la acción es el administrador autenticado
            $usuarioRemitenteId = Auth::guard('api')->user()->id;
            $usuarioDestino = Usuario::find($request->id_usuario_destino);

            // 2. Lógica de Envío (Simulación)
            /*
            // DESCOMENTAR en un proyecto real con el Mailable configurado.
            // Instrucción: Asumiendo que has de tener una clase NotificacionUsuario (Mailable) configurada.
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
