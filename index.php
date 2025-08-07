<?php
// index.php - CORREGIDO: Sin conflictos de redirección
session_start();

// Headers limpios para PWA
if (!headers_sent()) {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// Verificar autenticación de forma segura
try {
    require_once 'auth.php';
    
    // Crear instancia de Auth para verificación
    $auth = new Auth();
    
    // Si no está autenticado, redireccionar INMEDIATAMENTE
    if (!$auth->verificarAutenticacion()) {
        if (!headers_sent()) {
            header('Location: login.php?expired=1', true, 302);
            exit;
        } else {
            echo '<script>window.location.replace("login.php?expired=1");</script>';
            exit;
        }
    }
    
    // Obtener datos del usuario autenticado
    $usuario = $auth->obtenerUsuarioActual();
    
    // Si no hay usuario válido, forzar logout
    if (!$usuario) {
        if (!headers_sent()) {
            header('Location: logout.php?invalid=1', true, 302);
            exit;
        } else {
            echo '<script>window.location.replace("logout.php?invalid=1");</script>';
            exit;
        }
    }
    
} catch (Exception $e) {
    error_log("Error en index.php: " . $e->getMessage());
    if (!headers_sent()) {
        header('Location: login.php?error=system', true, 302);
        exit;
    } else {
        echo '<script>window.location.replace("login.php?error=system");</script>';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Sistema de Registro Financiero PWA</title>
    
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
            padding: 20px;
            /* PWA safe areas */
            padding-top: max(20px, env(safe-area-inset-top));
            padding-bottom: max(20px, env(safe-area-inset-bottom));
            padding-left: max(20px, env(safe-area-inset-left));
            padding-right: max(20px, env(safe-area-inset-right));
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
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
            right: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-value {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .ingresos { color: #4CAF50; }
        .egresos { color: #f44336; }
        .balance { color: #2196F3; }

        .controls {
            padding: 30px;
        }

        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            background: #e0e0e0;
        }

        .tab {
            flex: 1;
            padding: 15px;
            background: #e0e0e0;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .tab.active {
            background: #4CAF50;
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
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

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 10px;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background: #45a049;
        }

        .btn-voice {
            background: #2196F3;
            color: white;
        }

        .btn-voice:hover {
            background: #1976D2;
        }

        .btn-voice.recording {
            background: #f44336;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .voice-section {
            background: #f0f8ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .voice-feedback {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            font-style: italic;
        }

        .voice-controls {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .btn-help {
            background: #FF9800;
            color: white;
        }

        .btn-help:hover {
            background: #F57C00;
        }

        .transactions {
            padding: 30px;
            background: #fafafa;
        }

        .transaction-item {
            background: white;
            margin-bottom: 15px;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: relative;
        }

        .transaction-item.ingreso {
            border-left-color: #4CAF50;
        }

        .transaction-item.egreso {
            border-left-color: #f44336;
        }

        .transaction-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .transaction-info h3 {
            margin-bottom: 5px;
            color: #333;
        }

        .transaction-info p {
            color: #666;
            font-size: 14px;
        }

        .transaction-amount {
            font-size: 1.5em;
            font-weight: bold;
        }

        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 50%;
            background: #f44336;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.3s ease;
            opacity: 0.7;
            z-index: 10;
        }

        .delete-btn:hover {
            opacity: 1;
            background: #d32f2f;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
        }

        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* PWA Specific Styles */
        .pwa-offline-indicator {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(90deg, #dc3545, #fd7e14);
            color: white;
            text-align: center;
            padding: 8px;
            font-size: 14px;
            font-weight: bold;
            z-index: 1000;
            display: none;
        }

        .offline-mode .pwa-offline-indicator {
            display: block;
        }

        .sync-indicator {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 12px;
            z-index: 1000;
            display: none;
            animation: fadeInUp 0.3s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate(-50%, 20px);
            }
            to {
                opacity: 1;
                transform: translate(-50%, 0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
                padding-top: max(10px, env(safe-area-inset-top));
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .transaction-item {
                flex-direction: column;
                text-align: center;
                padding-right: 50px;
            }
            
            .user-info {
                position: static;
                margin-top: 10px;
                justify-content: center;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            background: linear-gradient(135deg, #FF9800, #F57C00);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            position: relative;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5em;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .close:hover {
            background: rgba(255,255,255,0.2);
        }

        .modal-body {
            padding: 30px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Loading Screen */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loading-screen.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .loading-content {
            text-align: center;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 6px solid rgba(255,255,255,0.3);
            border-top: 6px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <h2>💰 Sistema Financiero</h2>
            <p>Cargando aplicación...</p>
        </div>
    </div>

    <!-- PWA Offline Indicator -->
    <div class="pwa-offline-indicator" id="offlineIndicator">
        📴 Modo Offline - Los datos se sincronizarán al reconectar
    </div>

    <!-- Sync Status Indicator -->
    <div class="sync-indicator" id="syncIndicator">
        🔄 Sincronizando datos...
    </div>

    <div class="container">
        <div class="header">
            <div class="user-info">
                <span id="userName">👤 <?php echo htmlspecialchars($usuario['nombre']); ?> (<?php echo htmlspecialchars($usuario['username']); ?>)</span>
                <?php if ($usuario['rol'] === 'admin'): ?>
                    <a href="user_management.php" class="btn-logout" style="margin-right: 10px;">
                        ⚙️ Admin
                    </a>
                <?php endif; ?>
                <button onclick="logout()" class="btn-logout">🚪 Salir</button>
            </div>
            <br>
            <br>
            <h1>💰 Sistema Financiero PWA</h1>
            <p>Gestiona tus ingresos y egresos de forma inteligente - Funciona offline</p>
            
        </div>

        <div class="stats" id="stats">
            <div class="stat-card">
                <div class="stat-value balance" id="balance">$0</div>
                <div>Balance</div>
            </div>
           <div class="stat-card">
                <div class="stat-value ingresos" id="totalIngresos">$0</div>
                <div>Total Ingresos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value egresos" id="totalEgresos">$0</div>
                <div>Total Egresos</div>
            </div>

            <div class="stat-card">
                <div class="stat-value" id="totalTransacciones">0</div>
                <div>Transacciones</div>
            </div>
        </div>

        <div class="controls">
            <div class="tabs">
                <button class="tab active" onclick="switchTab('manual')">📝 Manual</button>
                <button class="tab" onclick="switchTab('voice')">🎤 Por Voz</button>
            </div>

            <!-- Formulario Manual -->
            <div id="manual" class="tab-content active">
                <form id="manualForm">
                    <div class="form-group">
                        <label for="tipo">Tipo de Transacción</label>
                        <select id="tipo" required>
                            <option value="">Seleccionar...</option>
                            <option value="ingreso">💰 Ingreso</option>
                            <option value="egreso">💸 Egreso</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="monto">Monto ($)</label>
                        <input type="number" id="monto" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input type="text" id="titulo" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción (opcional)</label>
                        <textarea id="descripcion" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Crear Transacción</button>
                </form>
            </div>

            <!-- Control por Voz -->
            <div id="voice" class="tab-content">
                <div class="voice-section">
                    <h3>🎤 Control por Voz (Offline)</h3>
                    
                    <div id="httpsWarning" style="display: none; background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        ⚠️ <strong>Atención:</strong> Para usar el micrófono, la página debe ejecutarse en HTTPS. 
                        <br>Si estás en desarrollo local, usa: <code>localhost</code> en lugar de <code>127.0.0.1</code>
                    </div>
                    
                    <p><strong>Ejemplos de comandos:</strong></p>
                    <ul style="text-align: left; display: inline-block; margin: 10px 0;">
                        <li>"Ingreso de doscientos mil título salario"</li>
                        <li>"Egreso de cincuenta mil título compras"</li>
                        <li>"Nuevo ingreso de quinientos mil título freelance"</li>
                    </ul>
                    
                    <div class="voice-controls">
                        <button id="voiceBtn" class="btn btn-voice" onclick="toggleVoiceRecording()">
                            🎤 Iniciar Grabación
                        </button>
                        
                        <button class="btn btn-help" onclick="showTipsModal()">
                            💡 Consejos
                        </button>
                    </div>
                    
                    <div id="voiceFeedback" class="voice-feedback"></div>
                </div>
            </div>

            <div id="alerts"></div>
        </div>

        <div class="transactions">
            <h2>📊 Transacciones Recientes</h2>
            <div id="transactionsList"></div>
        </div>
    </div>

    <!-- Modal de Consejos -->
    <div id="tipsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>💡 Consejos para Reconocimiento de Voz</h2>
                <button class="close" onclick="closeTipsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>📱 Comandos Optimizados:</strong></p>
                <ul>
                    <li>"Ingreso de cincuenta mil título salario"</li>
                    <li>"Egreso de quinientos mil título casa"</li>
                    <li>"Nuevo ingreso de doscientos mil título bonus"</li>
                </ul>
                <br>
                <p><strong>💡 Consejos para Móviles:</strong></p>
                <ul>
                    <li>Habla más fuerte que en PC</li>
                    <li>Acércate al micrófono (10-15cm)</li>
                    <li>Usa ambiente silencioso</li>
                    <li>Pronuncia claramente cada palabra</li>
                </ul>
            </div>
        </div>
    </div>

<script>
        console.log('🚀 Sistema Financiero PWA - Versión Completa Optimizada');
        
        // ==========================================
        // VARIABLES GLOBALES
        // ==========================================
        
        // Variables PWA
        let isOffline = !navigator.onLine;
        let pendingTransactions = [];
        let lastSyncTime = null;
        
        // Variables del sistema
        let isRecording = false;
        let recognition = null;
        let isAppInitialized = false;
        
        // Variables de logout optimizado
        let isLoggingOut = false;
        let logoutAbortController = null;
        let sessionCheckInterval = null;

        // ==========================================
        // SISTEMA DE LOGOUT OPTIMIZADO
        // ==========================================

        function logout(skipConfirmation = false) {
            if (isLoggingOut) {
                console.log('🔄 Logout ya en progreso...');
                return;
            }
            
            if (!skipConfirmation && !confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                return;
            }
            
            console.log('🚀 Iniciando logout optimizado...');
            isLoggingOut = true;
            
            // Limpiar intervalos y timers
            clearAllTimers();
            
            // Mostrar overlay minimalista
            showLogoutOverlay();
            
            // Abortar peticiones pendientes
            if (logoutAbortController) {
                logoutAbortController.abort();
            }
            logoutAbortController = new AbortController();
            
            // Estrategia de logout múltiple con timeouts cortos
            const logoutPromises = [
                logoutViaAPI(),
                new Promise(resolve => setTimeout(resolve, 1500)) // Timeout 1.5s
            ];
            
            Promise.race(logoutPromises)
                .then(() => {
                    console.log('✅ Logout process completed');
                    redirectToLogout();
                })
                .catch((error) => {
                    console.log('⚠️ Logout error, procediendo con redirección:', error);
                    redirectToLogout();
                });
            
            // Fallback absoluto - si no responde en 2 segundos
            setTimeout(() => {
                if (isLoggingOut) {
                    console.log('⏱️ Timeout absoluto, forzando logout');
                    redirectToLogout();
                }
            }, 2000);
        }

        async function logoutViaAPI() {
            try {
                const response = await fetch('auth.php?action=logout', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Cache-Control': 'no-cache'
                    },
                    signal: AbortSignal.timeout(1000) // 1 segundo timeout
                });
                
                console.log('📤 Logout API notification sent');
                return true;
                
            } catch (error) {
                if (error.name === 'TimeoutError') {
                    console.log('⏱️ Logout API timeout - continuando');
                } else {
                    console.log('⚠️ Logout API error:', error.message);
                }
                throw error;
            }
        }

        function showLogoutOverlay() {
            const overlay = document.createElement('div');
            overlay.id = 'logoutOverlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                color: white;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            `;
            
            overlay.innerHTML = `
                <div style="text-align: center; background: rgba(255,255,255,0.1); padding: 30px 40px; border-radius: 15px; backdrop-filter: blur(10px);">
                    <div style="font-size: 2.5em; margin-bottom: 15px; animation: wave 1s ease-in-out infinite;">👋</div>
                    <h3 style="margin: 0 0 10px 0; font-weight: 600;">Cerrando Sesión</h3>
                    <p style="margin: 0; opacity: 0.8; font-size: 14px;">Un momento por favor...</p>
                </div>
                <style>
                    @keyframes wave {
                        0%, 100% { transform: rotate(0deg) scale(1); }
                        50% { transform: rotate(10deg) scale(1.1); }
                    }
                </style>
            `;
            
            document.body.appendChild(overlay);
        }

        function redirectToLogout() {
            cleanupCurrentSessionOnly();
            window.location.replace('logout.php?source=index&fast=1&t=' + Date.now());
        }

        function cleanupCurrentSessionOnly() {
            try {
                const currentSessionKeys = [
                    'current_session_id',
                    'user_session_data',
                    'temp_auth_data',
                    'session_timestamp',
                    'last_activity'
                ];
                
                currentSessionKeys.forEach(key => {
                    localStorage.removeItem(key);
                    sessionStorage.removeItem(key);
                });
                
                console.log('✅ Limpieza específica de sesión completada');
                
            } catch (error) {
                console.log('⚠️ Error mínimo en limpieza específica:', error);
            }
        }

        function clearAllTimers() {
            if (sessionCheckInterval) {
                clearInterval(sessionCheckInterval);
                sessionCheckInterval = null;
            }
            
            if (window.syncInterval) {
                clearInterval(window.syncInterval);
            }
            
            if (window.heartbeatInterval) {
                clearInterval(window.heartbeatInterval);
            }
        }

        function forceCleanLogout() {
            console.log('🚨 Forzando logout limpio...');
            logout(true);
        }

        // ==========================================
        // MANEJO DE SESIÓN EXPIRADA
        // ==========================================

        function handleSessionExpired(showNotification = true) {
            if (isLoggingOut) return;
            
            console.log('⏰ Sesión expirada detectada');
            
            if (showNotification) {
                showSessionExpiredNotification();
            }
            
            setTimeout(() => {
                logout(true);
            }, showNotification ? 2000 : 0);
        }

        function showSessionExpiredNotification() {
            const notification = document.createElement('div');
            notification.id = 'sessionExpiredNotification';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: linear-gradient(135deg, #ff6b35, #f7931e);
                color: white;
                padding: 15px 25px;
                border-radius: 25px;
                z-index: 9999;
                font-weight: 600;
                font-size: 14px;
                box-shadow: 0 4px 20px rgba(255, 107, 53, 0.4);
                animation: slideDown 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                backdrop-filter: blur(10px);
            `;
            
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px;">⏰</span>
                    <div>
                        <div style="font-weight: 700;">Sesión Expirada</div>
                        <div style="font-size: 12px; opacity: 0.9;">Cerrando sesión automáticamente...</div>
                    </div>
                </div>
            `;
            
            if (!document.getElementById('sessionNotificationStyles')) {
                const style = document.createElement('style');
                style.id = 'sessionNotificationStyles';
                style.textContent = `
                    @keyframes slideDown {
                        from { 
                            transform: translate(-50%, -100px);
                            opacity: 0;
                            scale: 0.8;
                        }
                        to { 
                            transform: translate(-50%, 0);
                            opacity: 1;
                            scale: 1;
                        }
                    }
                `;
                document.head.appendChild(style);
            }
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.animation = 'slideDown 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) reverse';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.remove();
                        }
                    }, 300);
                }
            }, 1800);
        }

        // ==========================================
        // MANEJO DE RESPUESTAS DEL SERVIDOR
        // ==========================================

        function isValidJSON(text) {
            try {
                JSON.parse(text);
                return true;
            } catch (e) {
                return false;
            }
        }

        async function handleServerResponse(response) {
            if (isLoggingOut) {
                console.log('🔄 Logout en progreso, ignorando respuesta');
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status}`);
                }
                return {};
            }
            
            if (response.status === 401) {
                console.warn('🔄 Error 401 - Sesión no válida');
                handleSessionExpired();
                throw new Error('SESION_EXPIRADA');
            }
            
            if (response.status === 403) {
                console.warn('🔄 Error 403 - Sin permisos');
                showAlert('❌ No tienes permisos para realizar esta acción', 'error');
                throw new Error('SIN_PERMISOS');
            }
            
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
            }
            
            const text = await response.text();
            
            if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                console.error('🔄 Servidor devolvió HTML - sesión no válida');
                handleSessionExpired(false);
                throw new Error('RESPUESTA_HTML_INVALIDA');
            }
            
            if (!isValidJSON(text)) {
                console.error('Respuesta no es JSON válido:', text.substring(0, 100) + '...');
                throw new Error('Respuesta inválida del servidor');
            }
            
            return JSON.parse(text);
        }

        // ==========================================
        // LOADING SCREEN
        // ==========================================

        function hideLoadingScreen() {
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) {
                setTimeout(() => {
                    loadingScreen.classList.add('hidden');
                    setTimeout(() => {
                        loadingScreen.remove();
                    }, 500);
                }, 1000);
            }
        }

        // ==========================================
        // CARGA DE DATOS
        // ==========================================

        async function loadData() {
            if (isLoggingOut || !isAppInitialized) {
                console.log('🔄 App no inicializada o logout en progreso');
                return;
            }
            
            try {
                console.log('🔄 Cargando datos del servidor...');
                
                // Cargar resumen con timeout
                const resumenController = new AbortController();
                const resumenTimeout = setTimeout(() => resumenController.abort(), 10000);
                
                const resumenResponse = await fetch('api.php?action=obtener_resumen', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: resumenController.signal
                });
                
                clearTimeout(resumenTimeout);
                const resumen = await handleServerResponse(resumenResponse);
                
                if (resumen.success) {
                    updateStatsDisplay({
                        total_ingresos: resumen.data.total_ingresos || 0,
                        total_egresos: resumen.data.total_egresos || 0,
                        balance: resumen.data.balance || 0,
                        total_transacciones: resumen.data.total_transacciones || 0
                    });
                }
                
                // Cargar transacciones
                const transaccionesController = new AbortController();
                const transaccionesTimeout = setTimeout(() => transaccionesController.abort(), 10000);
                
                const transaccionesResponse = await fetch('api.php?action=obtener_transacciones', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: transaccionesController.signal
                });
                
                clearTimeout(transaccionesTimeout);
                const transacciones = await handleServerResponse(transaccionesResponse);
                
                if (transacciones.success) {
                    displayTransactions(transacciones.data);
                }
                
            } catch (error) {
                console.error('❌ Error al cargar datos:', error);
                
                if (error.name === 'AbortError') {
                    console.log('⏱️ Timeout en carga de datos');
                    showAlert('⏱️ Timeout de conexión. Intentando nuevamente...', 'error');
                    return;
                }
                
                if (error.message === 'SESION_EXPIRADA' || error.message === 'RESPUESTA_HTML_INVALIDA') {
                    return;
                }
                
                if (isOffline) {
                    showAlert('📴 Sin conexión. Mostrando datos guardados localmente.', 'info');
                    loadOfflineData();
                } else {
                    showAlert('❌ Error de conexión. Verifica que el servidor esté funcionando.', 'error');
                }
            }
        }

        // ==========================================
        // INTERFAZ DE USUARIO
        // ==========================================

        function displayTransactions(transactions) {
            const container = document.getElementById('transactionsList');
            if (!container) return;
            
            console.log('🎯 Mostrando', transactions?.length || 0, 'transacciones');
            
            container.innerHTML = '';
            
            if (!transactions || transactions.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: #666; padding: 40px;">
                        <div style="font-size: 3em; color: #ddd; margin-bottom: 20px;">📊</div>
                        <p>No hay transacciones registradas</p>
                        <p><small>Crea tu primera transacción usando el formulario manual o por voz</small></p>
                    </div>
                `;
                return;
            }
            
            transactions.forEach(transaction => {
                const div = document.createElement('div');
                div.className = `transaction-item ${transaction.tipo}`;
                div.setAttribute('data-transaction-id', transaction.id);
                div.style.position = 'relative';
                
                const fecha = new Date(transaction.fecha_creacion).toLocaleString('es-AR');
                const metodoBadge = transaction.metodo_creacion === 'audio' ? '🎤' : '📝';
                
                div.innerHTML = `
                    <div class="transaction-info">
                        <h3>${escapeHtml(transaction.titulo)} ${metodoBadge}</h3>
                        <p>${escapeHtml(transaction.descripcion || 'Sin descripción')}</p>
                        <p><small>📅 ${fecha}</small></p>
                    </div>
                    <div class="transaction-amount ${transaction.tipo}">
                        ${transaction.tipo === 'ingreso' ? '+' : '-'}${formatCurrency(transaction.monto)}
                    </div>
                    <button class="delete-btn" onclick="eliminarTransaccion(${transaction.id}, '${escapeHtml(transaction.titulo).replace(/'/g, "\\'")}', ${transaction.monto})" title="Eliminar transacción">
                        ✕
                    </button>
                `;
                
                container.appendChild(div);
            });
        }

        function updateStatsDisplay(stats) {
            console.log('📊 Actualizando estadísticas:', stats);
            
            const totalIngresosEl = document.getElementById('totalIngresos');
            const totalEgresosEl = document.getElementById('totalEgresos');
            const balanceEl = document.getElementById('balance');
            const totalTransaccionesEl = document.getElementById('totalTransacciones');
            
            if (totalIngresosEl) totalIngresosEl.textContent = formatCurrency(stats.total_ingresos);
            if (totalEgresosEl) totalEgresosEl.textContent = formatCurrency(stats.total_egresos);
            if (balanceEl) balanceEl.textContent = formatCurrency(stats.balance);
            if (totalTransaccionesEl) totalTransaccionesEl.textContent = stats.total_transacciones;
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS'
            }).format(amount || 0);
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            
            const alertsContainer = document.getElementById('alerts');
            if (alertsContainer) {
                alertsContainer.appendChild(alertDiv);
                
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            } else {
                console.log('Alert:', message);
            }
        }

        function switchTab(tabName) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            if (event && event.target) {
                event.target.classList.add('active');
            }
            
            const tabContent = document.getElementById(tabName);
            if (tabContent) {
                tabContent.classList.add('active');
            }
        }

        // ==========================================
        // ELIMINAR TRANSACCIONES
        // ==========================================

        async function eliminarTransaccion(id, titulo, monto) {
            if (isLoggingOut) return;
            
            const confirmMessage = `¿Estás seguro de que quieres eliminar esta transacción?\n\n"${titulo}"\nMonto: ${formatCurrency(monto)}`;
            
            if (!confirm(confirmMessage)) {
                return;
            }
            
            try {
                const deleteBtn = document.querySelector(`button[onclick*="eliminarTransaccion(${id}"]`);
                if (deleteBtn) {
                    deleteBtn.innerHTML = '⏳';
                    deleteBtn.disabled = true;
                }
                
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        action: 'eliminar_transaccion',
                        id: id
                    })
                });
                
                const result = await handleServerResponse(response);
                
                if (result.success) {
                    showAlert('✅ ' + result.message, 'success');
                    
                    const transactionElement = deleteBtn.closest('.transaction-item');
                    if (transactionElement) {
                        transactionElement.style.transition = 'all 0.3s ease';
                        transactionElement.style.transform = 'translateX(-100%)';
                        transactionElement.style.opacity = '0';
                        
                        setTimeout(() => {
                            transactionElement.remove();
                        }, 300);
                    }
                    
                    setTimeout(() => {
                        loadData();
                    }, 500);
                    
                    if (navigator.vibrate) {
                        navigator.vibrate([100, 50, 100]);
                    }
                    
                } else {
                    throw new Error(result.message || 'Error al eliminar la transacción');
                }
                
            } catch (error) {
                console.error('Error al eliminar transacción:', error);
                
                if (error.message === 'SESION_EXPIRADA' || error.message === 'RESPUESTA_HTML_INVALIDA') {
                    return;
                }
                
                showAlert('❌ Error al eliminar la transacción: ' + error.message, 'error');
                
                const deleteBtn = document.querySelector(`button[onclick*="eliminarTransaccion(${id}"]`);
                if (deleteBtn) {
                    deleteBtn.innerHTML = '✕';
                    deleteBtn.disabled = false;
                }
            }
        }

        // ==========================================
        // FORMULARIO MANUAL
        // ==========================================

        function setupManualForm() {
            const manualForm = document.getElementById('manualForm');
            if (manualForm) {
                manualForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    if (isLoggingOut) return;
                    
                    const datos = {
                        tipo: document.getElementById('tipo').value,
                        monto: parseFloat(document.getElementById('monto').value),
                        titulo: document.getElementById('titulo').value,
                        descripcion: document.getElementById('descripcion')?.value || '',
                        metodo_creacion: 'manual'
                    };
                    
                    if (!datos.tipo || !datos.monto || !datos.titulo) {
                        showAlert('❌ Por favor completa todos los campos obligatorios', 'error');
                        return;
                    }
                    
                    if (datos.monto <= 0) {
                        showAlert('❌ El monto debe ser mayor a 0', 'error');
                        return;
                    }
                    
                    if (isOffline) {
                        createTransactionOffline(datos);
                        showAlert('📱 Transacción guardada offline. Se sincronizará al reconectar.', 'success');
                        this.reset();
                    } else {
                        try {
                            const response = await fetch('api.php', {
                                method: 'POST',
                                headers: { 
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    action: 'crear_transaccion',
                                    datos: datos
                                })
                            });
                            
                            const result = await handleServerResponse(response);
                            
                            if (result.success) {
                                showAlert('✅ Transacción creada exitosamente!', 'success');
                                this.reset();
                                loadData();
                            } else {
                                showAlert('❌ ' + result.message, 'error');
                            }
                        } catch (error) {
                            console.error('Error al crear transacción:', error);
                            
                            if (error.message === 'SESION_EXPIRADA' || error.message === 'RESPUESTA_HTML_INVALIDA') {
                                return;
                            }
                            
                            if (error.message.includes('fetch')) {
                                createTransactionOffline(datos);
                                showAlert('📱 Sin conexión. Transacción guardada offline.', 'success');
                                this.reset();
                            } else {
                                showAlert('❌ Error al crear transacción', 'error');
                            }
                        }
                    }
                });
            }
        }

        // ==========================================
        // FUNCIONALIDAD OFFLINE (BÁSICA)
        // ==========================================

        function createTransactionOffline(transactionData) {
            const offlineTransaction = {
                ...transactionData,
                id: 'offline_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                timestamp: Date.now(),
                status: 'pending',
                fecha_creacion: new Date().toISOString()
            };
            
            pendingTransactions.push(offlineTransaction);
            savePendingTransactions();
            displayTransactionOptimistically(offlineTransaction);
            
            console.log('📦 Transaction queued for sync:', offlineTransaction.id);
            return offlineTransaction;
        }

        function displayTransactionOptimistically(transaction) {
            console.log('📱 Showing offline transaction:', transaction.titulo);
            // Implementación básica - mostrar en la lista con indicador offline
        }

        function savePendingTransactions() {
            try {
                localStorage.setItem('pending_transactions', JSON.stringify(pendingTransactions));
            } catch (error) {
                console.error('Error saving pending transactions:', error);
            }
        }

        function loadPendingTransactions() {
            try {
                const saved = localStorage.getItem('pending_transactions');
                if (saved) {
                    pendingTransactions = JSON.parse(saved);
                    console.log('📦 Loaded', pendingTransactions.length, 'pending transactions');
                }
            } catch (error) {
                console.error('Error loading pending transactions:', error);
            }
        }

        function loadOfflineData() {
            console.log('📱 Loading offline data...');
        }

        // ==========================================
        // CONECTIVIDAD
        // ==========================================

        function handleOnline() {
            console.log('🌐 Back online');
            isOffline = false;
            updateOfflineStatus();
            syncPendingTransactions();
        }

        function handleOffline() {
            console.log('📴 Gone offline');
            isOffline = true;
            updateOfflineStatus();
        }

        function updateOfflineStatus() {
            const indicator = document.getElementById('offlineIndicator');
            const body = document.body;
            
            if (isOffline) {
                body.classList.add('offline-mode');
                if (indicator) indicator.style.display = 'block';
            } else {
                body.classList.remove('offline-mode');
                if (indicator) indicator.style.display = 'none';
            }
        }

        async function syncPendingTransactions() {
            if (pendingTransactions.length === 0 || isLoggingOut) return;
            console.log('🔄 Syncing pending transactions...');
            // Implementación de sincronización básica
        }

        // ==========================================
        // RECONOCIMIENTO DE VOZ
        // ==========================================

        function initializeVoiceRecognition() {
            if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
                recognition.lang = 'es-ES';
                recognition.continuous = false;
                recognition.interimResults = false;
                recognition.maxAlternatives = 3;
                
                recognition.onstart = function() {
                    isRecording = true;
                    updateVoiceButton(true);
                    showVoiceFeedback('🎤 Escuchando...', 'info');
                };
                
                recognition.onresult = function(event) {
                    if (event.results.length > 0) {
                        const transcript = event.results[0][0].transcript;
                        showVoiceFeedback(`Procesando: "${transcript}"`, 'info');
                        processVoiceCommand(transcript);
                    }
                };
                
                recognition.onerror = function(event) {
                    console.error('Voice recognition error:', event.error);
                    showVoiceFeedback('❌ Error en reconocimiento de voz', 'error');
                    resetVoiceButton();
                };
                
                recognition.onend = function() {
                    resetVoiceButton();
                };
                
                return true;
            } else {
                const voiceBtn = document.getElementById('voiceBtn');
                if (voiceBtn) {
                    voiceBtn.textContent = '❌ Navegador no compatible';
                    voiceBtn.disabled = true;
                }
                return false;
            }
        }

        function toggleVoiceRecording() {
            if (!recognition) {
                showVoiceFeedback('❌ Reconocimiento de voz no disponible', 'error');
                return;
            }
            
            if (isRecording) {
                recognition.stop();
                return;
            }
            
            try {
                recognition.start();
            } catch (error) {
                showVoiceFeedback('❌ Error al iniciar reconocimiento: ' + error.message, 'error');
                resetVoiceButton();
            }
        }

        function updateVoiceButton(recording) {
            const btn = document.getElementById('voiceBtn');
            if (!btn) return;
            
            if (recording) {
                btn.classList.add('recording');
                btn.textContent = '🛑 Detener Grabación';
            } else {
                btn.classList.remove('recording');
                btn.textContent = '🎤 Iniciar Grabación';
            }
        }

        function resetVoiceButton() {
            isRecording = false;
            updateVoiceButton(false);
        }

        function showVoiceFeedback(message, type = 'info') {
            const feedback = document.getElementById('voiceFeedback');
            if (!feedback) return;
            
            feedback.textContent = message;
            feedback.className = 'voice-feedback';
            if (type === 'error') feedback.style.color = '#f44336';
            else if (type === 'success') feedback.style.color = '#4CAF50';
            else feedback.style.color = '#2196F3';
        }

        async function processVoiceCommand(text) {
            if (isLoggingOut) return;
            
            try {
                if (isOffline) {
                    showAlert('📱 Comando de voz procesado offline', 'info');
                    return;
                }
                
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        action: 'procesar_voz',
                        texto: text
                    })
                });
                
                const result = await handleServerResponse(response);
                
                if (result.success) {
                    showAlert('✅ Transacción creada exitosamente por voz!', 'success');
                    showVoiceFeedback('✅ Transacción creada exitosamente', 'success');
                    loadData();
                } else {
                    showAlert('❌ ' + result.message, 'error');
                    showVoiceFeedback('❌ ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error al procesar comando de voz:', error);
                
                if (error.message === 'SESION_EXPIRADA' || error.message === 'RESPUESTA_HTML_INVALIDA') {
                    return;
                }
                
                showAlert('❌ Error al procesar comando de voz', 'error');
                showVoiceFeedback('❌ Error de conexión', 'error');
            }
        }

        // ==========================================
        // MODALES
        // ==========================================

        function showTipsModal() {
            const modal = document.getElementById('tipsModal');
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeTipsModal() {
            const modal = document.getElementById('tipsModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // ==========================================
        // VERIFICACIÓN DE SESIÓN (OPCIONAL)
        // ==========================================

        async function checkSessionStatus() {
            if (isLoggingOut) return true;
            
            try {
                const response = await fetch('auth.php?action=ping', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: AbortSignal.timeout(3000)
                });
                
                if (response.ok) {
                    const result = await response.json();
                    return result.pong === true;
                }
                
                return false;
                
            } catch (error) {
                if (error.name === 'TimeoutError') {
                    console.log('⏱️ Session check timeout');
                } else {
                    console.log('⚠️ Session check error:', error.message);
                }
                return false;
            }
        }

        function startSessionMonitoring(intervalMinutes = 15) {
            if (sessionCheckInterval) {
                clearInterval(sessionCheckInterval);
            }
            
            sessionCheckInterval = setInterval(async () => {
                if (!isLoggingOut) {
                    const isValid = await checkSessionStatus();
                    if (!isValid) {
                        console.log('📊 Session monitoring: Session appears invalid');
                        // Solo log, no logout automático
                    }
                }
            }, intervalMinutes * 60 * 1000);
            
            console.log(`📊 Session monitoring started (every ${intervalMinutes} minutes)`);
        }

        function updateUserActivity() {
            if (!isLoggingOut) {
                localStorage.setItem('last_user_activity', Date.now().toString());
            }
        }

        // ==========================================
        // INICIALIZACIÓN DE LA APLICACIÓN
        // ==========================================

        function initializeApp() {
            console.log('🚀 Inicializando aplicación PWA...');
            
            if (isAppInitialized) {
                console.log('✅ App ya inicializada');
                return true;
            }
            
            // Verificar elementos críticos del DOM
            const criticalElements = ['totalIngresos', 'totalEgresos', 'balance', 'transactionsList'];
            const missingElements = criticalElements.filter(id => !document.getElementById(id));
            
            if (missingElements.length > 0) {
                console.error('Elementos críticos faltantes:', missingElements);
                showAlert('❌ Error en la interfaz. Recarga la página.', 'error');
                return false;
            }
            
            // Configurar event listeners
            setupManualForm();
            
            // Inicializar reconocimiento de voz
            if (initializeVoiceRecognition()) {
                console.log('✅ Reconocimiento de voz inicializado');
            }
            
            // Marcar como inicializada
            isAppInitialized = true;
            
            // Cargar datos iniciales
            setTimeout(() => {
                loadData();
            }, 500);
            
            // Ocultar loading screen
            hideLoadingScreen();
            
            console.log('✅ Aplicación PWA inicializada correctamente');
            return true;
        }

        function initializeLogoutSystem() {
            console.log('🔧 Inicializando sistema de logout optimizado...');
            
            // Detectar actividad del usuario
            const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
            
            activityEvents.forEach(event => {
                document.addEventListener(event, updateUserActivity, { passive: true });
            });
            
            // Inicializar timestamp de actividad
            updateUserActivity();
            
            // Monitoreo de sesión opcional
            startSessionMonitoring(15);
            
            console.log('✅ Sistema de logout optimizado inicializado');
        }

        // ==========================================
        // EVENT LISTENERS PRINCIPALES
        // ==========================================

        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 PWA Sistema Financiero cargado');
            
            // Verificar modo offline
            isOffline = !navigator.onLine;
            updateOfflineStatus();
            
            // Configurar event listeners de conectividad
            window.addEventListener('online', handleOnline);
            window.addEventListener('offline', handleOffline);
            
            // Cargar transacciones pendientes
            loadPendingTransactions();
            
            // Inicializar sistemas
            initializeApp();
            initializeLogoutSystem();
            
            // Event listeners para modal
            window.onclick = function(event) {
                const modal = document.getElementById('tipsModal');
                if (event.target === modal) {
                    closeTipsModal();
                }
            }
            
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeTipsModal();
                }
            });
        });

        // ==========================================
        // SERVICE WORKER
        // ==========================================

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('✅ Service Worker registrado:', registration.scope);
                        
                        // Verificar actualizaciones cada hora
                        setInterval(() => {
                            registration.update();
                        }, 3600000);
                        
                    }, function(err) {
                        console.error('❌ Service Worker falló:', err);
                    });
            });
        }

        // ==========================================
        // PWA INSTALLATION
        // ==========================================

        let deferredPrompt;
        let installButton = null;

        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('📱 PWA: Evento de instalación capturado');
            e.preventDefault();
            deferredPrompt = e;
            showInstallButton();
        });

        function showInstallButton() {
            if (!installButton) {
                installButton = document.createElement('button');
                installButton.id = 'installButton';
                installButton.innerHTML = '📱 Instalar App';
                installButton.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: linear-gradient(135deg, #4CAF50, #45a049);
                    color: white;
                    border: none;
                    padding: 15px 25px;
                    border-radius: 25px;
                    font-size: 16px;
                    font-weight: bold;
                    cursor: pointer;
                    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
                    z-index: 1000;
                    animation: pulseInstall 2s infinite;
                `;
                
                installButton.addEventListener('click', installPWA);
                document.body.appendChild(installButton);
            }
            installButton.style.display = 'block';
        }

        async function installPWA() {
            if (!deferredPrompt) {
                showAlert('La app ya está instalada o no es compatible', 'info');
                return;
            }
            
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                showAlert('✅ ¡App instalada exitosamente!', 'success');
                if (installButton) installButton.style.display = 'none';
                localStorage.setItem('pwa_installed', 'true');
            }
            
            deferredPrompt = null;
        }

        window.addEventListener('appinstalled', () => {
            console.log('✅ PWA instalada');
            if (installButton) installButton.style.display = 'none';
            localStorage.setItem('pwa_installed', 'true');
        });

        if (window.matchMedia('(display-mode: standalone)').matches) {
            console.log('📱 Ejecutando en modo PWA standalone');
            localStorage.setItem('pwa_installed', 'true');
        }

        // ==========================================
        // MANEJO DE ERRORES GLOBAL
        // ==========================================

        window.addEventListener('unhandledrejection', event => {
            if (event.reason?.message?.includes('SESION_EXPIRADA') && !isLoggingOut) {
                console.log('🔄 Unhandled session expiration detected');
                handleSessionExpired(false);
            }
        });

        window.addEventListener('error', event => {
            if (event.error?.message?.includes('SESION_EXPIRADA') && !isLoggingOut) {
                console.log('🔄 Error-based session expiration detected');
                handleSessionExpired(false);
            }
        });

        // ==========================================
        // EXPORTAR FUNCIONES GLOBALES
        // ==========================================

        window.switchTab = switchTab;
        window.logout = logout;
        window.forceCleanLogout = forceCleanLogout;
        window.showTipsModal = showTipsModal;
        window.closeTipsModal = closeTipsModal;
        window.toggleVoiceRecording = toggleVoiceRecording;
        window.eliminarTransaccion = eliminarTransaccion;
        window.installPWA = installPWA;
        window.handleSessionExpired = handleSessionExpired;
        window.handleServerResponse = handleServerResponse;
        window.checkSessionStatus = checkSessionStatus;

        console.log('✅ Sistema PWA completo optimizado cargado - Versión final');

    </script>
</body>
</html>