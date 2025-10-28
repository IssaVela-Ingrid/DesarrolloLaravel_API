<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\LogActionTrait;

/**
 * Controlador para la gestión de usuarios (CRUD) por parte de un administrador.
 * * NOTA: La autorización de rol 'admin' se aplica mediante el middleware 'admin'
 * en el archivo de rutas (api.php), por lo que hemos eliminado las comprobaciones
 * redundantes en este controlador.
 */
class UsuarioController extends Controller
{
    // Usa el Trait para loguear acciones en la base de datos
    use LogActionTrait;

    /*
     * ELIMINAMOS: Constructor y el método checkAdminAuthorization() 
     * ya que la protección de acceso se hace por el middleware 'admin' en api.php.
     */

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
     * URL: POST /api/usuarios
     */
    public function store(Request $request)
    {
        $currentUser = Auth::guard('api')->user();

        // 1. Validación
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'correo' => 'required|string|email|max:255|unique:usuarios,correo',
            'clave' => 'required|string|min:6|confirmed', // 'confirmed' busca 'clave_confirmation'
            'rol' => 'required|string|in:admin,user',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Creación
        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            // Hashing manual de la contraseña
            'clave' => Hash::make($request->clave), 
            'rol' => $request->rol,
        ]);

        // 3. Loguear la acción
        $this->logAction(
            'create_user_admin', 
            'Administrador ID: ' . $currentUser->id . ' creó usuario ID: ' . $usuario->id . ' con rol: ' . $usuario->rol,
            $currentUser->id
        );

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario
        ], 201);
    }

    /**
     * Muestra la información de un usuario específico (Solo Admin).
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
            'usuario' => $usuario
        ]);
    }

    /**
     * Actualiza un usuario específico (Solo Admin).
     * URL: PUT/PATCH /api/usuarios/{id}
     */
    public function update(Request $request, string $id)
    {
        $usuario = Usuario::find($id);
        $currentUser = Auth::guard('api')->user();
        
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado para actualizar'], 404);
        }

        // 1. Validación dinámica
        $rules = [
            'nombre' => 'sometimes|required|string|max:255',
            'correo' => 'sometimes|required|string|email|max:255|unique:usuarios,correo,' . $id,
            'rol' => 'sometimes|required|string|in:admin,user',
        ];

        // Solo se valida la clave si se proporciona
        if ($request->has('clave')) {
            $rules['clave'] = 'nullable|string|min:6|confirmed';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Preparar los datos para la actualización
        $data = $request->only('nombre', 'correo', 'rol');

        // Si se proporciona la clave, hashearla antes de actualizar
        if ($request->has('clave') && !empty($request->clave)) {
            $data['clave'] = Hash::make($request->clave);
        }

        // 3. Actualizar
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

        return response()->json(['message' => 'Usuario eliminado exitosamente'], 200);
    }
}
