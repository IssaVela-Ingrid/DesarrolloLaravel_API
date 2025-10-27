<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // Necesario para la autorización

class UsuarioUpdateRequest extends FormRequest
{
    /**
     * Determina si el usuario (administrador) está autorizado a realizar esta solicitud.
     * Esta acción solo debe ser permitida para un usuario que ya sea administrador.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // === MEJORA DE SEGURIDAD: Solo si el usuario es un administrador ===
        $user = Auth::guard('api')->user();

        if (!$user) {
            return false;
        }

        // Asumimos que el usuario debe tener el rol 'admin' para actualizar a otros usuarios.
        // Si la ruta es para que el propio usuario se actualice, la lógica cambiaría
        // (ej: return $user->id === $this->route('usuario') || $user->rol === 'admin';)
        return $user->rol === 'admin'; 
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // El ID del usuario que se está actualizando se obtiene de la ruta.
        // Asumiendo que la ruta es 'usuarios/{usuario}'
        $usuarioId = $this->route('usuario');

        return [
            // Los campos son 'sometimes' (opcionales) en la actualización
            'nombre' => 'sometimes|string|max:255',
            
            // Regla crucial: 'unique' ignora el ID del usuario que estamos editando.
            'correo' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                // Ignora el ID del usuario actual para que pueda mantener su propio correo
                Rule::unique('usuarios', 'correo')->ignore($usuarioId),
            ],
            
            // La clave es opcional, pero si se envía, debe cumplir con el mínimo.
            // Es 'clave' para mantener la coherencia con el modelo Usuario.
            'clave' => 'sometimes|string|min:6',
            
            // Opcional: Si el administrador puede cambiar el rol
            'rol' => 'sometimes|string|in:admin,normal', 
        ];
    }
    
    /**
     * Define mensajes de error personalizados.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'correo.email' => 'El formato del correo electrónico no es válido.',
            'correo.unique' => 'Este correo electrónico ya se encuentra en uso por otro usuario.',
            'clave.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'rol.in' => 'El rol proporcionado no es válido. Debe ser "admin" o "normal".',
        ];
    }
}
