<?php
// login.php - MEJORADO: Con mostrar/ocultar contraseña y recordar credenciales
session_start();

// Si ya está logueado, redireccionar INMEDIATAMENTE sin contenido HTML
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // NO generar ningún HTML, solo redireccionar
    if (!headers_sent()) {
        header('Location: /index.php', true, 302);
        exit;
    } else {
        // Si headers ya fueron enviados, usar JavaScript
        echo '<script>window.location.replace("/index.php");</script>';
        exit;
    }
}

require_once 'auth.php';

$mensaje = '';
$tipo_mensaje = '';
$mostrar_usuarios_demo = false;
$login_exitoso = false;

// Cargar credenciales recordadas si existen
$remembered_username = '';
$remember_checked = false;

if (isset($_COOKIE['remember_username']) && isset($_COOKIE['remember_password'])) {
    $remembered_username = $_COOKIE['remember_username'];
    $remember_checked = true;
}

// Procesar login
if ($_POST && isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
    
    if (!empty($username) && !empty($password)) {
        $auth = new Auth();
        $resultado = $auth->login($username, $password);
        
        if ($resultado['success']) {
            // Manejar recordar credenciales
            if ($remember) {
                // Guardar por 30 días
                $expire = time() + (30 * 24 * 60 * 60);
                setcookie('remember_username', $username, $expire, '/', '', false, true);
                setcookie('remember_password', base64_encode($password), $expire, '/', '', false, true);
                setcookie('remember_checked', 'true', $expire, '/', '', false, true);
            } else {
                // Eliminar cookies si no se marcó recordar
                setcookie('remember_username', '', time() - 3600, '/');
                setcookie('remember_password', '', time() - 3600, '/');
                setcookie('remember_checked', '', time() - 3600, '/');
            }
            
            // LOGIN EXITOSO - REDIRECCIÓN LIMPIA INMEDIATA
            if (!headers_sent()) {
                // Usar redirección PHP limpia
                header('Location: /index.php', true, 302);
                exit;
            } else {
                // Fallback JavaScript si headers ya enviados
                echo '<script>
                    console.log("✅ Login exitoso, redirigiendo...");
                    window.location.replace("/index.php");
                </script>';
                exit;
            }
        } else {
            $mensaje = $resultado['message'];
            $tipo_mensaje = 'error';
            $mostrar_usuarios_demo = true;
        }
    } else {
        $mensaje = 'Por favor, completa todos los campos';
        $tipo_mensaje = 'error';
    }
}

// Verificar si existen usuarios en la base de datos
$database = new Database();
$conn = $database->getConnection();
$usuarios_existen = false;

if ($conn) {
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM usuarios WHERE activo = TRUE");
        $count = $stmt->fetchColumn();
        $usuarios_existen = ($count > 0);
        
        if (!$usuarios_existen) {
            $mensaje = 'Sistema inicializándose... Ejecuta setup.php primero.';
            $tipo_mensaje = 'info';
        }
    } catch (Exception $e) {
        $mensaje = 'La Base de datos está vacía. Inicia "Setup Completo".';
        $tipo_mensaje = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Financiero</title>
    
    <!-- PWA Meta Tags -->
    <meta name="application-name" content="Sistema Financiero">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Finanzas">
    <meta name="description" content="Sistema completo de registro financiero con reconocimiento de voz">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-TileColor" content="#4CAF50">
    <meta name="theme-color" content="#4CAF50">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .login-form {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
            background: white;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        /* Estilos específicos para el campo de contraseña */
        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 50px; /* Espacio para el icono */
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #666;
            transition: color 0.3s ease;
            padding: 5px;
            border-radius: 5px;
        }

        .password-toggle:hover {
            color: #4CAF50;
            background: rgba(76, 175, 80, 0.1);
        }

        .password-toggle:focus {
            outline: none;
            color: #4CAF50;
        }

        /* Estilos para el checkbox de recordar */
        .remember-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .remember-checkbox {
            appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #ddd;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }

        .remember-checkbox:checked {
            background: #4CAF50;
            border-color: #4CAF50;
        }

        .remember-checkbox:checked::after {
            content: "✓";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .remember-checkbox:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }

        .remember-label {
            font-size: 14px;
            color: #555;
            cursor: pointer;
            user-select: none;
        }

        .remember-info {
            font-size: 12px;
            color: #888;
            margin-left: auto;
            font-style: italic;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(76, 175, 80, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .mensaje {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .mensaje.error {
            background: #fdf0f0;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .mensaje.success {
            background: #f0f8f0;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensaje.info {
            background: #e7f3ff;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .demo-info {
            background: #e7f3ff;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: none;
        }

        .demo-info.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .demo-info h4 {
            color: #0c5460;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .demo-info .credential {
            background: white;
            padding: 8px 12px;
            border-radius: 5px;
            margin: 5px 0;
            font-family: monospace;
            border: 1px solid #b8daff;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .demo-info .credential:hover {
            background: #f8f9ff;
        }

        .setup-link {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            font-size: 14px;
        }

        .setup-link a {
            color: #856404;
            text-decoration: none;
            font-weight: bold;
        }

        .setup-link a:hover {
            text-decoration: underline;
        }

        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4CAF50;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .security-features {
            background: #f8f9fa;
            padding: 20px;
            border-top: 1px solid #eee;
        }

        .security-features h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .security-features ul {
            list-style: none;
            font-size: 12px;
            color: #666;
        }

        .security-features li {
            margin: 5px 0;
            padding-left: 15px;
            position: relative;
        }

        .security-features li:before {
            content: "🔒";
            position: absolute;
            left: 0;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
            }
            
            .login-form {
                padding: 30px 20px;
            }
            
            .login-header {
                padding: 20px;
            }
            
            .login-header h1 {
                font-size: 1.5em;
            }

            .remember-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .remember-info {
                margin-left: 0;
                margin-top: 5px;
            }
        }

        /* Animación para campos con credenciales recordadas */
        .field-restored {
            animation: fieldRestore 0.5s ease;
        }

        @keyframes fieldRestore {
            0% { 
                background: rgba(76, 175, 80, 0.1);
                transform: scale(1.02);
            }
            100% { 
                background: #f8f9fa;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>💰 Sistema Financiero</h1>
            <p>Acceso Seguro</p>
        </div>

        <div class="login-form">
            <?php if ($mensaje): ?>
                <div class="mensaje <?php echo $tipo_mensaje; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <?php if (!$usuarios_existen): ?>
                <div class="setup-link">
                    ⚠️ <a href="setup.php">Ejecutar configuración inicial</a>
                </div>
            <?php endif; ?>

            <?php if ($mostrar_usuarios_demo): ?>
                <div class="demo-info show" id="demoInfo">
                    <h4>👥 Usuarios de prueba disponibles:</h4>
                    <div class="credential" onclick="fillCredentials('admin', 'admin123')">
                        <strong>👑 Administrador:</strong> admin / admin123
                    </div>
                    <div class="credential" onclick="fillCredentials('usuario', 'usuario123')">
                        <strong>👤 Usuario:</strong> usuario / usuario123
                    </div>
                    <small style="color: #666; font-size: 11px;">
                        ⚠️ Cambiar estas credenciales en producción
                    </small>
                </div>
            <?php endif; ?>

            <div class="loading" id="loadingDiv">
                <div class="spinner"></div>
                <p>Verificando credenciales...</p>
            </div>

            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autocomplete="username"
                        placeholder="Ingresa tu usuario"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : htmlspecialchars($remembered_username); ?>"
                        maxlength="50"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="current-password"
                            placeholder="Ingresa tu contraseña"
                            maxlength="255"
                        >
                        <button type="button" class="password-toggle" id="passwordToggle" title="Mostrar/Ocultar contraseña">
                            👁️
                        </button>
                    </div>
                </div>

                <div class="remember-group">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember" 
                        class="remember-checkbox"
                        <?php echo $remember_checked ? 'checked' : ''; ?>
                    >
                    <label for="remember" class="remember-label">
                        Recordar mis credenciales
                    </label>
                    <span class="remember-info">30 días</span>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    🔑 Iniciar Sesión
                </button>
            </form>
        </div>

    </div>

    <script>
        // Variables globales
        let loginAttempts = 0;
        const maxAttempts = 3;
        let passwordVisible = false;

        // Función para mostrar/ocultar contraseña
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleBtn = document.getElementById('passwordToggle');
            
            passwordVisible = !passwordVisible;
            
            if (passwordVisible) {
                passwordField.type = 'text';
                toggleBtn.textContent = '🙈';
                toggleBtn.title = 'Ocultar contraseña';
            } else {
                passwordField.type = 'password';
                toggleBtn.textContent = '👁️';
                toggleBtn.title = 'Mostrar contraseña';
            }
        }

        // Función para cargar contraseña recordada
        function loadRememberedCredentials() {
            // Verificar si hay credenciales recordadas en cookies
            const cookies = document.cookie.split(';');
            let rememberedUsername = '';
            let rememberedPassword = '';
            let rememberChecked = false;
            
            cookies.forEach(cookie => {
                const [name, value] = cookie.trim().split('=');
                if (name === 'remember_username') {
                    rememberedUsername = decodeURIComponent(value);
                } else if (name === 'remember_password') {
                    rememberedPassword = atob(decodeURIComponent(value)); // Decodificar base64
                } else if (name === 'remember_checked') {
                    rememberChecked = value === 'true';
                }
            });
            
            if (rememberedUsername && rememberedPassword && rememberChecked) {
                const usernameField = document.getElementById('username');
                const passwordField = document.getElementById('password');
                const rememberCheckbox = document.getElementById('remember');
                
                // Llenar campos con animación
                if (usernameField && !usernameField.value) {
                    usernameField.value = rememberedUsername;
                    usernameField.classList.add('field-restored');
                    setTimeout(() => usernameField.classList.remove('field-restored'), 500);
                }
                
                if (passwordField && !passwordField.value) {
                    passwordField.value = rememberedPassword;
                    passwordField.classList.add('field-restored');
                    setTimeout(() => passwordField.classList.remove('field-restored'), 500);
                }
                
                if (rememberCheckbox) {
                    rememberCheckbox.checked = true;
                }
                
                console.log('✅ Credenciales recordadas cargadas');
            }
        }

        function fillCredentials(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
            
            // Añadir efecto visual
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            inputs.forEach(input => {
                input.style.borderColor = '#4CAF50';
                setTimeout(() => {
                    input.style.borderColor = '#ddd';
                }, 1000);
            });
        }

        // Auto-focus en el primer campo
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar evento del botón de mostrar/ocultar contraseña
            const passwordToggle = document.getElementById('passwordToggle');
            if (passwordToggle) {
                passwordToggle.addEventListener('click', togglePasswordVisibility);
            }
            
            // Cargar credenciales recordadas
            setTimeout(() => {
                loadRememberedCredentials();
            }, 100);
            
            // Auto-focus
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');
            
            if (usernameField && !usernameField.value) {
                usernameField.focus();
            } else if (passwordField && !passwordField.value) {
                passwordField.focus();
            }
            
            // PWA: Registrar Service Worker desde login si está disponible
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('✅ SW registrado desde login'))
                    .catch(err => console.log('⚠️ SW no disponible desde login'));
            }
        });

        // Manejar Enter para enviar formulario
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !document.getElementById('loginBtn').disabled) {
                const form = document.getElementById('loginForm');
                if (form) {
                    form.submit();
                }
            }
        });

        // Validación en tiempo real
        document.getElementById('username')?.addEventListener('input', function() {
            const username = this.value.trim();
            if (username.length > 0 && username.length < 3) {
                this.style.borderColor = '#ff9800';
            } else if (username.length >= 3) {
                this.style.borderColor = '#4CAF50';
            } else {
                this.style.borderColor = '#ddd';
            }
        });

        document.getElementById('password')?.addEventListener('input', function() {
            const password = this.value;
            if (password.length > 0 && password.length < 6) {
                this.style.borderColor = '#ff9800';
            } else if (password.length >= 6) {
                this.style.borderColor = '#4CAF50';
            } else {
                this.style.borderColor = '#ddd';
            }
        });

        // Manejar envío del formulario
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            const loadingDiv = document.getElementById('loadingDiv');
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            // Validaciones básicas
            if (!username || !password) {
                e.preventDefault();
                alert('⚠️ Por favor, completa todos los campos');
                return;
            }

            if (username.length < 3) {
                e.preventDefault();
                alert('⚠️ El nombre de usuario debe tener al menos 3 caracteres');
                document.getElementById('username').focus();
                return;
            }

            // Mostrar loading
            if (btn) {
                btn.disabled = true;
                btn.textContent = '🔄 Verificando...';
            }
            
            if (loadingDiv) {
                loadingDiv.classList.add('show');
            }
            
            console.log('📤 Enviando credenciales de login...');
            
            // Timeout de seguridad
            setTimeout(() => {
                if (btn && btn.disabled) {
                    btn.disabled = false;
                    btn.textContent = '🔑 Iniciar Sesión';
                    if (loadingDiv) {
                        loadingDiv.classList.remove('show');
                    }
                    console.warn('⚠️ Login timeout - reactivando botón');
                }
            }, 10000);
        });

        // Contador de intentos fallidos
        <?php if ($tipo_mensaje === 'error' && !$login_exitoso): ?>
        loginAttempts++;
        console.log('❌ Intento de login fallido:', loginAttempts);
        
        if (loginAttempts >= maxAttempts) {
            const loginBtn = document.getElementById('loginBtn');
            if (loginBtn) {
                loginBtn.textContent = '⚠️ Demasiados intentos';
                loginBtn.style.background = '#f44336';
                
                setTimeout(() => {
                    loginBtn.textContent = '🔑 Iniciar Sesión';
                    loginBtn.style.background = 'linear-gradient(135deg, #4CAF50, #45a049)';
                    loginAttempts = 0;
                }, 5000);
            }
        }
        <?php endif; ?>

        // Verificar conexión con el servidor cada 30 segundos
        function checkServerConnection() {
            fetch('/auth.php?action=check', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.authenticated) {
                        console.log('✅ Usuario ya autenticado, redirigiendo...');
                        window.location.replace('/index.php');
                    }
                })
                .catch(error => {
                    console.log('🌐 Server check failed:', error);
                });
        }

        setInterval(checkServerConnection, 30000);

        // Limpiar campos sensibles al salir (excepto si está marcado recordar)
        window.addEventListener('beforeunload', function() {
            const rememberCheckbox = document.getElementById('remember');
            const passwordField = document.getElementById('password');
            
            // Solo limpiar si no está marcado recordar
            if (passwordField && (!rememberCheckbox || !rememberCheckbox.checked)) {
                passwordField.value = '';
            }
        });

        // Detectar si está corriendo como PWA instalada
        if (window.matchMedia('(display-mode: standalone)').matches) {
            console.log('📱 Ejecutándose como PWA instalada');
            document.body.classList.add('pwa-mode');
        }

        console.log('✅ Login mejorado con mostrar/ocultar contraseña y recordar credenciales');
    </script>
</body>
</html>