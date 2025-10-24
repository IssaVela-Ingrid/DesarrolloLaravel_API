<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // ¡Importar la clase Rule es fundamental!

class UsuarioUpdateRequest extends FormRequest
{
    /**
     * Determina si el usuario (administrador) está autorizado a realizar esta solicitud.
     * Siempre es true porque la autorización real (autenticación JWT) la maneja el middleware en el controlador.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Cambiar a true para permitir que la validación proceda
        return true; 
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
            'clave' => 'sometimes|string|min:6',
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
        ];
    }
}
