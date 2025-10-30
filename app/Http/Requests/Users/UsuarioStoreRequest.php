<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UsuarioStoreRequest extends FormRequest
{
    /**
     * Determina si el usuario (administrador) está autorizado a realizar esta solicitud.
     * * Esta acción solo debe ser permitida para un usuario que ya sea administrador
     * y esté intentando crear otro usuario (o un administrador normal).
     * * @return bool
     */
    public function authorize(): bool
    {
        // === MEJORA DE SEGURIDAD ===
        // 1. Verificar si hay un usuario autenticado.
        $user = Auth::guard('api')->user();

        if (!$user) {
            return false;
        }

        // 2. Verificar el rol. Asumiendo que el campo 'rol' existe en la tabla 'usuarios'
        // y que el rol de administrador es 'admin'.
        // Si tu lógica de roles es más compleja, ajústala aquí (ej: $user->is_admin).
        return $user->rol === 'admin'; 
    }

    /**
     * Obtiene las reglas de validación para la creación de un usuario.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            // Debe ser único en la tabla 'usuarios'
            'correo' => 'required|string|email|max:255|unique:usuarios,correo',
            // Corregido para usar 'clave' por coherencia.
            'clave' => 'required|string|min:6', 
            // 'rol' es opcional, pero si se envía debe ser uno de los permitidos.
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
            'correo.unique' => 'Este correo electrónico ya se encuentra en uso.',
            'clave.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'rol.in' => 'El rol proporcionado no es válido. Debe ser "admin" o "normal".',
        ];
    }
}
