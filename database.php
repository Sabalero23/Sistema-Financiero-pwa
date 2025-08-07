<?php
// database.php - Configuración y setup de la base de datos

class Database {
    private $host = 'localhost';
    private $db_name = 'finanzaspwa';
    private $username = 'finanzaspwa';
    private $password = 'iX5AwWPASRak3pYH';
    private $conn;
    
    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }
        
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
            $this->conn = null;
        }
        
        return $this->conn;
    }
    
    private function ensureConnection() {
        if ($this->conn === null) {
            $this->getConnection();
        }
        return $this->conn !== null;
    }
    
    public function createTables() {
        if (!$this->ensureConnection()) {
            error_log("No se pudo establecer conexión para crear tablas");
            return false;
        }
        
        try {
            // Crear tabla de usuarios
            $sql_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                nombre VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                rol ENUM('admin', 'usuario') DEFAULT 'usuario',
                activo BOOLEAN DEFAULT TRUE,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ultimo_acceso TIMESTAMP NULL,
                intentos_fallidos INT DEFAULT 0,
                bloqueado_hasta TIMESTAMP NULL
            )";
            
            $this->conn->exec($sql_usuarios);
            
            // Crear tabla de transacciones - SIN FOREIGN KEY
            $sql_transacciones = "CREATE TABLE IF NOT EXISTS transacciones (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT DEFAULT 1,
                tipo ENUM('ingreso', 'egreso') NOT NULL,
                monto DECIMAL(10,2) NOT NULL,
                titulo VARCHAR(255) NOT NULL,
                descripcion TEXT,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                metodo_creacion ENUM('manual', 'audio') DEFAULT 'manual'
            )";
            
            $this->conn->exec($sql_transacciones);
            
            // Crear tabla de sesiones
            $sql_sesiones = "CREATE TABLE IF NOT EXISTS sesiones (
                id VARCHAR(128) PRIMARY KEY,
                usuario_id INT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                activa BOOLEAN DEFAULT TRUE
            )";
            
            $this->conn->exec($sql_sesiones);
            
            return true;
        } catch(PDOException $e) {
            error_log("Error al crear tablas: " . $e->getMessage());
            return false;
        }
    }
    
    public function createDefaultUsers() {
        if (!$this->ensureConnection()) {
            error_log("No se pudo establecer conexión para crear usuarios");
            return false;
        }
        
        try {
            // Verificar si ya hay usuarios
            $stmt = $this->conn->query("SELECT COUNT(*) FROM usuarios");
            $count = $stmt->fetchColumn();
            
            if ($count == 0) {
                // Crear usuarios por defecto
                $usuarios_default = [
                    [
                        'username' => 'admin',
                        'password' => password_hash('admin123', PASSWORD_DEFAULT),
                        'nombre' => 'Administrador del Sistema',
                        'email' => 'admin@finanzas.com',
                        'rol' => 'admin'
                    ],
                    [
                        'username' => 'usuario',
                        'password' => password_hash('usuario123', PASSWORD_DEFAULT),
                        'nombre' => 'Usuario Demo',
                        'email' => 'usuario@finanzas.com',
                        'rol' => 'usuario'
                    ]
                ];
                
                $stmt = $this->conn->prepare("
                    INSERT INTO usuarios (username, password, nombre, email, rol) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                foreach ($usuarios_default as $usuario) {
                    $stmt->execute([
                        $usuario['username'],
                        $usuario['password'],
                        $usuario['nombre'],
                        $usuario['email'],
                        $usuario['rol']
                    ]);
                }
                
                return true;
            }
            
            return true; // Ya hay usuarios
            
        } catch(PDOException $e) {
            error_log("Error al crear usuarios por defecto: " . $e->getMessage());
            return false;
        }
    }
    
    public function authenticateUser($username, $password) {
        if (!$this->ensureConnection()) {
            error_log("No se pudo establecer conexión para autenticar usuario");
            return false;
        }
        
        try {
            // Verificar si el usuario está bloqueado
            $stmt = $this->conn->prepare("
                SELECT id, username, password, nombre, email, rol, activo, 
                       intentos_fallidos, bloqueado_hasta 
                FROM usuarios 
                WHERE username = ? AND activo = TRUE
            ");
            $stmt->execute([$username]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                return false; // Usuario no existe o está inactivo
            }
            
            // Verificar si está bloqueado
            if ($usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
                return false; // Usuario bloqueado
            }
            
            // Verificar contraseña
            if (password_verify($password, $usuario['password'])) {
                // Login exitoso - resetear intentos fallidos
                $stmt = $this->conn->prepare("
                    UPDATE usuarios 
                    SET intentos_fallidos = 0, bloqueado_hasta = NULL, ultimo_acceso = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$usuario['id']]);
                
                // Retornar datos del usuario (sin password)
                unset($usuario['password']);
                return $usuario;
            } else {
                // Login fallido - incrementar intentos
                $intentos = $usuario['intentos_fallidos'] + 1;
                $bloqueado_hasta = null;
                
                // Bloquear después de 5 intentos fallidos por 15 minutos
                if ($intentos >= 5) {
                    $bloqueado_hasta = date('Y-m-d H:i:s', time() + 900); // 15 minutos
                }
                
                $stmt = $this->conn->prepare("
                    UPDATE usuarios 
                    SET intentos_fallidos = ?, bloqueado_hasta = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$intentos, $bloqueado_hasta, $usuario['id']]);
                
                return false;
            }
            
        } catch(PDOException $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserById($id) {
        if (!$this->ensureConnection()) {
            error_log("No se pudo establecer conexión para obtener usuario");
            return false;
        }
        
        try {
            $stmt = $this->conn->prepare("
                SELECT id, username, nombre, email, rol, activo, fecha_creacion, ultimo_acceso 
                FROM usuarios 
                WHERE id = ? AND activo = TRUE
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            return false;
        }
    }
    
    public function createSession($session_id, $usuario_id, $ip_address, $user_agent) {
        if (!$this->ensureConnection()) {
            error_log("No se pudo establecer conexión para crear sesión");
            return false;
        }
        
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO sesiones (id, usuario_id, ip_address, user_agent) 
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$session_id, $usuario_id, $ip_address, $user_agent]);
        } catch(PDOException $e) {
            error_log("Error al crear sesión: " . $e->getMessage());
            return false;
        }
    }
    
    public function validateSession($session_id) {
        if (!$this->ensureConnection()) {
            error_log("No se pudo establecer conexión para validar sesión");
            return false;
        }
        
        try {
            $stmt = $this->conn->prepare("
                SELECT s.usuario_id, u.username, u.nombre, u.email, u.rol 
                FROM sesiones s 
                JOIN usuarios u ON s.usuario_id = u.id 
                WHERE s.id = ? AND s.activa = TRUE AND u.activo = TRUE
                AND s.ultima_actividad > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ");
            $stmt->execute([$session_id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                // Actualizar última actividad
                $stmt = $this->conn->prepare("
                    UPDATE sesiones 
                    SET ultima_actividad = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$session_id]);
            }
            
            return $resultado;
        } catch(PDOException $e) {
            error_log("Error al validar sesión: " . $e->getMessage());
            return false;
        }
    }
    
    public function destroySession($session_id) {
        if (!$this->ensureConnection()) {
            error_log("No se pudo establecer conexión para destruir sesión");
            return false;
        }
        
        try {
            $stmt = $this->conn->prepare("UPDATE sesiones SET activa = FALSE WHERE id = ?");
            return $stmt->execute([$session_id]);
        } catch(PDOException $e) {
            error_log("Error al destruir sesión: " . $e->getMessage());
            return false;
        }
    }
    
    public function cleanupSessions() {
        if (!$this->ensureConnection()) {
            error_log("No se pudo establecer conexión para limpiar sesiones");
            return false;
        }
        
        try {
            // Marcar como inactivas las sesiones expiradas
            $stmt = $this->conn->prepare("
                UPDATE sesiones 
                SET activa = FALSE 
                WHERE ultima_actividad < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ");
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error al limpiar sesiones: " . $e->getMessage());
            return false;
        }
    }
    
    public function testConnection() {
        if ($this->ensureConnection()) {
            try {
                $stmt = $this->conn->query("SELECT 1");
                return $stmt !== false;
            } catch(PDOException $e) {
                error_log("Error al probar conexión: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }
}

// NO ejecutar código aquí para evitar salida indeseada
?>