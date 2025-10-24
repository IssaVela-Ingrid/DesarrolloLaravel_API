<?php

namespace App\Http\Controllers;

use App\Models\User; // Usamos 'User' por convención, si tu modelo se llama 'Usuario' cámbialo aquí.
// Eliminamos la importación de Registro, ya que el Trait la maneja internamente
// use App\Models\Registro; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
// Importamos y usaremos el Trait
use App\Http\Traits\LogActionTrait; 
// Aunque no lo has proporcionado, es una buena práctica:
use App\Http\Requests\UsuarioRequest; 
use Validator; // Usamos la Facade directamente para la validación en update()

class UsuarioController extends Controller
{
    // 1. Usamos el Trait para heredar el método logAction()
    use LogActionTrait;

    /**
     * Constructor. Protege TODOS los métodos del CRUD. 
     * El registro público (store) debe ser manejado por AuthController.
     */
    public function __construct()
    {
        // Todos los métodos de CRUD de UsuarioController requieren autenticación JWT.
        $this->middleware('auth:api');
    }

    // 2. ELIMINAMOS la función logAction() duplicada, ahora heredada del Trait

    /**
     * Muestra una lista paginada de todos los usuarios (solo para Administradores).
     * URL: GET /api/usuarios (Protegido)
     */
    public function index()
    {
        // 1. Implementación de paginación para eficiencia
        // Asegúrate de usar el modelo correcto (User o Usuario).
        $usuarios = User::orderBy('nombre', 'asc')->paginate(10); 
        
        // 2. Registrar la acción: Quién vio la lista
        $this->logAction(Auth::guard('api')->id());

        // 3. Retornar la colección de usuarios
        return response()->json([
            'status' => 'success',
            'data' => $usuarios
        ], 200);
    }
    
    /**
     * Almacena un nuevo usuario.
     * ⚠️ NOTA: Esta ruta solo debería ser usada por un administrador.
     * Si necesitas registro público, usa AuthController::register.
     * Usamos UsuarioRequest para la validación.
     * URL: POST /api/usuarios (Protegido)
     */
    public function store(UsuarioRequest $request)
    {
        // Validación asegurada por UsuarioRequest

        try {
            $usuario = User::create([
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                // Es CRÍTICO hashear la clave, ya que el modelo User no lo hace automáticamente sin un Mutator/Cast
                'password' => Hash::make($request->clave), 
            ]);

            // Registrar la acción: Quién creó este nuevo usuario
            $this->logAction(Auth::guard('api')->id());

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario creado exitosamente por administrador.',
                'data' => $usuario
            ], 201);
        } catch (\Exception $e) {
             Log::error("Error al crear usuario en store de UsuarioController: " . $e->getMessage());
             return response()->json(['status' => 'error', 'message' => 'Error al crear el usuario.'], 500);
        }
    }

    /**
     * Muestra la información de un usuario específico.
     * URL: GET /api/usuarios/{id} (Protegido)
     */
    public function show(string $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Registrar la acción: Quién vio el detalle
        $this->logAction(Auth::guard('api')->id());

        return response()->json($usuario);
    }

    /**
     * Actualiza la información de un usuario específico.
     * URL: PUT/PATCH /api/usuarios/{id} (Protegido)
     */
    public function update(Request $request, string $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado para actualizar'], 404);
        }

        // 2. Validar los datos de entrada
        // Usamos Request aquí por simplicidad al ser PATCH/PUT
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|between:2,100',
            // Importante: ignorar el propio ID del usuario para la validación de unicidad
            'correo' => 'sometimes|required|email|unique:users,correo,' . $id,
            'clave' => 'sometimes|required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        
        $data = $request->only(['nombre', 'correo']);

        // 3. Hashear la clave si se proporciona
        if ($request->has('clave')) {
            // El campo de la base de datos debe ser 'password' en el modelo User estándar.
            // Si usas 'clave' en tu BD, ajusta esto. Aquí asumo 'password' para el hash.
            $data['password'] = Hash::make($request->clave);
        }

        $usuario->update($data);

        // 4. Registrar la acción
        $this->logAction(Auth::guard('api')->id());

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'usuario' => $usuario
        ]);
    }

    /**
     * Elimina un usuario específico.
     * URL: DELETE /api/usuarios/{id} (Protegido)
     */
    public function destroy(string $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado para eliminar'], 404);
        }

        $usuario->delete();

        // 3. Registrar la acción
        $this->logAction(Auth::guard('api')->id());

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }
}
