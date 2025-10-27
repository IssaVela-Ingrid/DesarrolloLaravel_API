<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\LogActionTrait;

class UsuarioController extends Controller
{
    // Usa el Trait para loguear acciones en la base de datos
    use LogActionTrait;

    /**
     * Constructor. Protege TODOS los métodos del CRUD.
     */
    public function __construct()
    {
        // Todos los métodos de CRUD requieren autenticación JWT.
        $this->middleware('auth:api');
    }

    /**
     * Lógica de Autorización de Administrador.
     * Reutilizable para cualquier método que requiera permisos de administrador.
     * @return \Illuminate\Http\JsonResponse|null
     */
    protected function checkAdminAuthorization()
    {
        $user = Auth::guard('api')->user();
        // Verifica si el usuario está autenticado y tiene el rol 'admin'
        if (!$user || !$user->isAdmin()) {
            return response()->json(['message' => 'Acceso no autorizado. Se requiere rol de administrador.'], 403);
        }
        return null; // Devuelve null si la autorización es exitosa
    }

    /**
     * Muestra una lista de todos los usuarios (Solo Admin).
     * URL: GET /api/usuarios
     */
    public function index()
    {
        // 1. Autorización de acceso: Solo administradores
        if ($response = $this->checkAdminAuthorization()) {
            return $response;
        }
        
        try {
            // Limita la cantidad de usuarios para evitar sobrecarga y los pagina.
            $usuarios = Usuario::orderBy('id', 'desc')->paginate(20);
            
            // 2. Loguear la acción del administrador
            $this->logAction(
                'read_all_users', 
                'Administrador recuperó la lista de todos los usuarios.', 
                Auth::guard('api')->id()
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Lista de usuarios recuperada exitosamente.',
                'data' => $usuarios
            ]);
        } catch (\Exception $e) {
            Log::error("Error al obtener lista de usuarios: " . $e->getMessage());
            return response()->json(['message' => 'Error del servidor al obtener usuarios.'], 500);
        }
    }

    /**
     * Almacena un nuevo usuario (Solo Admin puede asignar el rol).
     * NOTA: Este es para el CRUD de Admin. 'register' es para el registro público.
     * URL: POST /api/usuarios
     */
    public function store(Request $request)
    {
        // 1. Autorización de acceso: Solo administradores
        if ($response = $this->checkAdminAuthorization()) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|between:2,100',
            'correo' => 'required|email|unique:usuarios,correo',
            // Opcionalmente puedes requerir 'clave_confirmation'
            'clave' => 'required|string|min:6', 
            'rol' => 'required|string|in:admin,user', // El admin debe especificar el rol
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $usuario = Usuario::create(array_merge(
            $validator->validated(),
            // CRÍTICO: Usamos 'clave' y Hash::make para la columna de contraseña.
            ['clave' => Hash::make($request->clave)] 
        ));

        // 2. Loguear la acción
        $this->logAction(
            'create_user_admin', 
            'Administrador creó un nuevo usuario ID: ' . $usuario->id . ' con rol: ' . $usuario->rol, 
            Auth::guard('api')->id()
        );

        return response()->json([
            'message' => 'Usuario creado exitosamente por administrador',
            'usuario' => $usuario
        ], 201);
    }

    /**
     * Muestra un usuario específico.
     * El usuario puede ver su propio perfil; el admin puede ver cualquiera.
     * URL: GET /api/usuarios/{id}
     */
    public function show(string $id)
    {
        $usuario = Usuario::find($id);
        $currentUser = Auth::guard('api')->user();

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // 1. Autorización: Solo puede ver su perfil O si es un administrador
        if ($currentUser->id != $id && !$currentUser->isAdmin()) {
            return response()->json(['message' => 'Acceso no autorizado para ver este perfil.'], 403);
        }

        // 2. Loguear la acción
        $logType = ($currentUser->id == $id) ? 'read_own_profile' : 'read_user_profile_admin';
        $logMessage = "Usuario ID: {$currentUser->id} leyó perfil ID: {$id}";
        $this->logAction($logType, $logMessage, $currentUser->id);

        return response()->json([
            'status' => 'success',
            'data' => $usuario
        ]);
    }

    /**
     * Actualiza un usuario específico.
     * El usuario puede actualizar su propio perfil; el admin puede actualizar cualquiera.
     * URL: PUT/PATCH /api/usuarios/{id}
     */
    public function update(Request $request, string $id)
    {
        $usuario = Usuario::find($id);
        $currentUser = Auth::guard('api')->user();

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado para actualizar'], 404);
        }

        // 1. Autorización: Solo puede actualizar su perfil O si es un administrador
        if ($currentUser->id != $id && !$currentUser->isAdmin()) {
            return response()->json(['message' => 'Acceso no autorizado para actualizar este perfil.'], 403);
        }

        // 2. Validación de la Solicitud
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|between:2,100',
            // Regla unique ajustada: ignora el ID del usuario actual.
            'correo' => 'sometimes|required|email|unique:usuarios,correo,' . $id, 
            'clave' => 'sometimes|required|string|min:6',
            // Solo el admin puede cambiar el rol
            'rol' => 'sometimes|required|string|in:admin,user', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        
        $data = $request->only(['nombre', 'correo', 'rol']);

        // Hashear la clave si se proporciona
        if ($request->has('clave')) {
            $data['clave'] = Hash::make($request->clave);
        }

        // Si el usuario NO es admin, se elimina 'rol' de los datos a actualizar para prevenir auto-elevación
        if (!$currentUser->isAdmin() && isset($data['rol'])) {
            unset($data['rol']);
        }
        
        $usuario->update($data);

        // 3. Loguear la acción
        $this->logAction(
            'update_profile', 
            'Usuario ID: ' . $currentUser->id . ' actualizó perfil ID: ' . $usuario->id . '. Campos actualizados: ' . implode(', ', array_keys($data)), 
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
        // 1. Autorización de acceso: Solo administradores
        if ($response = $this->checkAdminAuthorization()) {
            return $response;
        }

        $usuario = Usuario::find($id);
        $currentUser = Auth::guard('api')->user();

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado para eliminar'], 404);
        }

        // Prohibir que un admin se auto-elimine (por seguridad)
        if ($usuario->id == $currentUser->id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta de administrador a través del CRUD.'], 403);
        }

        $usuarioId = $usuario->id;
        $usuario->delete();

        // 2. Loguear la acción
        $this->logAction(
            'delete_user_admin', 
            'Administrador ID: ' . $currentUser->id . ' eliminó usuario ID: ' . $usuarioId, 
            $currentUser->id
        );

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }
}
