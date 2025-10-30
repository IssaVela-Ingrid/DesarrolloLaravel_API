<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request; // Sigue siendo necesario para el index y destroy
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\LogActionTrait;

// Importamos los Form Requests dedicados
use App\Http\Requests\Users\UsuarioStoreRequest;
use App\Http\Requests\Users\UsuarioUpdateRequest;

/**
 * Controlador para la gestión de usuarios (CRUD) por parte de un administrador.
 * El acceso está protegido por el middleware 'admin' en api.php.
 */
class UsuarioController extends Controller
{
    // Usa el Trait para loguear acciones en la base de datos
    use LogActionTrait;

    /**
     * Muestra una lista de todos los usuarios (Solo Admin).
     * URL: GET /api/usuarios
     */
    public function index(Request $request)
    {
        // El acceso está garantizado para administradores por el middleware de ruta.
        $usuarios = Usuario::orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Usuarios recuperados exitosamente',
            'data' => $usuarios
        ]);
    }

    /**
     * Crea un nuevo usuario (Solo Admin).
     * * Usamos UsuarioStoreRequest para manejar la validación y la autorización de rol.
     * URL: POST /api/usuarios
     */
    public function store(UsuarioStoreRequest $request)
    {
        // La validación (reglas y errores) y la autorización de rol ya se manejaron en UsuarioStoreRequest.

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'clave' => Hash::make($request->clave), // Asegurarse de usar Hash::make()
            'rol' => $request->rol ?? 'normal', // Si no se envía el rol, por defecto es 'normal'
        ]);

        // 1. Loguear la acción
        $currentUser = Auth::guard('api')->user();
        $this->logAction(
            'create_user_admin', 
            'Administrador ID: ' . $currentUser->id . ' creó nuevo usuario ID: ' . $usuario->id,
            $currentUser->id
        );

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario
        ], 201);
    }

    /**
     * Muestra un usuario específico (Solo Admin).
     * URL: GET /api/usuarios/{id}
     */
    public function show(string $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario recuperado exitosamente',
            'data' => $usuario
        ]);
    }

    /**
     * Actualiza un usuario específico (Solo Admin).
     * * Usamos UsuarioUpdateRequest para manejar la validación y la autorización de rol.
     * URL: PUT/PATCH /api/usuarios/{id}
     */
    public function update(UsuarioUpdateRequest $request, string $id)
    {
        // La validación y la autorización de rol ya se manejaron en UsuarioUpdateRequest.

        $usuario = Usuario::find($id);
        $currentUser = Auth::guard('api')->user();

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado para actualizar'], 404);
        }

        // 1. Recolectar solo los campos que vienen en la solicitud (método 'only' para seguridad)
        $data = $request->only('nombre', 'correo', 'rol');

        // 2. Si se proporciona la clave, hashearla.
        if ($request->has('clave')) {
            $data['clave'] = Hash::make($request->clave);
        }

        // 3. Actualizar el usuario
        $usuario->update($data);

        // 4. Loguear la acción
        $this->logAction(
            'update_user_admin', 
            'Administrador ID: ' . $currentUser->id . ' actualizó perfil ID: ' . $usuario->id . '. Campos actualizados: ' . implode(', ', array_keys($data)), 
            $currentUser->id
        );

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'usuario' => $usuario
        ]);
    }

    /**
     * Elimina un usuario específico (Solo Admin).
     * URL: DELETE /api/usuarios/{id}
     */
    public function destroy(string $id)
    {
        $usuario = Usuario::find($id);
        $currentUser = Auth::guard('api')->user();

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado para eliminar'], 404);
        }

        // Prohibir que un admin se auto-elimine (por seguridad/regla de negocio)
        if ($usuario->id == $currentUser->id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta de administrador a través del CRUD.'], 403);
        }

        $usuarioId = $usuario->id;
        $usuario->delete();

        // 1. Loguear la acción
        $this->logAction(
            'delete_user_admin', 
            'Administrador ID: ' . $currentUser->id . ' eliminó usuario ID: ' . $usuarioId, 
            $currentUser->id
        );

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }
}
