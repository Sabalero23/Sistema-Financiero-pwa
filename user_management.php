<?php
// user_management.php - Gestión de usuarios (solo administradores)

require_once 'auth.php';
$auth = new Auth();
$auth->requerirAdmin(); // Solo administradores pueden acceder

require_once 'database.php';
$database = new Database();
$conn = $database->getConnection();

// Obtener usuario actual ANTES de procesar acciones
$usuario_actual = $auth->obtenerUsuarioActual();

$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones
if ($_POST && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'crear_usuario':
            if (isset($_POST['username'], $_POST['password'], $_POST['nombre'], $_POST['email'], $_POST['rol'])) {
                $username = trim($_POST['username']);
                $password = $_POST['password'];
                $nombre = trim($_POST['nombre']);
                $email = trim($_POST['email']);
                $rol = $_POST['rol'];
                
                if (!empty($username) && !empty($password) && !empty($nombre) && !empty($email)) {
                    try {
                        $stmt = $conn->prepare("
                            INSERT INTO usuarios (username, password, nombre, email, rol) 
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        
                        if ($stmt->execute([$username, $password_hash, $nombre, $email, $rol])) {
                            $mensaje = "✅ Usuario '$username' creado correctamente";
                            $tipo_mensaje = 'success';
                        } else {
                            $mensaje = "❌ Error al crear usuario '$username'";
                            $tipo_mensaje = 'error';
                        }
                    } catch (Exception $e) {
                        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                            $mensaje = "❌ El usuario '$username' ya existe";
                        } else {
                            $mensaje = "❌ Error: " . $e->getMessage();
                        }
                        $tipo_mensaje = 'error';
                    }
                } else {
                    $mensaje = "❌ Todos los campos son obligatorios";
                    $tipo_mensaje = 'error';
                }
            }
            break;
            
        case 'toggle_usuario':
            if (isset($_POST['user_id'])) {
                $user_id = (int)$_POST['user_id'];
                try {
                    $stmt = $conn->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?");
                    if ($stmt->execute([$user_id])) {
                        $mensaje = "✅ Estado del usuario actualizado";
                        $tipo_mensaje = 'success';
                    } else {
                        $mensaje = "❌ Error al actualizar usuario";
                        $tipo_mensaje = 'error';
                    }
                } catch (Exception $e) {
                    $mensaje = "❌ Error: " . $e->getMessage();
                    $tipo_mensaje = 'error';
                }
            }
            break;
            
        case 'reset_password':
            if (isset($_POST['user_id'], $_POST['new_password'])) {
                $user_id = (int)$_POST['user_id'];
                $new_password = $_POST['new_password'];
                
                if (strlen($new_password) >= 6) {
                    try {
                        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("
                            UPDATE usuarios 
                            SET password = ?, intentos_fallidos = 0, bloqueado_hasta = NULL 
                            WHERE id = ?
                        ");
                        
                        if ($stmt->execute([$password_hash, $user_id])) {
                            $mensaje = "✅ Contraseña actualizada correctamente";
                            $tipo_mensaje = 'success';
                        } else {
                            $mensaje = "❌ Error al actualizar contraseña";
                            $tipo_mensaje = 'error';
                        }
                    } catch (Exception $e) {
                        $mensaje = "❌ Error: " . $e->getMessage();
                        $tipo_mensaje = 'error';
                    }
                } else {
                    $mensaje = "❌ La contraseña debe tener al menos 6 caracteres";
                    $tipo_mensaje = 'error';
                }
            }
            break;
            
        case 'desbloquear_usuario':
            if (isset($_POST['user_id'])) {
                $user_id = (int)$_POST['user_id'];
                try {
                    $stmt = $conn->prepare("
                        UPDATE usuarios 
                        SET intentos_fallidos = 0, bloqueado_hasta = NULL 
                        WHERE id = ?
                    ");
                    
                    if ($stmt->execute([$user_id])) {
                        $mensaje = "✅ Usuario desbloqueado correctamente";
                        $tipo_mensaje = 'success';
                    } else {
                        $mensaje = "❌ Error al desbloquear usuario";
                        $tipo_mensaje = 'error';
                    }
                } catch (Exception $e) {
                    $mensaje = "❌ Error: " . $e->getMessage();
                    $tipo_mensaje = 'error';
                }
            }
            break;
            
        case 'actualizar_perfil':
            if (isset($_POST['user_id'], $_POST['username'], $_POST['nombre'], $_POST['email'])) {
                $user_id = (int)$_POST['user_id'];
                $username = trim($_POST['username']);
                $nombre = trim($_POST['nombre']);
                $email = trim($_POST['email']);
                
                // Solo permitir actualizar su propio perfil
                if ($user_id == $usuario_actual['id']) {
                    if (!empty($username) && !empty($nombre) && !empty($email)) {
                        // Validar formato del username
                        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
                            $mensaje = "❌ El usuario debe tener 3-50 caracteres y solo letras, números y guiones bajos";
                            $tipo_mensaje = 'error';
                        } else {
                            try {
                                $stmt = $conn->prepare("
                                    UPDATE usuarios 
                                    SET username = ?, nombre = ?, email = ? 
                                    WHERE id = ?
                                ");
                                
                                if ($stmt->execute([$username, $nombre, $email, $user_id])) {
                                    $mensaje = "✅ Perfil actualizado correctamente";
                                    $tipo_mensaje = 'success';
                                    
                                    // Actualizar la sesión con el nuevo username
                                    $_SESSION['username'] = $username;
                                } else {
                                    $mensaje = "❌ Error al actualizar perfil";
                                    $tipo_mensaje = 'error';
                                }
                            } catch (Exception $e) {
                                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                                    if (strpos($e->getMessage(), 'username') !== false) {
                                        $mensaje = "❌ El nombre de usuario '$username' ya está en uso";
                                    } else {
                                        $mensaje = "❌ El email ya está en uso";
                                    }
                                } else {
                                    $mensaje = "❌ Error: " . $e->getMessage();
                                }
                                $tipo_mensaje = 'error';
                            }
                        }
                    } else {
                        $mensaje = "❌ Todos los campos son obligatorios";
                        $tipo_mensaje = 'error';
                    }
                } else {
                    $mensaje = "❌ Solo puedes actualizar tu propio perfil";
                    $tipo_mensaje = 'error';
                }
            }
            break;

        case 'actualizar_usuario_admin':
            // Nueva acción: Los admins pueden editar cualquier usuario
            if (isset($_POST['user_id'], $_POST['username'], $_POST['nombre'], $_POST['email'], $_POST['rol'])) {
                $user_id = (int)$_POST['user_id'];
                $username = trim($_POST['username']);
                $nombre = trim($_POST['nombre']);
                $email = trim($_POST['email']);
                $rol = $_POST['rol'];
                
                if (!empty($username) && !empty($nombre) && !empty($email)) {
                    // Validar formato del username
                    if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
                        $mensaje = "❌ El usuario debe tener 3-50 caracteres y solo letras, números y guiones bajos";
                        $tipo_mensaje = 'error';
                    } else {
                        try {
                            $stmt = $conn->prepare("
                                UPDATE usuarios 
                                SET username = ?, nombre = ?, email = ?, rol = ? 
                                WHERE id = ?
                            ");
                            
                            if ($stmt->execute([$username, $nombre, $email, $rol, $user_id])) {
                                $mensaje = "✅ Usuario actualizado correctamente";
                                $tipo_mensaje = 'success';
                            } else {
                                $mensaje = "❌ Error al actualizar usuario";
                                $tipo_mensaje = 'error';
                            }
                        } catch (Exception $e) {
                            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                                if (strpos($e->getMessage(), 'username') !== false) {
                                    $mensaje = "❌ El nombre de usuario '$username' ya está en uso";
                                } else {
                                    $mensaje = "❌ El email ya está en uso";
                                }
                            } else {
                                $mensaje = "❌ Error: " . $e->getMessage();
                            }
                            $tipo_mensaje = 'error';
                        }
                    }
                } else {
                    $mensaje = "❌ Todos los campos son obligatorios";
                    $tipo_mensaje = 'error';
                }
            }
            break;

        case 'eliminar_usuario':
            if (isset($_POST['user_id'], $_POST['confirm_username'])) {
                $user_id = (int)$_POST['user_id'];
                $confirm_username = trim($_POST['confirm_username']);
                
                // Verificar que no sea su propia cuenta
                if ($user_id == $usuario_actual['id']) {
                    $mensaje = "❌ No puedes eliminar tu propia cuenta";
                    $tipo_mensaje = 'error';
                } else {
                    try {
                        // Obtener datos del usuario a eliminar
                        $stmt = $conn->prepare("SELECT username, nombre, rol FROM usuarios WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $usuario_eliminar = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (!$usuario_eliminar) {
                            $mensaje = "❌ Usuario no encontrado";
                            $tipo_mensaje = 'error';
                        } else {
                            // Verificar confirmación de username
                            if ($confirm_username !== $usuario_eliminar['username']) {
                                $mensaje = "❌ El nombre de usuario de confirmación no coincide";
                                $tipo_mensaje = 'error';
                            } else {
                                // Contar administradores
                                $stmt = $conn->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin' AND activo = 1");
                                $total_admins = $stmt->fetchColumn();
                                
                                // Prevenir eliminar el último administrador
                                if ($usuario_eliminar['rol'] === 'admin' && $total_admins <= 1) {
                                    $mensaje = "❌ No se puede eliminar el último administrador del sistema";
                                    $tipo_mensaje = 'error';
                                } else {
                                    // Eliminar usuario
                                    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
                                    
                                    if ($stmt->execute([$user_id])) {
                                        $mensaje = "✅ Usuario '{$usuario_eliminar['username']}' eliminado correctamente";
                                        $tipo_mensaje = 'success';
                                        
                                        // Log de la eliminación (opcional)
                                        error_log("Usuario eliminado: {$usuario_eliminar['username']} por {$usuario_actual['username']}");
                                    } else {
                                        $mensaje = "❌ Error al eliminar usuario";
                                        $tipo_mensaje = 'error';
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $mensaje = "❌ Error: " . $e->getMessage();
                        $tipo_mensaje = 'error';
                    }
                }
            }
            break;
    }
}

// Obtener usuarios
$usuarios = [];
try {
    $stmt = $conn->query("
        SELECT id, username, nombre, email, rol, activo, fecha_creacion, ultimo_acceso, 
               intentos_fallidos, bloqueado_hasta
        FROM usuarios 
        ORDER BY fecha_creacion DESC
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $mensaje = "❌ Error al cargar usuarios: " . $e->getMessage();
    $tipo_mensaje = 'error';
}

// Actualizar datos del usuario actual después de posibles cambios
$usuario_actual = $auth->obtenerUsuarioActual();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema Financiero</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 30px;
            position: relative;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .user-info {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 14px;
            background: rgba(255,255,255,0.1);
            padding: 10px 15px;
            border-radius: 25px;
        }

        .nav-links {
            margin-top: 20px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
            padding: 8px 15px;
            border-radius: 20px;
            background: rgba(255,255,255,0.1);
            transition: all 0.3s;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
        }

        .content {
            padding: 40px;
        }

        .mensaje {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .mensaje.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensaje.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-card {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 2px solid #e9ecef;
        }

        .form-card h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .btn {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: #333;
        }

        .btn-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
        }

        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .status-blocked {
            background: #fff3cd;
            color: #856404;
        }

        .role-admin {
            color: #dc3545;
            font-weight: bold;
        }

        .role-user {
            color: #28a745;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid #bee5eb;
        }

        @media (max-width: 768px) {
            .header {
                padding: 20px;
                text-align: center;
            }

            .user-info {
                position: static;
                margin-top: 20px;
                text-align: center;
            }

            .content {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="user-info">
                👤 <?php echo htmlspecialchars($usuario_actual['nombre']); ?> (<?php echo htmlspecialchars($usuario_actual['username']); ?>)
                <a href="auth.php?action=logout" style="color: white; margin-left: 10px;">🚪 Salir</a>
            </div>
             <br>
            <br>           
            <h1>👥 Gestión de Usuarios</h1>
            <p>Administración de usuarios del sistema</p>
            
            <div class="nav-links">
                <a href="index.php">🏠 Inicio</a>
                <a href="setup.php">🔧 Setup</a>
                <a href="#" onclick="mostrarEstadisticas()">📊 Estadísticas</a>
            </div>
        </div>

        <div class="content">
            <?php if ($mensaje): ?>
                <div class="mensaje <?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario para crear nuevo usuario -->
            <div class="form-card">
                <h3>➕ Crear Nuevo Usuario</h3>
                
                <form method="POST" id="createUserForm">
                    <input type="hidden" name="action" value="crear_usuario">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>👤 Usuario:</label>
                            <input type="text" name="username" required maxlength="50" 
                                   placeholder="nombre_usuario" pattern="[a-zA-Z0-9_]+" 
                                   title="Solo letras, números y guiones bajos">
                        </div>
                        
                        <div class="form-group">
                            <label>🔐 Contraseña:</label>
                            <input type="password" name="password" required minlength="6" 
                                   placeholder="Mínimo 6 caracteres">
                        </div>
                        
                        <div class="form-group">
                            <label>📝 Nombre Completo:</label>
                            <input type="text" name="nombre" required maxlength="100" 
                                   placeholder="Nombre y Apellido">
                        </div>
                        
                        <div class="form-group">
                            <label>📧 Email:</label>
                            <input type="email" name="email" required maxlength="100" 
                                   placeholder="usuario@ejemplo.com">
                        </div>
                        
                        <div class="form-group">
                            <label>👑 Rol:</label>
                            <select name="rol" required>
                                <option value="usuario">Usuario</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">
                        ➕ Crear Usuario
                    </button>
                </form>
            </div>

            <!-- Lista de usuarios -->
            <div class="form-card">
                <h3>📋 Usuarios del Sistema (<?php echo count($usuarios); ?>)</h3>
                
                <?php if (!empty($usuarios)): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Información</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Último Acceso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $user): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                            <?php if ($user['rol'] === 'admin'): ?>
                                                <span title="Administrador">👑</span>
                                            <?php endif; ?>
                                            <?php if ($user['id'] == $usuario_actual['id']): ?>
                                                <span title="Tu cuenta" style="color: #007bff;">👤</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div><strong><?php echo htmlspecialchars($user['nombre']); ?></strong></div>
                                            <small style="color: #666;"><?php echo htmlspecialchars($user['email']); ?></small>
                                        </td>
                                        <td>
                                            <span class="<?php echo $user['rol'] === 'admin' ? 'role-admin' : 'role-user'; ?>">
                                                <?php echo $user['rol'] === 'admin' ? '👑 Admin' : '👤 Usuario'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $bloqueado = $user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time();
                                            
                                            if ($bloqueado): ?>
                                                <span class="status-badge status-blocked">🔒 Bloqueado</span>
                                                <br><small>Hasta: <?php echo date('d/m/Y H:i', strtotime($user['bloqueado_hasta'])); ?></small>
                                            <?php elseif ($user['activo']): ?>
                                                <span class="status-badge status-active">✅ Activo</span>
                                                <?php if ($user['intentos_fallidos'] > 0): ?>
                                                    <br><small style="color: #856404;">Intentos: <?php echo $user['intentos_fallidos']; ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="status-badge status-inactive">❌ Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($user['ultimo_acceso']) {
                                                $ultimo_acceso = strtotime($user['ultimo_acceso']);
                                                $diferencia = time() - $ultimo_acceso;
                                                
                                                echo date('d/m/Y H:i', $ultimo_acceso);
                                                
                                                if ($diferencia < 3600) {
                                                    echo '<br><small style="color: #28a745;">Hace ' . floor($diferencia/60) . ' min</small>';
                                                } elseif ($diferencia < 86400) {
                                                    echo '<br><small style="color: #ffc107;">Hace ' . floor($diferencia/3600) . ' horas</small>';
                                                } elseif ($diferencia < 604800) {
                                                    echo '<br><small style="color: #fd7e14;">Hace ' . floor($diferencia/86400) . ' días</small>';
                                                } else {
                                                    echo '<br><small style="color: #6c757d;">Hace mucho</small>';
                                                }
                                            } else {
                                                echo '<span style="color: #6c757d;">Nunca</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($user['id'] != $usuario_actual['id']): // Acciones para otros usuarios ?>
                                                
                                                <!-- Editar usuario (solo admin) -->
                                                <button class="btn btn-small btn-info" 
                                                        onclick="mostrarEditarUsuario(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['nombre']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo $user['rol']; ?>')"
                                                        title="Editar usuario">
                                                    ✏️
                                                </button>
                                                
                                                <!-- Toggle Activo/Inactivo -->
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="toggle_usuario">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" class="btn btn-small btn-warning" 
                                                            title="<?php echo $user['activo'] ? 'Desactivar' : 'Activar'; ?> usuario"
                                                            onclick="return confirm('¿<?php echo $user['activo'] ? 'Desactivar' : 'Activar'; ?> usuario?')">
                                                        <?php echo $user['activo'] ? '⏸️' : '▶️'; ?>
                                                    </button>
                                                </form>

                                                <!-- Resetear contraseña -->
                                                <button class="btn btn-small btn-info" 
                                                        onclick="mostrarResetPassword(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                                        title="Cambiar contraseña">
                                                    🔑
                                                </button>

                                                <?php if ($bloqueado): ?>
                                                    <!-- Desbloquear usuario -->
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="desbloquear_usuario">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn btn-small btn-danger" 
                                                                title="Desbloquear usuario"
                                                                onclick="return confirm('¿Desbloquear usuario?')">
                                                            🔓
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <!-- Eliminar usuario -->
                                                <button class="btn btn-small btn-danger" 
                                                        onclick="mostrarEliminarUsuario(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['nombre']); ?>', '<?php echo $user['rol']; ?>')"
                                                        title="Eliminar usuario"
                                                        style="margin-left: 5px;">
                                                    🗑️
                                                </button>

                                            <?php else: ?>
                                                <!-- Acciones para tu propia cuenta -->
                                                <button class="btn btn-small btn-info" 
                                                        onclick="mostrarEditarPerfil(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['nombre']); ?>', '<?php echo htmlspecialchars($user['email']); ?>')"
                                                        title="Editar tu perfil">
                                                    ✏️ Editar
                                                </button>
                                                
                                                <button class="btn btn-small btn-warning" 
                                                        onclick="mostrarResetPassword(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                                        title="Cambiar tu contraseña">
                                                    🔑 Cambiar
                                                </button>
                                                
                                                <small style="color: #28a745; font-style: italic;">Tu cuenta</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 40px;">
                        📭 No hay usuarios registrados
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para resetear contraseña -->
    <div id="resetPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h3>🔑 Cambiar Contraseña</h3>
            
            <form method="POST" id="resetPasswordForm">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" id="resetUserId">
                
                <div class="form-group">
                    <label>Usuario:</label>
                    <input type="text" id="resetUsername" readonly style="background: #f8f9fa;">
                </div>
                
                <div class="form-group">
                    <label>Nueva Contraseña:</label>
                    <input type="password" name="new_password" required minlength="6" 
                           placeholder="Mínimo 6 caracteres" id="newPassword">
                </div>
                
                <div class="form-group">
                    <label>Confirmar Contraseña:</label>
                    <input type="password" required minlength="6" 
                           placeholder="Repetir contraseña" id="confirmPassword">
                </div>
                
                <button type="submit" class="btn">🔄 Cambiar Contraseña</button>
                <button type="button" class="btn btn-danger" onclick="cerrarModal()">❌ Cancelar</button>
            </form>
        </div>
    </div>
    
    <!-- Modal para editar perfil propio -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalPerfil()">&times;</span>
            <h3>✏️ Editar Mi Perfil</h3>
            
            <form method="POST" id="editProfileForm">
                <input type="hidden" name="action" value="actualizar_perfil">
                <input type="hidden" name="user_id" id="editUserId">
                
                <div class="form-group">
                    <label>👤 Nombre de Usuario:</label>
                    <input type="text" name="username" id="editUsername" required maxlength="50" 
                           placeholder="nombre_usuario" pattern="[a-zA-Z0-9_]+" 
                           title="Solo letras, números y guiones bajos (3-50 caracteres)">
                    <small style="color: #666;">Puedes cambiar tu nombre de usuario</small>
                </div>
                
                <div class="form-group">
                    <label>📝 Nombre Completo:</label>
                    <input type="text" name="nombre" id="editNombre" required maxlength="100" 
                           placeholder="Tu nombre completo">
                </div>
                
                <div class="form-group">
                    <label>📧 Email:</label>
                    <input type="email" name="email" id="editEmail" required maxlength="100" 
                           placeholder="tu@email.com">
                </div>
                
                <div class="alert-info">
                    <strong>💡 Importante:</strong> Puedes cambiar tu nombre de usuario, pero debe ser único en el sistema. 
                    Para cambiar tu contraseña, usa el botón "🔑 Cambiar" correspondiente.
                </div>
                
                <button type="submit" class="btn">💾 Guardar Cambios</button>
                <button type="button" class="btn btn-danger" onclick="cerrarModalPerfil()">❌ Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para editar usuario (admin) -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalUsuario()">&times;</span>
            <h3>✏️ Editar Usuario</h3>
            
            <form method="POST" id="editUserForm">
                <input type="hidden" name="action" value="actualizar_usuario_admin">
                <input type="hidden" name="user_id" id="editUserIdAdmin">
                
                <div class="form-group">
                    <label>👤 Nombre de Usuario:</label>
                    <input type="text" name="username" id="editUsernameAdmin" required maxlength="50" 
                           placeholder="nombre_usuario" pattern="[a-zA-Z0-9_]+" 
                           title="Solo letras, números y guiones bajos (3-50 caracteres)">
                </div>
                
                <div class="form-group">
                    <label>📝 Nombre Completo:</label>
                    <input type="text" name="nombre" id="editNombreAdmin" required maxlength="100" 
                           placeholder="Nombre completo del usuario">
                </div>
                
                <div class="form-group">
                    <label>📧 Email:</label>
                    <input type="email" name="email" id="editEmailAdmin" required maxlength="100" 
                           placeholder="email@ejemplo.com">
                </div>
                
                <div class="form-group">
                    <label>👑 Rol:</label>
                    <select name="rol" id="editRolAdmin" required>
                        <option value="usuario">Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <div class="alert-info">
                    <strong>⚠️ Administrador:</strong> Estás editando los datos de otro usuario. 
                    Ten cuidado al cambiar el rol de administrador.
                </div>
                
                <button type="submit" class="btn">💾 Guardar Cambios</button>
                <button type="button" class="btn btn-danger" onclick="cerrarModalUsuario()">❌ Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para eliminar usuario -->
    <div id="deleteUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalEliminar()">&times;</span>
            <h3 style="color: #dc3545;">🗑️ Eliminar Usuario</h3>
            
            <div style="background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #dc3545;">
                <h4 style="color: #721c24; margin-bottom: 15px;">⚠️ ADVERTENCIA: Esta acción es IRREVERSIBLE</h4>
                <p style="color: #721c24; margin-bottom: 10px;">
                    Estás a punto de eliminar permanentemente al usuario:
                </p>
                <div style="background: white; padding: 15px; border-radius: 5px; margin: 10px 0;">
                    <strong id="deleteUserInfo"></strong>
                </div>
                <p style="color: #721c24; font-size: 14px;">
                    • Se eliminarán todos los datos del usuario<br>
                    • Esta acción NO se puede deshacer<br>
                    • Se recomienda desactivar en lugar de eliminar
                </p>
            </div>
            
            <form method="POST" id="deleteUserForm">
                <input type="hidden" name="action" value="eliminar_usuario">
                <input type="hidden" name="user_id" id="deleteUserId">
                
                <div class="form-group">
                    <label style="color: #dc3545; font-weight: bold;">
                        Para confirmar, escribe el nombre de usuario exacto:
                    </label>
                    <input type="text" name="confirm_username" id="confirmUsername" required 
                           placeholder="Escribe el nombre de usuario aquí" 
                           style="border: 2px solid #dc3545;">
                    <small style="color: #666;">Debe coincidir exactamente: <strong id="usernameToConfirm"></strong></small>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn btn-danger" style="margin-right: 10px;">
                        🗑️ SÍ, ELIMINAR PERMANENTEMENTE
                    </button>
                    <button type="button" class="btn" onclick="cerrarModalEliminar()">
                        ❌ Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Validación del formulario de crear usuario
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
            const username = this.querySelector('[name="username"]').value.trim();
            const password = this.querySelector('[name="password"]').value;
            const email = this.querySelector('[name="email"]').value.trim();
            
            if (username.length < 3) {
                e.preventDefault();
                alert('⚠️ El nombre de usuario debe tener al menos 3 caracteres');
                return;
            }
            
            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                e.preventDefault();
                alert('⚠️ El usuario solo puede contener letras, números y guiones bajos');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('⚠️ La contraseña debe tener al menos 6 caracteres');
                return;
            }
            
            if (!email.includes('@') || !email.includes('.')) {
                e.preventDefault();
                alert('⚠️ Ingresa un email válido');
                return;
            }
            
            if (!confirm(`¿Crear usuario "${username}"?`)) {
                e.preventDefault();
                return;
            }
        });

        // Funciones del modal de reseteo de contraseña
        function mostrarResetPassword(userId, username) {
            document.getElementById('resetUserId').value = userId;
            document.getElementById('resetUsername').value = username;
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
            document.getElementById('resetPasswordModal').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('resetPasswordModal').style.display = 'none';
        }

        // Funciones para editar perfil propio
        function mostrarEditarPerfil(userId, username, nombre, email) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUsername').value = username;
            document.getElementById('editNombre').value = nombre;
            document.getElementById('editEmail').value = email;
            document.getElementById('editProfileModal').style.display = 'block';
        }

        function cerrarModalPerfil() {
            document.getElementById('editProfileModal').style.display = 'none';
        }

        // Funciones para editar usuario (admin)
        function mostrarEditarUsuario(userId, username, nombre, email, rol) {
            document.getElementById('editUserIdAdmin').value = userId;
            document.getElementById('editUsernameAdmin').value = username;
            document.getElementById('editNombreAdmin').value = nombre;
            document.getElementById('editEmailAdmin').value = email;
            document.getElementById('editRolAdmin').value = rol;
            document.getElementById('editUserModal').style.display = 'block';
        }

        function cerrarModalUsuario() {
            document.getElementById('editUserModal').style.display = 'none';
        }

        // Funciones para eliminar usuario
        function mostrarEliminarUsuario(userId, username, nombre, rol) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('confirmUsername').value = '';
            document.getElementById('usernameToConfirm').textContent = username;
            document.getElementById('deleteUserInfo').innerHTML = `
                👤 <strong>${username}</strong> (${nombre})<br>
                👑 Rol: ${rol === 'admin' ? 'Administrador' : 'Usuario'}
            `;
            document.getElementById('deleteUserModal').style.display = 'block';
            
            // Enfocar el campo de confirmación
            setTimeout(() => {
                document.getElementById('confirmUsername').focus();
            }, 100);
        }

        function cerrarModalEliminar() {
            document.getElementById('deleteUserModal').style.display = 'none';
        }

        // Validación del formulario de reset password
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('⚠️ Las contraseñas no coinciden');
                return;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('⚠️ La contraseña debe tener al menos 6 caracteres');
                return;
            }
            
            if (!confirm('¿Cambiar la contraseña del usuario?')) {
                e.preventDefault();
                return;
            }
        });

        // Validación del formulario de editar perfil propio
        document.getElementById('editProfileForm').addEventListener('submit', function(e) {
            const username = document.getElementById('editUsername').value.trim();
            const nombre = document.getElementById('editNombre').value.trim();
            const email = document.getElementById('editEmail').value.trim();
            
            if (username.length < 3 || username.length > 50) {
                e.preventDefault();
                alert('⚠️ El nombre de usuario debe tener entre 3 y 50 caracteres');
                return;
            }
            
            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                e.preventDefault();
                alert('⚠️ El usuario solo puede contener letras, números y guiones bajos');
                return;
            }
            
            if (nombre.length < 2) {
                e.preventDefault();
                alert('⚠️ El nombre debe tener al menos 2 caracteres');
                return;
            }
            
            if (!email.includes('@') || !email.includes('.')) {
                e.preventDefault();
                alert('⚠️ Ingresa un email válido');
                return;
            }
            
            if (!confirm('¿Guardar los cambios en tu perfil?\n\nNota: Si cambias tu nombre de usuario, deberás usarlo para iniciar sesión.')) {
                e.preventDefault();
                return;
            }
        });

        // Validación del formulario de editar usuario (admin)
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            const username = document.getElementById('editUsernameAdmin').value.trim();
            const nombre = document.getElementById('editNombreAdmin').value.trim();
            const email = document.getElementById('editEmailAdmin').value.trim();
            const rol = document.getElementById('editRolAdmin').value;
            
            if (username.length < 3 || username.length > 50) {
                e.preventDefault();
                alert('⚠️ El nombre de usuario debe tener entre 3 y 50 caracteres');
                return;
            }
            
            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                e.preventDefault();
                alert('⚠️ El usuario solo puede contener letras, números y guiones bajos');
                return;
            }
            
            if (nombre.length < 2) {
                e.preventDefault();
                alert('⚠️ El nombre debe tener al menos 2 caracteres');
                return;
            }
            
            if (!email.includes('@') || !email.includes('.')) {
                e.preventDefault();
                alert('⚠️ Ingresa un email válido');
                return;
            }
            
            let confirmMessage = `¿Guardar los cambios del usuario "${username}"?`;
            if (rol === 'admin') {
                confirmMessage += '\n\n⚠️ ATENCIÓN: Le estás dando permisos de administrador.';
            }
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return;
            }
        });

        // Validación del formulario de eliminar usuario
        document.getElementById('deleteUserForm').addEventListener('submit', function(e) {
            const confirmUsername = document.getElementById('confirmUsername').value.trim();
            const expectedUsername = document.getElementById('usernameToConfirm').textContent;
            
            if (confirmUsername !== expectedUsername) {
                e.preventDefault();
                alert('⚠️ El nombre de usuario no coincide. Debe escribir exactamente: ' + expectedUsername);
                document.getElementById('confirmUsername').focus();
                return;
            }
            
            // Confirmación final extra
            if (!confirm(`⚠️ ÚLTIMA CONFIRMACIÓN\n\n¿Estás COMPLETAMENTE SEGURO de que deseas ELIMINAR PERMANENTEMENTE al usuario "${expectedUsername}"?\n\nEsta acción NO se puede deshacer.\n\nHaz clic en "Aceptar" solo si estás 100% seguro.`)) {
                e.preventDefault();
                return;
            }
            
            // Deshabilitar el botón para evitar doble envío
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = '🕐 Eliminando...';
        });

        // Validación en tiempo real para el campo de confirmación
        document.getElementById('confirmUsername').addEventListener('input', function() {
            const confirmValue = this.value.trim();
            const expectedValue = document.getElementById('usernameToConfirm').textContent;
            const submitBtn = document.querySelector('#deleteUserForm button[type="submit"]');
            
            if (confirmValue === expectedValue) {
                this.style.borderColor = '#28a745';
                this.style.backgroundColor = '#d4edda';
                submitBtn.disabled = false;
            } else {
                this.style.borderColor = '#dc3545';
                this.style.backgroundColor = '#f8d7da';
                submitBtn.disabled = true;
            }
        });

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const resetModal = document.getElementById('resetPasswordModal');
            const profileModal = document.getElementById('editProfileModal');
            const userModal = document.getElementById('editUserModal');
            const deleteModal = document.getElementById('deleteUserModal');
            
            if (event.target == resetModal) {
                cerrarModal();
            }
            if (event.target == profileModal) {
                cerrarModalPerfil();
            }
            if (event.target == userModal) {
                cerrarModalUsuario();
            }
            if (event.target == deleteModal) {
                cerrarModalEliminar();
            }
        }

        // Función para mostrar estadísticas
        function mostrarEstadisticas() {
            const totalUsuarios = <?php echo count($usuarios); ?>;
            const usuariosActivos = <?php echo count(array_filter($usuarios, function($u) { return $u['activo']; })); ?>;
            const administradores = <?php echo count(array_filter($usuarios, function($u) { return $u['rol'] === 'admin'; })); ?>;
            const bloqueados = <?php echo count(array_filter($usuarios, function($u) { return $u['bloqueado_hasta'] && strtotime($u['bloqueado_hasta']) > time(); })); ?>;
            
            const stats = `
📊 ESTADÍSTICAS DE USUARIOS

👥 Total de usuarios: ${totalUsuarios}
✅ Usuarios activos: ${usuariosActivos}
❌ Usuarios inactivos: ${totalUsuarios - usuariosActivos}
👑 Administradores: ${administradores}
👤 Usuarios regulares: ${totalUsuarios - administradores}
🔒 Usuarios bloqueados: ${bloqueados}

💡 Ratio activos: ${totalUsuarios > 0 ? Math.round((usuariosActivos/totalUsuarios)*100) : 0}%
            `;
            
            alert(stats);
        }

        // Auto-refresh cada 5 minutos
        setInterval(() => {
            fetch('auth.php?action=check')
                .then(response => response.json())
                .then(data => {
                    if (!data.authenticated || !data.is_admin) {
                        alert('⚠️ Sesión expirada o permisos insuficientes. Redirigiendo...');
                        window.location.href = 'login.php';
                    }
                })
                .catch(error => console.log('Check session failed:', error));
        }, 300000); // 5 minutos

        // Confirmaciones adicionales para acciones críticas
        document.querySelectorAll('form').forEach(form => {
            const action = form.querySelector('input[name="action"]');
            if (action && action.value === 'toggle_usuario') {
                form.addEventListener('submit', function(e) {
                    const button = this.querySelector('button');
                    button.disabled = true;
                    button.textContent = '⏳';
                    
                    setTimeout(() => {
                        button.disabled = false;
                        button.textContent = button.title.includes('Desactivar') ? '⏸️' : '▶️';
                    }, 3000);
                });
            }
        });

        // Generar contraseña aleatoria
        function generarPassword() {
            const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let password = '';
            for (let i = 0; i < 8; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return password;
        }

        // Agregar botón de generar contraseña en el modal
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('newPassword');
            const generateBtn = document.createElement('button');
            generateBtn.type = 'button';
            generateBtn.className = 'btn btn-small btn-info';
            generateBtn.innerHTML = '🎲 Generar';
            generateBtn.style.marginLeft = '10px';
            generateBtn.onclick = function() {
                const newPass = generarPassword();
                passwordField.value = newPass;
                document.getElementById('confirmPassword').value = newPass;
                alert(`Contraseña generada: ${newPass}\n\n¡Cópiala antes de continuar!`);
            };
            
            passwordField.parentNode.appendChild(generateBtn);
        });

        // Validación en tiempo real del username
        function validarUsername(input) {
            const username = input.value.trim();
            const feedback = input.nextElementSibling;
            
            if (username.length < 3) {
                input.style.borderColor = '#dc3545';
                if (feedback && feedback.tagName === 'SMALL') {
                    feedback.textContent = 'Mínimo 3 caracteres';
                    feedback.style.color = '#dc3545';
                }
            } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                input.style.borderColor = '#dc3545';
                if (feedback && feedback.tagName === 'SMALL') {
                    feedback.textContent = 'Solo letras, números y guiones bajos';
                    feedback.style.color = '#dc3545';
                }
            } else {
                input.style.borderColor = '#28a745';
                if (feedback && feedback.tagName === 'SMALL') {
                    feedback.textContent = 'Nombre de usuario válido';
                    feedback.style.color = '#28a745';
                }
            }
        }

        // Aplicar validación en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const usernameInputs = document.querySelectorAll('input[name="username"]');
            usernameInputs.forEach(input => {
                input.addEventListener('input', function() {
                    validarUsername(this);
                });
            });
        });
    </script>
</body>
</html>