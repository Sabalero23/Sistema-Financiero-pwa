<?php
// logout.php - OPTIMIZADO: Logout rápido y específico por sesión

// Headers para evitar cache pero sin ser agresivos
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Variables para logging mínimo
$session_id = session_id();
$usuario_info = null;
$logout_source = $_GET['source'] ?? 'unknown';

// Obtener información del usuario ANTES de destruir la sesión
if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
    $usuario_info = [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? 'usuario',
        'nombre' => $_SESSION['nombre'] ?? 'Usuario'
    ];
    
    error_log("LOGOUT RÁPIDO - Usuario: {$usuario_info['username']}, Source: $logout_source");
}

// PASO 1: Solo desactivar la sesión ACTUAL en BD (no todas las sesiones del usuario)
try {
    require_once 'database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn && $session_id) {
        // Solo desactivar la sesión específica actual
        $stmt = $conn->prepare("UPDATE sesiones SET activa = 0, ultima_actividad = NOW() WHERE id = ? LIMIT 1");
        $stmt->execute([$session_id]);
        error_log("LOGOUT: Solo sesión actual $session_id desactivada");
    }
} catch (Exception $e) {
    error_log("Error desactivando sesión específica: " . $e->getMessage());
}

// PASO 2: Limpiar variables de sesión PHP
$_SESSION = array();

// PASO 3: Eliminar SOLO la cookie de la sesión actual
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// PASO 4: Destruir solo la sesión PHP actual
session_destroy();

// Log final
if ($usuario_info) {
    error_log("LOGOUT COMPLETADO RÁPIDO - Usuario: {$usuario_info['username']}");
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando Sesión...</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            color: white;
        }
        
        .logout-container {
            text-align: center;
            background: rgba(255,255,255,0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 90%;
        }
        
        .logout-icon {
            font-size: 3em;
            margin-bottom: 20px;
            animation: wave 1.5s ease-in-out infinite;
        }
        
        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(20deg); }
        }
        
        h1 {
            font-size: 1.8em;
            margin-bottom: 15px;
        }
        
        p {
            margin: 10px 0;
            opacity: 0.9;
        }
        
        .redirect-timer {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .countdown {
            font-size: 2em;
            font-weight: bold;
            color: #4CAF50;
        }
        
        .manual-link {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
        }
        
        .manual-link:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        .success-check {
            display: inline-block;
            width: 20px;
            height: 20px;
            background: #4CAF50;
            border-radius: 50%;
            position: relative;
            margin-right: 8px;
        }
        
        .success-check::after {
            content: "✓";
            position: absolute;
            color: white;
            font-size: 12px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">👋</div>
        <h1>Sesión Cerrada</h1>
        
        <div style="margin: 20px 0;">
            <div style="display: flex; align-items: center; justify-content: center; margin: 10px 0;">
                <div class="success-check"></div>
                <span>Sesión cerrada correctamente</span>
            </div>
        </div>
        
        <div class="redirect-timer">
            <p><strong>Redirigiendo en:</strong></p>
            <div class="countdown" id="countdown">2</div>
            <p><small>segundos</small></p>
        </div>
        
        <a href="login.php?clean=1" class="manual-link" id="manualLink">
            🔑 Ir al Login Ahora
        </a>
        
        <?php if ($usuario_info): ?>
        <p style="margin-top: 20px; font-size: 0.9em; opacity: 0.7;">
            Hasta pronto, <strong><?php echo htmlspecialchars($usuario_info['nombre']); ?></strong>
        </p>
        <?php endif; ?>
    </div>

    <script>
        console.log('🧹 LOGOUT OPTIMIZADO - Limpieza rápida y específica');
        
        let countdown = 2;
        const countdownElement = document.getElementById('countdown');
        const manualLink = document.getElementById('manualLink');
        
        // NO limpiar storage agresivamente - solo los datos específicos de sesión
        try {
            // Solo limpiar datos específicos de la sesión actual
            localStorage.removeItem('current_session');
            localStorage.removeItem('user_preferences');
            localStorage.removeItem('temp_data');
            
            // NO limpiar: pending_transactions, pwa_settings, etc.
            // Esto permite que otras pestañas sigan funcionando
            
            console.log('✅ Datos específicos de sesión limpiados');
        } catch (error) {
            console.log('⚠️ Error mínimo en limpieza:', error);
        }
        
        // Prevenir navegación hacia atrás solo en esta pestaña
        history.pushState(null, null, window.location.href);
        window.onpopstate = function() {
            window.location.replace('login.php?back=1');
        };
        
        // Contador regresivo más rápido
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                redirectToLogin();
            }
        }, 1000);
        
        // Función de redirección optimizada
        function redirectToLogin() {
            console.log('🔄 Redirección rápida a login...');
            
            // Redirección inmediata sin delays
            window.location.replace('login.php?logout=success&t=' + Date.now());
        }
        
        // Redirección manual inmediata
        manualLink.addEventListener('click', function(e) {
            e.preventDefault();
            clearInterval(timer);
            redirectToLogin();
        });
        
        // Auto-redirección de emergencia si algo falla
        setTimeout(() => {
            console.log('🔄 Redirección de emergencia');
            window.location.href = 'login.php?emergency=1';
        }, 4000); // Solo 4 segundos máximo
        
        console.log('✅ LOGOUT OPTIMIZADO - Listo para redirección rápida');
        console.log('🎯 Source:', '<?php echo $logout_source; ?>');
        console.log('👤 Usuario:', <?php echo json_encode($usuario_info); ?>);
    </script>
</body>
</html>