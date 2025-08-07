<?php
// auth.php - OPTIMIZADO
session_start();

// Headers optimizados
if (!headers_sent()) {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

require_once 'database.php';

class Auth {
    private $db;
    private $sessionTimeout = 1800; // 30 minutos
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function login($username, $password) {
        if (!$this->db) {
            return [
                'success' => false,
                'message' => 'Error de conexión a la base de datos'
            ];
        }
        
        $database = new Database();
        $usuario = $database->authenticateUser($username, $password);
        
        if ($usuario) {
            // Generar ID de sesión único
            $session_id = session_id();
            
            // Almacenar datos en la sesión
            $_SESSION['logged_in'] = true;
            $_SESSION['session_id'] = $session_id;
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            
            // Crear registro de sesión en BD
            $ip_address = $this->getClientIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $database->createSession($session_id, $usuario['id'], $ip_address, $user_agent);
            
            return [
                'success' => true,
                'user' => $usuario,
                'session_id' => $session_id
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Usuario o contraseña incorrectos'
        ];
    }
    
    public function verificarAutenticacion($updateActivity = true) {
        // Verificar si el usuario está logueado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        
        // Si no hay conexión a BD, permitir sesión básica por timeout reducido
        if (!$this->db) {
            $timeout = 300; // 5 minutos sin BD
            if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout) {
                $this->logout(false); // No redireccionar automáticamente
                return false;
            }
            return true;
        }
        
        // Verificar timeout de sesión antes de consultar BD
        $currentTime = time();
        $lastActivity = $_SESSION['last_activity'] ?? $_SESSION['login_time'] ?? $currentTime;
        
        if (($currentTime - $lastActivity) > $this->sessionTimeout) {
            // Sesión expirada por timeout
            $this->logout(false);
            return false;
        }
        
        // Verificar sesión en base de datos (solo ocasionalmente para performance)
        $shouldCheckDB = !isset($_SESSION['last_db_check']) || 
                        (time() - $_SESSION['last_db_check']) > 300; // Cada 5 minutos
        
        if ($shouldCheckDB && isset($_SESSION['session_id'])) {
            $database = new Database();
            $session_data = $database->validateSession($_SESSION['session_id']);
            
            if (!$session_data) {
                // Sesión inválida en BD
                $this->logout(false);
                return false;
            }
            
            $_SESSION['last_db_check'] = time();
        }
        
        // Actualizar actividad si se solicita
        if ($updateActivity) {
            $_SESSION['last_activity'] = $currentTime;
        }
        
        return true;
    }
    
    public function requerirAutenticacion() {
        if (!$this->verificarAutenticacion()) {
            if ($this->isAjaxRequest()) {
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8', true);
                    http_response_code(401);
                }
                
                echo json_encode([
                    'success' => false,
                    'message' => 'Sesión expirada',
                    'redirect' => 'login.php',
                    'authenticated' => false,
                    'code' => 'SESSION_EXPIRED'
                ]);
                exit;
            } else {
                if (!headers_sent()) {
                    header('Location: login.php?expired=1', true, 302);
                    exit;
                } else {
                    echo '<script>window.location.replace("login.php?expired=1");</script>';
                    exit;
                }
            }
        }
    }
    
    public function obtenerUsuarioActual() {
        if ($this->verificarAutenticacion(false)) { // No actualizar actividad aquí
            return [
                'id' => $_SESSION['user_id'] ?? 1,
                'username' => $_SESSION['username'] ?? 'usuario',
                'nombre' => $_SESSION['nombre'] ?? 'Usuario',
                'email' => $_SESSION['email'] ?? 'usuario@sistema.com',
                'rol' => $_SESSION['rol'] ?? 'usuario',
                'login_time' => $_SESSION['login_time'] ?? time(),
                'session_id' => $_SESSION['session_id'] ?? session_id()
            ];
        }
        return null;
    }
    
    public function logout($autoRedirect = true) {
        $session_id = $_SESSION['session_id'] ?? session_id();
        
        // OPTIMIZADO: Solo desactivar la sesión ACTUAL, no todas las del usuario
        if ($session_id && $this->db) {
            try {
                $database = new Database();
                // Solo desactivar esta sesión específica
                $database->destroySession($session_id);
                error_log("Logout: Sesión específica $session_id desactivada");
            } catch (Exception $e) {
                error_log("Error en logout específico: " . $e->getMessage());
            }
        }
        
        // Limpiar variables de sesión PHP
        $_SESSION = array();
        
        // Destruir solo la sesión actual
        session_destroy();
        
        // Redireccionar solo si se solicita y headers no enviados
        if ($autoRedirect && !$this->isAjaxRequest() && !headers_sent()) {
            header('Location: login.php?logout=1', true, 302);
            exit;
        }
    }
    
    public function isAdmin() {
        $usuario = $this->obtenerUsuarioActual();
        return $usuario && $usuario['rol'] === 'admin';
    }
    
    public function requerirAdmin() {
        $this->requerirAutenticacion();
        
        if (!$this->isAdmin()) {
            if ($this->isAjaxRequest()) {
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8', true);
                    http_response_code(403);
                }
                
                echo json_encode([
                    'success' => false,
                    'message' => 'Acceso denegado. Se requieren permisos de administrador.'
                ]);
                exit;
            } else {
                if (!headers_sent()) {
                    http_response_code(403);
                }
                
                echo "<!DOCTYPE html>
                <html>
                <head>
                    <title>Acceso Denegado</title>
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                        .error { color: #f44336; }
                        .btn { background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                    </style>
                </head>
                <body>
                    <h1 class='error'>⚠️ Acceso Denegado</h1>
                    <p>No tienes permisos suficientes para acceder a esta página.</p>
                    <a href='index.php' class='btn'>🏠 Volver al Inicio</a>
                </body>
                </html>";
                exit;
            }
        }
    }
    
    // NUEVO: Método para obtener estadísticas de sesión
    public function getSessionStats() {
        if (!$this->verificarAutenticacion(false)) {
            return null;
        }
        
        $currentTime = time();
        $loginTime = $_SESSION['login_time'] ?? $currentTime;
        $lastActivity = $_SESSION['last_activity'] ?? $currentTime;
        
        return [
            'session_duration' => $currentTime - $loginTime,
            'time_since_activity' => $currentTime - $lastActivity,
            'time_remaining' => $this->sessionTimeout - ($currentTime - $lastActivity),
            'expires_at' => $lastActivity + $this->sessionTimeout
        ];
    }
    
    // NUEVO: Método para renovar sesión
    public function renewSession() {
        if ($this->verificarAutenticacion(false)) {
            $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    }
    
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    private function getClientIP() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

// Funciones de compatibilidad optimizadas
function verificarAutenticacion($updateActivity = true) {
    $auth = new Auth();
    return $auth->verificarAutenticacion($updateActivity);
}

function requerirAutenticacion() {
    $auth = new Auth();
    return $auth->requerirAutenticacion();
}

function obtenerUsuarioActual() {
    $auth = new Auth();
    return $auth->obtenerUsuarioActual();
}

function cerrarSesion($autoRedirect = true) {
    $auth = new Auth();
    return $auth->logout($autoRedirect);
}

function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Si se llama directamente este archivo
if (basename($_SERVER['PHP_SELF']) === 'auth.php') {
    $auth = new Auth();
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'logout':
                // OPTIMIZADO: Logout específico sin redirección automática
                $auth->logout(false);
                
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                echo json_encode([
                    'success' => true,
                    'message' => 'Sesión cerrada correctamente',
                    'redirect' => 'login.php'
                ]);
                exit;
                
            case 'check':
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                
                $isAuthenticated = $auth->verificarAutenticacion(false); // No actualizar actividad
                
                echo json_encode([
                    'authenticated' => $isAuthenticated,
                    'user' => $isAuthenticated ? $auth->obtenerUsuarioActual() : null,
                    'is_admin' => $isAuthenticated ? $auth->isAdmin() : false,
                    'timestamp' => time()
                ]);
                exit;
                
            case 'status':
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                
                $usuario = $auth->obtenerUsuarioActual();
                $sessionStats = $auth->getSessionStats();
                
                if ($usuario && $sessionStats) {
                    echo json_encode([
                        'success' => true,
                        'user' => $usuario,
                        'session_stats' => $sessionStats,
                        'timestamp' => time()
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Sesión no válida',
                        'code' => 'SESSION_INVALID',
                        'timestamp' => time()
                    ]);
                }
                exit;
                
            case 'renew':
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                
                if ($auth->renewSession()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Sesión renovada',
                        'timestamp' => time()
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'message' => 'No se pudo renovar la sesión',
                        'code' => 'SESSION_RENEWAL_FAILED',
                        'timestamp' => time()
                    ]);
                }
                exit;
                
            case 'ping':
                // Endpoint ligero para verificar conectividad
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                
                echo json_encode([
                    'pong' => true,
                    'timestamp' => time(),
                    'server_time' => date('Y-m-d H:i:s')
                ]);
                exit;
                
            default:
                $auth->requerirAutenticacion();
        }
    } else {
        $auth->requerirAutenticacion();
    }
}
?>