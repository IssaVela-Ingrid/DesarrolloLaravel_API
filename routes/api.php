<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Pruebas de API (Laravel JWT & Rol)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f4f8; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background-color: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); padding: 24px; }
        .btn { padding: 10px 15px; border-radius: 8px; font-weight: 600; transition: background-color 0.3s; cursor: pointer; }
        .btn-primary { background-color: #10b981; color: white; }
        .btn-primary:hover { background-color: #059669; }
        .btn-secondary { background-color: #f3f4f6; color: #1f2937; border: 1px solid #e5e7eb; }
        .btn-secondary:hover { background-color: #e5e7eb; }
        .input-field { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; margin-top: 5px; }
        pre { background-color: #1f2937; color: #4ade80; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 0.9rem; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 5px; font-weight: 700; }
        .status-authenticated { background-color: #d1fae5; color: #065f46; }
        .status-unauthenticated { background-color: #fee2e2; color: #991b1b; }
        .status-inactive { background-color: #e5e7eb; color: #4b5563; }
    </style>
</head>
<body>
    <div class="container py-8">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-6">Panel de Pruebas de API (Laravel JWT & Rol)</h1>
        <p class="text-gray-600 mb-8">Simulación de Registro, Login y Protección de Rutas.</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Columna de Autenticación -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Estado de Autenticación -->
                <div class="card">
                    <h2 class="text-xl font-semibold mb-3 text-gray-700">Estado de Autenticación</h2>
                    <p class="mb-2">ID de Sesión (Firebase): <span id="firebase-id" class="font-mono text-xs text-gray-500">No autenticado</span></p>
                    <p class="mb-2">Token JWT Actual:</p>
                    <div id="jwt-token" class="status-badge status-inactive text-sm">Token INACTIVO</div>
                    <p class="mt-2 text-sm">Rol del Token: <span id="token-role" class="font-bold text-blue-600">N/A</span></p>
                </div>

                <!-- 1. Registro -->
                <div class="card">
                    <h2 class="text-xl font-semibold mb-3 text-gray-700">1. Registro (Rol: User por defecto)</h2>
                    <form id="register-form" class="space-y-4">
                        <input type="text" id="reg-nombre" placeholder="Nombre" required class="input-field">
                        <input type="email" id="reg-correo" placeholder="Correo Electrónico" required class="input-field">
                        <input type="password" id="reg-clave" placeholder="Contraseña (mín. 6)" required class="input-field">
                        <button type="submit" class="btn btn-primary w-full">Registrar Usuario</button>
                    </form>
                </div>

                <!-- 2. Inicio de Sesión -->
                <div class="card">
                    <h2 class="text-xl font-semibold mb-3 text-gray-700">2. Inicio de Sesión</h2>
                    <form id="login-form" class="space-y-4 mb-4">
                        <input type="email" id="log-correo" placeholder="Correo Electrónico" required class="input-field">
                        <input type="password" id="log-clave" placeholder="Contraseña" required class="input-field">
                        <button type="submit" class="btn btn-primary w-full">Iniciar Sesión y Obtener Token</button>
                    </form>
                    <button id="logout-btn" class="btn btn-secondary w-full">Cerrar Sesión (Logout)</button>
                </div>
            </div>

            <!-- Columna de Pruebas y Respuesta -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 3. Pruebas de Endpoints -->
                <div class="card">
                    <h2 class="text-xl font-semibold mb-3 text-gray-700">3. Pruebas de Endpoints</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <button id="test-me-btn" class="btn btn-secondary text-left">
                            <span class="font-mono text-xs bg-gray-200 px-2 py-1 rounded">POST</span>
                            /api/auth/me <br>(Requiere JWT)
                        </button>
                        <button id="test-admin-btn" class="btn btn-secondary text-left">
                            <span class="font-mono text-xs bg-gray-200 px-2 py-1 rounded">GET</span>
                            /api/usuarios <br>(Requiere Rol 'admin')
                        </button>
                        <button id="refresh-btn" class="btn btn-secondary text-left col-span-2">
                            <span class="font-mono text-xs bg-gray-200 px-2 py-1 rounded">POST</span>
                            /api/auth/refresh <br>(Refresca el Token)
                        </button>
                    </div>
                </div>

                <!-- Respuesta de la API -->
                <div class="card">
                    <h2 class="text-xl font-semibold mb-3 text-gray-700">Respuesta de la API</h2>
                    <pre id="api-response" class="h-64"></pre>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        // Importaciones de Firebase
        import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
        import { getAuth, signInWithCustomToken, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";

        // --- CONSTANTES Y VARIABLES GLOBALES (Inyectadas por Canvas) ---
        // Se asume que estas variables existen en el entorno de ejecución
        const firebaseConfig = JSON.parse(typeof __firebase_config !== 'undefined' ? __firebase_config : '{}');
        const initialAuthToken = typeof __initial_auth_token !== 'undefined' ? __initial_auth_token : null;
        const apiBaseUrl = "http://localhost:8000/api"; // URL base de tu backend Laravel

        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);

        let currentJwtToken = localStorage.getItem('jwt_token') || null;
        let authReady = false;

        // --- FUNCIONES DE UTILIDAD ---

        // Función para decodificar el JWT y obtener el rol (simplificada para no usar librerías)
        function decodeJwt(token) {
            try {
                const base64Url = token.split('.')[1];
                const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
                const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                }).join(''));
                const payload = JSON.parse(jsonPayload);
                return payload;
            } catch (e) {
                console.error("Error al decodificar JWT:", e);
                return null;
            }
        }

        // Función para actualizar el estado visual
        function updateUI() {
            const tokenElement = document.getElementById('jwt-token');
            const tokenRoleElement = document.getElementById('token-role');
            const firebaseIdElement = document.getElementById('firebase-id');

            if (authReady) {
                 firebaseIdElement.textContent = auth.currentUser ? auth.currentUser.uid : 'No autenticado';
            }

            if (currentJwtToken) {
                const payload = decodeJwt(currentJwtToken);
                const role = payload ? (payload.role || 'user') : 'N/A';

                tokenElement.textContent = 'Token ACTIVO';
                tokenElement.className = 'status-badge status-authenticated text-sm';
                tokenRoleElement.textContent = role.toUpperCase();

            } else {
                tokenElement.textContent = 'Token INACTIVO';
                tokenElement.className = 'status-badge status-inactive text-sm';
                tokenRoleElement.textContent = 'N/A';
            }
        }

        // Función para hacer llamadas a la API
        async function makeApiCall(endpoint, method = 'GET', body = null) {
            const url = `${apiBaseUrl}${endpoint}`;
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            };

            if (currentJwtToken) {
                headers['Authorization'] = `Bearer ${currentJwtToken}`;
            }

            const config = {
                method: method,
                headers: headers,
            };

            if (body) {
                config.body = JSON.stringify(body);
            }

            let response;
            let data;

            try {
                response = await fetch(url, config);

                // Intenta parsear la respuesta como JSON
                const text = await response.text();
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    // Si no es JSON, usa el texto sin procesar
                    data = { message: `Respuesta inesperada (no JSON): ${text}` };
                }

                const output = {
                    status: response.status,
                    statusText: response.statusText,
                    data: data
                };

                document.getElementById('api-response').textContent = JSON.stringify(output, null, 2);
                return output;

            } catch (error) {
                const output = {
                    status: 'Network Error',
                    statusText: 'Failed to fetch',
                    data: error.message
                };
                document.getElementById('api-response').textContent = JSON.stringify(output, null, 2);
                console.error("Error de red o CORS:", error);
                return output;
            }
        }

        // Función para manejar el token JWT después de Login/Refresh
        function handleTokenResponse(response) {
            if (response.status >= 200 && response.status < 300 && response.data.access_token) {
                currentJwtToken = response.data.access_token;
                localStorage.setItem('jwt_token', currentJwtToken);
                updateUI();
                return true;
            }
            return false;
        }

        // --- INICIALIZACIÓN Y EVENTOS ---

        // 1. Inicialización de Firebase (Auth)
        onAuthStateChanged(auth, async (user) => {
            if (!authReady) {
                if (initialAuthToken) {
                    // Intenta iniciar sesión con el token personalizado (Canvas)
                    await signInWithCustomToken(auth, initialAuthToken).catch(e => {
                        console.warn("Fallo al iniciar sesión con token de Canvas:", e.message);
                    });
                } else if (!user) {
                     // Si no hay token inicial y no hay usuario, queda sin autenticar
                    console.log("No hay token de Canvas. Esperando autenticación.");
                }
                authReady = true;
                updateUI();
            }
        });

        window.onload = () => {
            updateUI(); // Actualiza el UI al cargar

            // --- EVENT HANDLERS ---

            // 1. Registro
            document.getElementById('register-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const nombre = document.getElementById('reg-nombre').value;
                const correo = document.getElementById('reg-correo').value;
                const clave = document.getElementById('reg-clave').value;

                // CORREGIDO: Usar /auth/register
                const result = await makeApiCall('/auth/register', 'POST', { nombre, correo, clave });

                if (result.status === 201) {
                    alert("¡Registro exitoso! Ahora inicia sesión.");
                }
            });

            // 2. Login
            document.getElementById('login-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const correo = document.getElementById('log-correo').value;
                const clave = document.getElementById('log-clave').value;

                // CORREGIDO: Usar /auth/login
                const response = await makeApiCall('/auth/login', 'POST', { correo, clave });
                handleTokenResponse(response);
            });

            // 2. Logout
            document.getElementById('logout-btn').addEventListener('click', async () => {
                if (currentJwtToken) {
                    // CORREGIDO: Usar /auth/logout
                    const response = await makeApiCall('/auth/logout', 'POST');
                    if (response.status === 200) {
                        currentJwtToken = null;
                        localStorage.removeItem('jwt_token');
                        updateUI();
                        alert("Sesión cerrada exitosamente.");
                    } else {
                        // Forzar cierre local si hay error en el endpoint (p.ej. token expirado)
                        currentJwtToken = null;
                        localStorage.removeItem('jwt_token');
                        updateUI();
                    }
                } else {
                    alert("No hay sesión activa para cerrar.");
                }
            });

            // 3. Pruebas de Endpoints

            // GET /api/auth/me (Requiere JWT)
            document.getElementById('test-me-btn').addEventListener('click', async () => {
                if (!currentJwtToken) {
                    alert("Debes iniciar sesión primero.");
                    return;
                }
                // CORREGIDO: Usar /auth/me y método POST
                await makeApiCall('/auth/me', 'POST');
            });

            // GET /api/usuarios (Requiere Rol 'admin')
            document.getElementById('test-admin-btn').addEventListener('click', async () => {
                if (!currentJwtToken) {
                    alert("Debes iniciar sesión primero.");
                    return;
                }
                // CORRECTO: Esta ruta NO usa el prefijo 'auth/'
                await makeApiCall('/usuarios', 'GET');
            });

            // POST /api/auth/refresh
            document.getElementById('refresh-btn').addEventListener('click', async () => {
                if (!currentJwtToken) {
                    alert("Debes iniciar sesión primero.");
                    return;
                }
                // CORREGIDO: Usar /auth/refresh
                const response = await makeApiCall('/auth/refresh', 'POST');
                handleTokenResponse(response);
            });
        };
    </script>
</body>
</html>
