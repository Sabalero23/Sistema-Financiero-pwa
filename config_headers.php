<?php
// config_headers.php - Configuración limpia de headers para PWA

// Función para limpiar output buffer de forma segura
function limpiarOutputBuffer() {
    // Limpiar todos los buffers de output activos
    while (ob_get_level()) {
        ob_end_clean();
    }
}

// Función para configurar headers PWA seguros
function configurarHeadersPWA() {
    // Solo configurar si headers no han sido enviados
    if (!headers_sent()) {
        // Headers para PWA y cache
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
        
        // Headers de seguridad
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        
        // Content Security Policy básico
        header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob:");
        
        return true;
    }
    return false;
}

// Función para enviar respuesta JSON segura
function enviarRespuestaJSON($data, $httpCode = 200) {
    // Limpiar cualquier output previo
    limpiarOutputBuffer();
    
    // Configurar headers JSON si es posible
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        if ($httpCode !== 200) {
            http_response_code($httpCode);
        }
    }
    
    echo json_encode($data);
    exit;
}

// Función para redireccionar de forma segura
function redireccionarSeguro($url, $permanente = false) {
    if (!headers_sent()) {
        $codigo = $permanente ? 301 : 302;
        header("Location: $url", true, $codigo);
        exit;
    } else {
        // Fallback JavaScript si headers ya enviados
        echo "<script>window.location.replace('$url');</script>";
        exit;
    }
}

// Auto-configurar headers básicos al incluir este archivo
configurarHeadersPWA();
?>