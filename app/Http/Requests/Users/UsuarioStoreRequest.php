<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioStoreRequest extends FormRequest
{
    /**
     * Determina si el usuario (administrador) está autorizado a realizar esta solicitud.
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
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
            'clave' => 'required|string|min:6',
        ];
    }
}
