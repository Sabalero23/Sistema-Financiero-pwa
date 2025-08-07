# ?? Sistema de Registro Financiero PWA

Sistema web completo para gestionar ingresos y egresos con reconocimiento de voz avanzado en espa?ol, autenticaci��n segura, gesti��n de usuarios y funcionalidades PWA offline.

## ?? Caracter��sticas Principales

- **?? Registro Manual** - Formulario tradicional para transacciones con validaci��n completa
- **?? Control por Voz Avanzado** - Comandos en espa?ol natural con procesamiento inteligente optimizado
- **?? Dashboard en Tiempo Real** - Estad��sticas y balance autom��tico con actualizaciones en vivo
- **?? Autenticaci��n Multi-Usuario** - Sistema de login protegido con roles y sesiones seguras
- **?? PWA Completa** - Funciona offline, instalable, con Service Worker y sincronizaci��n
- **??? Gesti��n de Transacciones** - Crear, eliminar, filtrar y visualizar registros con confirmaci��n inteligente
- **?? Panel de Administraci��n** - Gesti��n completa de usuarios con roles y permisos granulares
- **?? Responsive Avanzado** - Compatible con m��viles y tablets con optimizaciones espec��ficas
- **?? Setup Autom��tico** - Configuraci��n inicial inteligente con diagn��sticos completos
- **?? Logout Optimizado** - Sistema de cierre de sesi��n inteligente sin afectar otras pesta?as

## ?? Requisitos del Sistema

- **PHP 7.4+** (recomendado PHP 8.0+)
- **MySQL 5.7+ / MariaDB 10.3+**
- **Servidor web** (Apache/Nginx) con mod_rewrite
- **Navegador moderno** con soporte para micr��fono y Service Worker
- **HTTPS** (recomendado para reconocimiento de voz y PWA)
- **Extensiones PHP**: PDO, PDO_MySQL, JSON, OpenSSL

## ? Instalaci��n R��pida

### 1. Configurar Base de Datos
```sql
CREATE DATABASE finanzaspwa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'finanzaspwa'@'localhost' IDENTIFIED BY 'iX5AwWPASRak3pYH';
GRANT ALL PRIVILEGES ON finanzaspwa.* TO 'finanzaspwa'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Configurar Conexi��n
Editar `database.php`:
```php
private $host = 'localhost';
private $db_name = 'finanzaspwa';
private $username = 'finanzaspwa';
private $password = 'iX5AwWPASRak3pYH';
```

### 3. Ejecutar Setup Inicial
```
1. Subir archivos al servidor
2. Acceder a: https://tu-dominio.com/setup.php
3. Ejecutar "Setup Completo" 
4. Verificar diagn��sticos del sistema
5. Ir a: https://tu-dominio.com/login.php
```

### 4. Acceso Inicial
```
URL: https://tu-dominio.com/login.php
```

## ?? Credenciales por Defecto

| Usuario | Contrase?a | Rol | Descripci��n |
|---------|------------|-----|-------------|
| `admin` | `admin123` | Administrador | Acceso completo + gesti��n usuarios |
| `usuario` | `usuario123` | Usuario | Gesti��n de transacciones personales |

> ?? **IMPORTANTE:** Cambiar estas credenciales inmediatamente en producci��n

## ?? Control por Voz - Gu��a Completa

### ? Comandos Optimizados que Funcionan
```bash
# Formatos num��ricos b��sicos
"ingreso de 150000 t��tulo salario"
"egreso 25000 t��tulo compras del mes"

# N��meros con separadores (reconocidos por voz)
"ingreso de 150,000 t��tulo proyecto freelance"  
"egreso 1,200,000 t��tulo auto nuevo"

# Millones decimales 
"ingreso 1.5 millones t��tulo negocio"     �� $1,500,000
"egreso 0.5 millones t��tulo casa"         �� $500,000
"ingreso 2.3 millones t��tulo inversi��n"   �� $2,300,000

# Millones compuestos (reconocimiento natural)
"egreso un mill��n 200 000 t��tulo inmueble"    �� $1,200,000
"ingreso dos millones 500 000 t��tulo venta"   �� $2,500,000

# N��meros con espacios (com��n en m��viles)
"ingreso 200 000 t��tulo salario mensual"     �� $200,000
"egreso 1 500 000 t��tulo proyecto grande"    �� $1,500,000

# Abreviaciones reconocidas
"ingreso 150k t��tulo freelance web"          �� $150,000
"egreso 250k t��tulo equipos oficina"         �� $250,000

# N��meros en palabras (m��s confiable)
"ingreso doscientos mil t��tulo bonus anual"     �� $200,000
"egreso cincuenta mil t��tulo reparaci��n auto"   �� $50,000
"ingreso quinientos mil t��tulo comisi��n venta"  �� $500,000
```

### ?? Patrones de Comando Reconocidos
- **Inicio**: `"crear"`, `"nuevo"`, `"ingreso"`, `"egreso"`
- **Monto**: N��meros en texto o d��gitos + `"mil"`, `"millones"`
- **T��tulo**: `"t��tulo"`, `"con t��tulo"`, `"llamado"`
- **Descripci��n**: `"descripci��n"`, `"detalle"` (opcional)

### ?? Optimizaci��n por Dispositivo

#### **Android (Chrome/Firefox)**
```bash
# Configuraci��n recomendada:
- Habla 2x m��s fuerte que en PC
- Usa Chrome actualizado
- Desactiva "Ok Google" temporalmente
- Verifica: Configuraci��n > Apps > Chrome > Permisos > Micr��fono

# Comandos optimizados:
"ingreso ciento cincuenta mil t��tulo salario"
"egreso veinticinco mil t��tulo supermercado"
"ingreso medio mill��n t��tulo bonus"
```

#### **iOS/iPhone (Safari)**
```bash
# Configuraci��n recomendada:
- Usa Safari (mejor compatibilidad)
- Habla al micr��fono inferior del tel��fono
- Desactiva Siri temporalmente
- Configurar: Ajustes > Safari > C��mara y Micr��fono > Permitir

# Comandos optimizados:
"ingreso doscientos mil t��tulo freelance"
"egreso cincuenta mil t��tulo compras"
"nuevo ingreso de quinientos mil t��tulo proyecto"
```

## ?? Funciones Avanzadas del Sistema

### ?? PWA (Progressive Web App)
- **Instalaci��n nativa** - Se puede instalar como aplicaci��n en cualquier dispositivo
- **Funcionalidad offline** - Trabaja sin conexi��n a internet
- **Service Worker** - Cache inteligente y sincronizaci��n autom��tica
- **Manifest.json** - Configuraci��n completa para instalaci��n
- **Sincronizaci��n** - Los datos offline se sincronizan autom��ticamente al reconectar
- **Push notifications** - Notificaciones de sincronizaci��n y updates

### ??? Gesti��n Avanzada de Transacciones
- **Eliminar transacciones** con bot��n X en cada registro y confirmaci��n detallada
- **Confirmaci��n inteligente** mostrando detalles completos de la transacci��n
- **Animaciones suaves** de eliminaci��n con feedback visual y sonoro
- **Actualizaci��n autom��tica** del balance y estad��sticas en tiempo real
- **Historial visual** con ��conos ?? (voz) y ?? (manual) para identificar origen
- **Formato monetario** completo en pesos argentinos con separadores de miles

### ?? Dashboard Mejorado y Completo
- **Estad��sticas en tiempo real**: Total ingresos, egresos, balance autom��tico
- **Contador de transacciones** con m��tricas detalladas y an��lisis
- **Balance autom��tico** calculado matem��ticamente (ingresos - egresos)
- **Actualizaci��n autom��tica** cada 30 segundos con indicadores visuales
- **Formato de moneda argentina** (ARS) con separadores de miles correctos
- **Indicadores PWA** - Modo offline, estado de sincronizaci��n

### ?? Sistema Multi-Usuario Completo
- **Roles granulares**: Administrador y Usuario con permisos espec��ficos
- **Sesiones seguras** almacenadas en base de datos con timeout configurable
- **Transacciones por usuario** - completa separaci��n de datos por usuario
- **Validaci��n autom��tica** de permisos en cada operaci��n API
- **Protecci��n contra fuerza bruta** con bloqueo temporal progresivo
- **Gesti��n de sesiones** - control de sesiones activas por usuario

### ?? Panel de Administraci��n Avanzado (`user_management.php`)
- **Crear usuarios** con validaci��n completa de datos y restricciones
- **Activar/Desactivar usuarios** con un solo clic y confirmaci��n
- **Resetear contrase?as** con generador autom��tico seguro
- **Desbloquear usuarios** despu��s de intentos fallidos autom��ticamente
- **Editar usuarios** - perfiles completos con validaci��n en tiempo real
- **Eliminar usuarios** con confirmaci��n m��ltiple y restricciones de seguridad
- **Estad��sticas del sistema** en tiempo real con m��tricas completas
- **Sesiones activas** - monitoreo de usuarios conectados
- **Gesti��n de roles** - cambio de permisos con validaciones

### ??? Setup Inteligente y Completo (`setup.php`)
- **Diagn��stico integral** de base de datos, conexiones y permisos
- **Creaci��n autom��tica** de todas las tablas con relaciones correctas
- **Usuarios demo** incluidos para pruebas inmediatas
- **Verificaci��n de conectividad** y tests de rendimiento
- **Limpieza autom��tica** de sesiones expiradas y optimizaci��n
- **Estad��sticas detalladas** del sistema con conteos y m��tricas
- **Validaci��n de integridad** de datos y estructura

### ?? Sistema de Logout Optimizado
- **Logout espec��fico por sesi��n** - no afecta otras pesta?as del mismo usuario
- **Confirmaci��n visual** con animaciones y feedback inmediato
- **Limpieza selectiva** de datos de sesi��n sin afectar configuraciones PWA
- **Redirecci��n inteligente** con timeouts y fallbacks autom��ticos
- **Manejo de errores** robusto con m��ltiples estrategias de logout
- **Preservaci��n de datos offline** durante el logout

### ?? Sistema de Autenticaci��n Mejorado (`login.php`)
- **Mostrar/Ocultar contrase?a** con bot��n visual interactivo
- **Recordar credenciales** por 30 d��as con cookies seguras HttpOnly
- **Validaci��n en tiempo real** de campos con feedback visual
- **Auto-focus inteligente** en campos relevantes
- **Carga autom��tica** de credenciales recordadas con animaciones
- **Protecci��n contra fuerza bruta** con contadores y bloqueos temporales

## ?? Estructura Completa de Archivos

```
sistema-financiero-pwa/
������ ?? Autenticaci��n y Seguridad
��   ������ login.php              # Login con recordar credenciales y validaci��n
��   ������ auth.php               # Sistema de autenticaci��n optimizado
��   ������ logout.php             # Logout optimizado por sesi��n espec��fica
��   ������ user_management.php    # Panel admin completo con CRUD usuarios
��
������ ?? Aplicaci��n Principal PWA
��   ������ index.php              # Dashboard PWA con funcionalidad offline
��   ������ api.php                # API REST completa con manejo de errores
��   ������ database.php           # Configuraci��n BD con seguridad mejorada
��   ������ config_headers.php     # Headers de seguridad PWA
��
������ ?? PWA y Offline
��   ������ manifest.json          # Manifest PWA completo con shortcuts
��   ������ sw.js                  # Service Worker con cache inteligente
��   ������ offline.html           # P��gina offline interactiva
��   ������ icons/                 # Iconos PWA en m��ltiples tama?os
��
������ ?? Configuraci��n y Herramientas
��   ������ setup.php              # Setup completo con diagn��sticos avanzados
��   ������ test_voice.php         # Test exhaustivo de reconocimiento de voz
��
������ ?? Documentaci��n Completa
��   ������ README.md              # Documentaci��n completa actualizada
��   ������ CHANGELOG.md           # Historial detallado de cambios
��   ������ INSTALL.md             # Gu��a de instalaci��n paso a paso
��
������ ?? Configuraci��n de Servidor
    ������ .htaccess              # Configuraci��n Apache con seguridad PWA
    ������ .env.example           # Variables de entorno para producci��n
    ������ robots.txt             # SEO y seguridad
```

## ??? API REST Completa y Documentada

### ?? Endpoints Principales

#### Autenticaci��n Avanzada
```http
# Verificar sesi��n con estad��sticas
GET /auth.php?action=status
Response: {
  "success": true,
  "user": {
    "id": 1,
    "username": "admin",
    "nombre": "Administrador",
    "email": "admin@sistema.com",
    "rol": "admin"
  },
  "session_stats": {
    "session_duration": 1800,
    "time_remaining": 1200,
    "expires_at": 1693564800
  },
  "is_admin": true,
  "timestamp": 1693563000
}

# Renovar sesi��n activa
GET /auth.php?action=renew
Response: { 
  "success": true, 
  "message": "Sesi��n renovada",
  "timestamp": 1693563000 
}

# Logout optimizado por sesi��n
GET /auth.php?action=logout
Response: { 
  "success": true, 
  "message": "Sesi��n espec��fica cerrada correctamente",
  "redirect": "login.php" 
}

# Ping de conectividad
GET /auth.php?action=ping
Response: { 
  "pong": true, 
  "timestamp": 1693563000,
  "server_time": "2023-08-31 15:30:00" 
}
```

#### Transacciones con Metadatos
```http
# Obtener resumen financiero completo
GET /api.php?action=obtener_resumen
Response: {
  "success": true,
  "data": {
    "total_ingresos": 2500000,
    "total_egresos": 1200000,
    "balance": 1300000,
    "total_transacciones": 45,
    "promedio_ingreso": 125000,
    "promedio_egreso": 60000,
    "transacciones_mes": 12,
    "balance_mes": 450000
  },
  "timestamp": 1693563000
}

# Obtener transacciones con filtros
GET /api.php?action=obtener_transacciones&limit=50&tipo=ingreso&mes=2023-08
Response: {
  "success": true,
  "data": [
    {
      "id": 1,
      "tipo": "ingreso",
      "monto": 250000,
      "titulo": "Freelance Desarrollo Web",
      "descripcion": "Proyecto e-commerce para cliente corporativo",
      "metodo_creacion": "audio",
      "fecha_creacion": "2023-08-15 14:30:00",
      "monto_formateado": "$250.000,00"
    }
  ],
  "meta": {
    "total": 45,
    "filtered": 12,
    "page": 1,
    "per_page": 50
  }
}

# Procesar comando de voz con debug
POST /api.php
Content-Type: application/json
{
  "action": "procesar_voz",
  "texto": "ingreso de doscientos cincuenta mil t��tulo freelance web"
}
Response: {
  "success": true,
  "message": "Transacci��n creada exitosamente por voz",
  "id": 123,
  "monto_formateado": "$250.000,00",
  "debug": {
    "texto_original": "ingreso de doscientos cincuenta mil t��tulo freelance web",
    "texto_normalizado": "ingreso de doscientos cincuenta mil titulo freelance web",
    "patron_usado": "ingreso",
    "monto_extraido": "doscientos cincuenta mil",
    "monto_convertido": 250000,
    "titulo_extraido": "freelance web"
  }
}

# Eliminar transacci��n con confirmaci��n
POST /api.php
Content-Type: application/json
{
  "action": "eliminar_transaccion",
  "id": 123
}
Response: {
  "success": true,
  "message": "Transacci��n eliminada: Freelance Web (ingreso de $250.000)",
  "transaccion_eliminada": {
    "id": 123,
    "titulo": "Freelance Web",
    "monto": 250000,
    "tipo": "ingreso"
  }
}
```

#### Gesti��n de Usuarios (Admin)
```http
# Crear usuario con validaciones
POST /user_management.php
Content-Type: application/x-www-form-urlencoded
action=crear_usuario&username=nuevo_user&password=password123&nombre=Nuevo Usuario&email=nuevo@ejemplo.com&rol=usuario

# Editar usuario existente
POST /user_management.php
Content-Type: application/x-www-form-urlencoded
action=actualizar_usuario_admin&user_id=2&username=usuario_editado&nombre=Usuario Editado&email=editado@ejemplo.com&rol=usuario

# Eliminar usuario con confirmaci��n
POST /user_management.php
Content-Type: application/x-www-form-urlencoded
action=eliminar_usuario&user_id=2&confirm_username=usuario_exacto

# Desbloquear usuario bloqueado
POST /user_management.php
Content-Type: application/x-www-form-urlencoded
action=desbloquear_usuario&user_id=2
```

## ?? Configuraci��n Avanzada para Producci��n

### Variables de Entorno Recomendadas
```bash
# .env
DB_HOST=localhost
DB_NAME=finanzaspwa
DB_USER=finanzaspwa_user
DB_PASS=password_ultra_seguro_2024!
DB_CHARSET=utf8mb4

# Configuraci��n de sesiones
SESSION_LIFETIME=1800
SESSION_NAME=SISTEMA_FINANCIERO_PWA
SESSION_COOKIE_SECURE=true
SESSION_COOKIE_HTTPONLY=true

# Configuraci��n PWA
PWA_CACHE_VERSION=v2.0.0
PWA_OFFLINE_ENABLED=true
PWA_SYNC_ENABLED=true

# Configuraci��n de seguridad
ENABLE_HTTPS_REDIRECT=true
ENABLE_BRUTEFORCE_PROTECTION=true
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_DURATION=900
ENABLE_SESSION_FINGERPRINTING=true
```

### Configuraci��n Apache para PWA (.htaccess)
```apache
# Redirigir a HTTPS para PWA
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Headers espec��ficos para PWA
<IfModule mod_headers.c>
    # Headers de seguridad PWA
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Service Worker headers
    <FilesMatch "sw\.js$">
        Header set Cache-Control "no-cache, no-store, must-revalidate"
        Header set Content-Type "application/javascript"
    </FilesMatch>
    
    # Manifest headers
    <FilesMatch "manifest\.json$">
        Header set Content-Type "application/manifest+json"
        Header set Cache-Control "public, max-age=86400"
    </FilesMatch>
    
    # Offline page headers
    <FilesMatch "offline\.html$">
        Header set Cache-Control "public, max-age=86400"
    </FilesMatch>
</IfModule>

# Proteger archivos sensibles
<Files "database.php">
    Require all denied
</Files>
<Files ".env">
    Require all denied
</Files>
<Files "*.log">
    Require all denied
</Files>

# Cacheo optimizado para PWA
<IfModule mod_expires.c>
    ExpiresActive On
    
    # Assets est��ticos
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    
    # Manifest y SW (cache corto)
    ExpiresByType application/manifest+json "access plus 1 day"
    
    # API endpoints (sin cache)
    ExpiresByType application/json "access plus 0 seconds"
</IfModule>

# Compresi��n GZIP para PWA
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE application/manifest+json
</IfModule>
```

### Configuraci��n PHP para Producci��n
```php
// config_production.php - Configuraci��n segura para producci��n PWA
<?php
class ProductionConfig {
    public static function init() {
        // Configuraci��n de sesi��n segura
        ini_set('session.cookie_lifetime', 0);
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        
        // Configuraci��n de errores (ocultar en producci��n)
        error_reporting(0);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        ini_set('error_log', '/var/log/php/finanzas_errors.log');
        
        // Headers de seguridad PWA
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            
            // Content Security Policy para PWA
            header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob:; manifest-src 'self'; worker-src 'self'");
            
            // Feature Policy para PWA
            header("Permissions-Policy: microphone=(self), camera=(), geolocation=()");
        }
    }
    
    public static function getDatabaseConfig() {
        return [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'dbname' => $_ENV['DB_NAME'] ?? 'finanzaspwa',
            'username' => $_ENV['DB_USER'] ?? 'finanzaspwa_user',
            'password' => $_ENV['DB_PASS'] ?? 'secure_password_here',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'"
            ]
        ];
    }
}

// Inicializar configuraci��n de producci��n
ProductionConfig::init();
?>
```

## ?? Gu��a de Uso del Sistema PWA

### 1. **Instalaci��n y Configuraci��n Inicial**
```bash
1. Crear base de datos MySQL con charset utf8mb4
2. Configurar credenciales seguras en database.php
3. Subir archivos al servidor web con HTTPS habilitado
4. Ejecutar setup.php para configuraci��n autom��tica completa
5. Verificar diagn��sticos del sistema y PWA
6. Instalar PWA desde el navegador (bot��n "Instalar App")
7. Acceder a login.php con credenciales por defecto
```

### 2. **Primer Uso como Administrador PWA**
```bash
1. Login como admin/admin123 desde PWA instalada
2. Ir a "?? Admin" �� user_management.php
3. Cambiar contrase?a del admin inmediatamente
4. Crear usuarios adicionales con roles apropiados
5. Probar creaci��n de transacciones (manual y voz)
6. Verificar funcionamiento offline (desconectar internet)
7. Probar sincronizaci��n al reconectar
8. Verificar estad��sticas del dashboard PWA
```

### 3. **Uso Diario del Sistema PWA**
```bash
1. Abrir PWA instalada o acceder v��a navegador
2. Login autom��tico si credenciales fueron recordadas
3. Dashboard muestra estado de conectividad (online/offline)
4. Registrar transacciones:
   - ?? Manual: Formulario con validaci��n en tiempo real
   - ?? Por voz: Comandos optimizados con feedback inmediato
5. Funciona completamente offline:
   - Crear transacciones sin conexi��n
   - Se guardan localmente y se sincronizan autom��ticamente
6. Eliminar/editar transacciones con confirmaci��n inteligente
7. Monitoreo de balance en tiempo real
8. Logout seguro sin afectar otras sesiones
```

### 4. **Gesti��n de Usuarios PWA (Solo Admin)**
```bash
1. Acceder a user_management.php desde PWA
2. Dashboard de usuarios con estad��sticas en tiempo real
3. Crear usuarios con validaci��n completa:
   - Username ��nico con patrones seguros
   - Email v��lido y ��nico
   - Contrase?as seguras (m��nimo 6 caracteres)
   - Roles granulares (Usuario/Administrador)
4. Gestionar usuarios existentes:
   - Editar perfiles con validaci��n en tiempo real
   - Activar/desactivar usuarios con un clic
   - Resetear contrase?as con generador autom��tico
   - Desbloquear cuentas bloqueadas
   - Eliminar usuarios con confirmaci��n m��ltiple
5. Monitorear sesiones activas y estad��sticas del sistema
6. Todo funciona offline con sincronizaci��n autom��tica
```

## ?? Caracter��sticas Avanzadas del Dashboard PWA

### Estad��sticas en Tiempo Real Mejoradas
- ?? **Total Ingresos** - Suma completa con formateo de moneda argentina
- ?? **Total Egresos** - Suma total de gastos con indicadores visuales
- ?? **Balance Calculado** - Diferencia matem��tica autom��tica (ingresos - egresos)
- ?? **Total Transacciones** - Contador con an��lisis de tendencias
- ?? **Promedios** - C��lculo autom��tico de promedios por tipo de transacci��n
- ?? **Estad��sticas Mensuales** - An��lisis del mes actual vs anteriores
- ?? **Indicadores PWA** - Estado de conectividad y sincronizaci��n

### Lista de Transacciones PWA Avanzada
- ???? **��conos diferenciados** - Visual claro entre Manual (??) y Por Voz (??)
- ??? **Fecha/hora completa** - Formato localizado argentino con timestamps relativos
- ?????? **Formato moneda argentina** - Pesos con separadores de miles correctos
- ?? **Eliminar con confirmaci��n detallada** - Muestra detalles completos antes de confirmar
- ??? **Actualizaci��n autom��tica** - Refresh cada 30 segundos con indicadores visuales
- ???? **Dise?o responsive PWA** - Optimizaci��n espec��fica para m��viles y tablets
- ???? **Indicadores de estado** - Online/Offline con sincronizaci��n autom��tica
- ??? **Animaciones fluidas** - Transiciones suaves con feedback haptic (m��viles)

### Funciones PWA Exclusivas
- ?? **Instalaci��n nativa** - Funciona como app nativa en cualquier dispositivo
- ?? **Modo offline completo** - Todas las funciones disponibles sin internet
- ?? **Sincronizaci��n autom��tica** - Background sync al reconectar
- ?? **Notificaciones push** - Alertas de sincronizaci��n y updates (opcional)
- ?? **Storage inteligente** - Cache selectivo que preserva espacio
- ? **Carga instant��nea** - Service Worker optimizado para velocidad m��xima

### Seguridad PWA Avanzada
- ?? **Sesiones seguras por pesta?a** - No interfiere con otras sesiones del usuario
- ??? **Validaci��n continua** de permisos con renovaci��n autom��tica
- ?? **Protecci��n CSRF** avanzada con tokens din��micos
- ?? **Audit trail** completo para transacciones y cambios de usuarios
- ?? **Headers de seguridad** - CSP, HSTS, y configuraci��n PWA completa
- ?? **Detecci��n de ataques** - Bloqueo autom��tico por fuerza bruta

## ?? Diagn��stico y Soluci��n de Problemas PWA

### ?? Herramientas de Diagn��stico Avanzadas

#### 1. Setup.php - Diagn��stico Integral PWA
```
https://tu-dominio.com/setup.php

? Verificar conexi��n a base de datos con tests de rendimiento
? Comprobar existencia y estructura de todas las tablas
? Contar usuarios, transacciones y sesiones activas
? Limpiar sesiones expiradas y optimizar BD
? Verificar configuraci��n PWA y Service Worker
? Test de conectividad API con m��tricas de respuesta
? Validar headers de seguridad y configuraci��n HTTPS
? Crear usuarios demo con datos de prueba
? Verificar permisos de archivos y configuraci��n del servidor
? Diagn��stico de cache y storage del navegador
```

#### 2. Test_voice.php - Prueba Exhaustiva de Reconocimiento
```
https://tu-dominio.com/test_voice.php

? Probar micr��fono y permisos del navegador
? Verificar compatibilidad completa del navegador
? Test de comandos espec��ficos con 15+ patrones optimizados
? Debug detallado de conversi��n de texto a n��meros
? An��lisis de patrones de reconocimiento con m��tricas
? Consejos espec��ficos por dispositivo (Android/iOS)
? Simulaci��n de comandos complejos con validaci��n
? Test de n��meros en palabras vs d��gitos
? Verificaci��n de formatos de moneda y separadores
? An��lisis de rendimiento de reconocimiento de voz
```

#### 3. Herramientas PWA del Navegador
```bash
# Chrome DevTools - PWA Audit
1. F12 �� Lighthouse �� PWA Audit
2. Application �� Service Workers �� Inspeccionar SW
3. Application �� Storage �� Verificar caches
4. Application �� Manifest �� Validar configuraci��n

# Firefox DevTools - PWA Tools
1. F12 �� Application �� Service Workers
2. Storage �� IndexedDB/Cache �� Verificar datos offline
3. Network �� Offline �� Test funcionalidad sin conexi��n

# Safari (iOS) - PWA Debug
1. Configuraci��n �� Safari �� Avanzado �� Web Inspector
2. Desarrollar �� iPhone �� Inspeccionar PWA
3. Timeline �� Analizar rendimiento offline
```

### ?? Problemas Comunes PWA y Soluciones

#### **PWA y Service Worker**
| Problema | Causa | Soluci��n |
|----------|-------|----------|
| "PWA no se instala" | Manifest incorrecto o HTTPS faltante | Verificar manifest.json y certificado SSL |
| "No funciona offline" | Service Worker no registrado | Verificar registro en index.php y sw.js |
| "Datos no se sincronizan" | Background sync no habilitado | Verificar permisos y conexi��n de red |
| "Cache no actualiza" | Versi��n de cache no cambiada | Actualizar CACHE_NAME en sw.js |
| "Instalaci��n no aparece" | Criterios PWA no cumplidos | Ejecutar audit de Lighthouse |

#### **Reconocimiento de Voz Mejorado**
| Problema | Causa | Soluci��n |
|----------|-------|----------|
| Error "not-allowed" | Permisos denegados o HTTP | Usar HTTPS y otorgar permisos en configuraci��n |
| No reconoce en m��vil | Micr��fono poco sensible | Hablar 2x m��s fuerte, usar Chrome/Safari |
| N��meros incorrectos | Formato no reconocido | Usar n��meros en palabras: "cincuenta mil" |
| Se corta la grabaci��n | No detecta voz continua | Hablar inmediatamente despu��s del beep |
| Patrones no coinciden | Comando mal estructurado | Seguir patrones: "ingreso MONTO t��tulo TITULO" |

#### **Base de Datos y API**
| Error | Causa | Soluci��n |
|-------|-------|----------|
| "Connection failed" | Credenciales o servidor BD | Verificar database.php y servicio MySQL |
| "Table doesn't exist" | Tablas no creadas correctamente | Ejecutar setup.php completo |
| "API timeout" | Consultas lentas o servidor sobrecargado | Optimizar consultas y aumentar l��mites PHP |
| "JSON parse error" | Respuesta malformada del servidor | Verificar logs de error PHP y formato API |
| "CORS error" | Headers de seguridad | Verificar config_headers.php |

#### **Autenticaci��n y Sesiones PWA**
| Error | Causa | Soluci��n |
|-------|-------|----------|
| "Session expired" | Timeout o limpieza autom��tica | Login autom��tico - funcionalidad normal |
| "Invalid credentials" | Usuario/contrase?a incorrectos | Usar user_management.php para resetear |
| "Permission denied" | Rol insuficiente o sesi��n corrupta | Verificar rol en BD o re-login |
| "Multiple sessions" | Sesiones duplicadas | Sistema optimizado - no requiere acci��n |
| "Logout loop" | Error en redirecci��n | Verificar configuraci��n de headers |

#### **Problemas Espec��ficos de M��viles PWA**
| Error | Dispositivo | Soluci��n |
|-------|-------------|----------|
| "Micr��fono no funciona" | iOS Safari | Configurar: Ajustes > Safari > Micr��fono > Permitir |
| "PWA no instala" | Android Chrome | Verificar manifest.json y criterios PWA |
| "App lenta" | Dispositivos antiguos | Optimizar cache y reducir animaciones |
| "Sincronizaci��n falla" | Conexi��n intermitente | Sistema robusto con reintentos autom��ticos |
| "Notificaciones no llegan" | Permisos del sistema | Configurar en ajustes del dispositivo |

## ?? Optimizaci��n PWA M��vil Detallada

### ?? Android - Configuraci��n Completa PWA
```bash
Configuraci��n Recomendada para PWA:
? Chrome 90+ actualizado con flags PWA habilitados
? Configuraci��n > Apps > Chrome > Permisos > Micr��fono/Storage
? Instalar PWA: Chrome > Men�� > "Agregar a pantalla de inicio"
? Desactivar "Ok Google" durante uso de reconocimiento de voz
? Conexi��n WiFi estable (mejor rendimiento que datos m��viles)
? Habilitar "Datos en segundo plano" para sync offline
? Configurar notificaciones PWA en ajustes del sistema

Optimizaciones Espec��ficas Android PWA:
? Usar teclado f��sico Bluetooth para entrada r��pida
? Configurar gestos de navegaci��n para mejor UX
? Habilitar modo "No molestar" para grabaci��n de voz
? Optimizar bater��a: Configuraci��n > Bater��a > Optimizaci��n > Excluir PWA
? Verificar espacio de almacenamiento para cache offline

Comandos de Voz Optimizados Android:
"ingreso ciento cincuenta mil pesos t��tulo salario mensual"
"egreso veinticinco mil pesos t��tulo supermercado compras"
"nuevo ingreso medio mill��n pesos t��tulo bonus anual"
"crear egreso doscientos mil pesos t��tulo reparaci��n auto"
```

### ?? iOS/iPhone - Configuraci��n Completa PWA
```bash
Configuraci��n Recomendada para PWA iOS:
? Safari 14+ con soporte PWA completo
? Configurar: Ajustes > Safari > C��mara/Micr��fono > Permitir
? Instalar PWA: Safari > Compartir > "Agregar a pantalla de inicio"
? Desactivar Siri temporalmente: "Oye Siri" OFF
? Hablar al micr��fono inferior del iPhone directamente
? iOS 15+ recomendado para mejor rendimiento PWA
? Configurar notificaciones: Ajustes > Notificaciones > PWA

Optimizaciones Espec��ficas iOS PWA:
? Habilitar "Acceso sin restricciones" en Tiempo de pantalla
? Configurar Focus Mode para concentraci��n durante uso
? Usar AirPods/auriculares con micr��fono para mejor reconocimiento
? Activar "Reducir movimiento" si hay lag en animaciones
? Verificar almacenamiento: Ajustes > General > Almacenamiento

Comandos de Voz Optimizados iOS:
"ingreso de doscientos mil pesos con t��tulo proyecto freelance"
"egreso de cincuenta mil pesos con t��tulo compras mensuales"
"crear nuevo ingreso quinientos mil pesos t��tulo consultor��a"
"nuevo egreso ciento veinticinco mil pesos t��tulo servicios"
```

### ??? Desktop - Configuraci��n PWA Avanzada
```bash
Windows 10/11 PWA:
? Chrome/Edge con PWA support habilitado
? Instalar desde: chrome://apps o edge://apps
? Configurar micr��fono en Configuraci��n > Privacidad > Micr��fono
? Usar Chrome flags: enable-desktop-pwas, enable-desktop-pwas-run-on-os-login
? Configurar inicio autom��tico: Configuraci��n > Apps > Inicio

macOS PWA:
? Safari 14+ o Chrome con soporte PWA
? Permisos: Preferencias del Sistema > Seguridad > Micr��fono
? Dock integration para f��cil acceso
? Configurar Spotlight search para la PWA

Linux PWA:
? Chrome/Chromium con flags PWA habilitados
? PulseAudio configurado correctamente para micr��fono
? Desktop integration con .desktop files
? Configurar autostart si es necesario
```

## ?? Seguridad y Mejores Pr��cticas PWA

### Para Producci��n PWA
```bash
Configuraci��n Obligatoria PWA:
- [ ] Cambiar TODAS las credenciales por defecto inmediatamente
- [ ] Configurar HTTPS obligatorio con certificado v��lido (Let's Encrypt)
- [ ] Implementar Content Security Policy estricta para PWA
- [ ] Configurar backup autom��tico de BD con versionado
- [ ] Habilitar logs de acceso, error y audit trail completo
- [ ] Configurar firewall con reglas espec��ficas para PWA
- [ ] Actualizar contrase?as regularmente (policy de 90 d��as)
- [ ] Usar variables de entorno para todas las credenciales
- [ ] Configurar l��mites de rate limiting en API endpoints
- [ ] Verificar integridad del Service Worker regularmente
- [ ] Configurar monitoreo de uptime y rendimiento PWA
- [ ] Implementar backup de cache y datos offline
```

### Monitoreo Continuo PWA
```bash
Verificaciones Regulares PWA:
- [ ] Revisar logs de error PHP, MySQL y Service Worker
- [ ] Monitorear espacio en disco y cache size
- [ ] Verificar sesiones activas y patrones sospechosos
- [ ] Backup regular de transacciones con integridad de datos
- [ ] Actualizar dependencias del sistema y navegadores soportados
- [ ] Revisar accesos administrativos y permisos granulares
- [ ] Test de funcionalidad offline semanalmente
- [ ] Verificar rendimiento de reconocimiento de voz
- [ ] Monitorear m��tricas de instalaci��n PWA
- [ ] Auditar configuraci��n de seguridad mensualmente
```

### Headers de Seguridad PWA Completos
```php
// Headers de seguridad PWA en production
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Robots-Tag: noindex, nofollow'); // Si es sistema interno

// Content Security Policy espec��fico para PWA
$csp = "default-src 'self'; " .
       "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
       "style-src 'self' 'unsafe-inline'; " .
       "img-src 'self' data: blob:; " .
       "font-src 'self'; " .
       "connect-src 'self'; " .
       "manifest-src 'self'; " .
       "worker-src 'self'; " .
       "child-src 'none'; " .
       "object-src 'none'; " .
       "base-uri 'self'; " .
       "form-action 'self'";

header("Content-Security-Policy: $csp");

// Feature Policy para PWA (permisos espec��ficos)
header("Permissions-Policy: microphone=(self), camera=(), geolocation=(), payment=(), usb=()");

// HSTS para PWA (HTTPS forzado)
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');

// Cache control para diferentes tipos de archivos PWA
if (strpos($_SERVER['REQUEST_URI'], 'sw.js') !== false) {
    header('Cache-Control: no-cache, no-store, must-revalidate');
} elseif (strpos($_SERVER['REQUEST_URI'], 'manifest.json') !== false) {
    header('Cache-Control: public, max-age=86400');
} elseif (strpos($_SERVER['REQUEST_URI'], '/api.php') !== false) {
    header('Cache-Control: no-cache, no-store, must-revalidate');
}
```

## ?? Soporte y Debug Avanzado PWA

### Orden de Diagn��stico Recomendado PWA
1. **setup.php** - Verificar configuraci��n general del sistema y PWA
2. **Chrome DevTools** - Lighthouse PWA audit y an��lisis de rendimiento
3. **test_voice.php** - Probar funcionalidad espec��fica de reconocimiento de voz
4. **Service Worker** - Verificar registro, cache y sincronizaci��n en DevTools
5. **Network Tab** - Analizar requests API y funcionamiento offline
6. **Application Tab** - Verificar manifest, storage y datos offline
7. **Logs del servidor** - Revisar `/var/log/apache2/error.log` y logs PHP
8. **Console del navegador** - Verificar errores JavaScript y PWA
9. **Conectividad y rendimiento** - Tests de velocidad y latencia API

### Activar Debug Detallado PWA (Temporalmente)
```php
// Agregar al inicio de archivos PHP para debug PWA
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_finanzas_debug.log');

// Debug espec��fico para PWA
define('PWA_DEBUG', true);
define('SW_DEBUG', true);
define('VOICE_DEBUG', true);

// Para debug de Service Worker
if (PWA_DEBUG) {
    echo "<script>console.log('PWA Debug Mode Enabled');</script>";
}

// Para debug de SQL espec��fico a PWA
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

### Logs de Actividad PWA Personalizados
```php
// Funci��n mejorada para logging PWA
function logPWAActivity($usuario_id, $accion, $detalles = '', $dispositivo = '') {
    $timestamp = date('Y-m-d H:i:s');
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    // Detectar si es PWA
    $is_pwa = strpos($user_agent, 'Mobile') !== false ? 'PWA-Mobile' : 'PWA-Desktop';
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        $is_pwa .= '-Ajax';
    }
    
    $log_entry = "$timestamp - Usuario:$usuario_id - $accion - $detalles - Dispositivo:$is_pwa - IP:$ip\n";
    
    // Log separado para PWA
    file_put_contents('logs/pwa_activity.log', $log_entry, FILE_APPEND | LOCK_EX);
    
    // Log adicional para voice commands
    if (strpos($accion, 'VOZ') !== false) {
        file_put_contents('logs/voice_activity.log', $log_entry, FILE_APPEND | LOCK_EX);
    }
}

// Uso mejorado en el c��digo PWA
logPWAActivity($_SESSION['user_id'], 'TRANSACCION_VOZ_CREADA', 'Monto: $150000, Comando: ingreso cincuenta mil t��tulo salario', 'Mobile-PWA');
logPWAActivity($_SESSION['user_id'], 'PWA_INSTALLED', 'Usuario instal�� PWA desde Chrome', 'Android-Chrome');
logPWAActivity($_SESSION['user_id'], 'OFFLINE_SYNC', 'Sincronizadas 3 transacciones offline', 'PWA-Background-Sync');
```

## ?? Estad��sticas y M��tricas PWA

### Dashboard de Administrador PWA
- ?? **Usuarios totales** - Estados activo/inactivo con ��ltima conexi��n PWA
- ?? **Transacciones globales** - An��lisis por tipo, m��todo (manual/voz) y dispositivo
- ?? **Crecimiento mensual** - Tendencias de usuarios PWA y adopci��n
- ?? **Uso de reconocimiento de voz** - Estad��sticas de ��xito vs manual
- ?? **Sesiones activas PWA** - Tiempo promedio de uso y patrones
- ?? **Instalaciones PWA** - M��tricas de instalaci��n por plataforma
- ?? **Sincronizaci��n offline** - Volumen y ��xito de sync autom��tico
- ?? **Cobertura de red** - An��lisis de uso online vs offline

### M��tricas de Rendimiento PWA
- ? **Tiempo de respuesta API** - Latencia promedio con breakdown por endpoint
- ?? **Tasa de ��xito reconocimiento** - Precisi��n de conversi��n voz a transacci��n
- ?? **Distribuci��n por dispositivos** - M��vil vs desktop vs tablet
- ?? **Navegadores utilizados** - Chrome, Safari, Firefox con versiones
- ?? **Funciones m��s populares** - Heat map de caracter��sticas usadas
- ?? **Tama?o de cache** - Uso de storage y eficiencia offline
- ?? **Engagement PWA** - Tiempo de sesi��n vs web tradicional
- ?? **Performance metrics** - FCP, LCP, CLS espec��ficos para PWA

### Analytics PWA Personalizados
```javascript
// Sistema de analytics PWA integrado
class PWAAnalytics {
    constructor() {
        this.sessionStart = Date.now();
        this.events = [];
        this.isInstalled = window.matchMedia('(display-mode: standalone)').matches;
        this.connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
    }
    
    // Track eventos espec��ficos PWA
    trackEvent(event, data = {}) {
        const eventData = {
            timestamp: Date.now(),
            event: event,
            session_duration: Date.now() - this.sessionStart,
            is_installed: this.isInstalled,
            is_online: navigator.onLine,
            connection_type: this.connection?.effectiveType || 'unknown',
            user_agent: navigator.userAgent,
            ...data
        };
        
        this.events.push(eventData);
        this.sendToServer(eventData);
    }
    
    // Eventos espec��ficos del sistema
    trackVoiceCommand(command, success, amount) {
        this.trackEvent('voice_command', {
            command: command,
            success: success,
            amount: amount,
            device_type: /iPhone|iPad|iPod|Android/i.test(navigator.userAgent) ? 'mobile' : 'desktop'
        });
    }
    
    trackOfflineUsage(action, data_synced = 0) {
        this.trackEvent('offline_usage', {
            action: action,
            data_synced: data_synced,
            offline_duration: this.getOfflineDuration()
        });
    }
    
    trackPWAInstall() {
        this.trackEvent('pwa_install', {
            platform: navigator.platform,
            install_source: 'browser_prompt'
        });
    }
    
    // Env��o as��ncrono al servidor
    async sendToServer(eventData) {
        try {
            if (navigator.onLine) {
                await fetch('/api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'track_analytics',
                        event_data: eventData
                    })
                });
            } else {
                // Guardar para sync posterior
                this.storeOfflineEvent(eventData);
            }
        } catch (error) {
            console.log('Analytics tracking failed:', error);
        }
    }
}

// Inicializar analytics PWA
const pwaAnalytics = new PWAAnalytics();

// Uso en el sistema
pwaAnalytics.trackVoiceCommand('ingreso 150000 t��tulo salario', true, 150000);
pwaAnalytics.trackOfflineUsage('transaction_created', 1);
```

## ?? Sistema PWA Listo para Producci��n

**? Instalaci��n completamente automatizada con PWA**  
**? Reconocimiento de voz avanzado con IA optimizada**  
**? Dashboard PWA en tiempo real con m��tricas completas**  
**? Seguridad multi-nivel con auditor��a y compliance**  
**? Panel de administraci��n con gesti��n granular de usuarios**  
**? Compatible con TODOS los dispositivos y plataformas**  
**? Documentaci��n t��cnica completa y actualizada**  
**? API REST con endpoints optimizados para PWA**  
**? Sistema de backup y recuperaci��n con integridad**  
**? Monitoreo y diagn��sticos integrados con analytics**  
**? Funcionalidad offline completa con sincronizaci��n**  
**? Service Worker optimizado para m��ximo rendimiento**  

---

## ?? Informaci��n del Proyecto

**Nombre**: Sistema de Registro Financiero PWA  
**Versi��n**: 2.5 PWA (Agosto 2025)  
**Tecnolog��as**: PHP 8, MySQL 8, JavaScript ES6+, HTML5, CSS3, Service Worker, PWA APIs  
**Licencia**: MIT License  
**Compatibilidad PWA**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+  
**Certificaciones**: PWA Compliant, Offline First, Mobile Optimized

**Caracter��sticas ��nicas PWA**:
- ?? Reconocimiento de voz en espa?ol con 20+ patrones optimizados para PWA
- ?? Optimizaci��n espec��fica para Android, iOS y Desktop como PWA nativa
- ?? Sistema de autenticaci��n con protecci��n avanzada y sesiones por pesta?a
- ?? Dashboard PWA en tiempo real con actualizaci��n autom��tica y offline
- ?? Gesti��n multi-usuario con roles granulares y audit trail completo
- ??? Setup autom��tico con diagn��sticos PWA y configuraci��n inteligente
- ?? Funcionalidad offline completa con sincronizaci��n background autom��tica
- ?? Service Worker optimizado con cache inteligente y update autom��tico
- ?? Sistema de notificaciones PWA opcional para sync y updates
- ?? Analytics PWA integrados con m��tricas espec��ficas de rendimiento

**Estado**: ? **LISTO PARA PRODUCCI��N PWA**

**Casos de Uso Recomendados**:
- ?? **Peque?as y medianas empresas** - Control financiero completo offline/online
- ?? **Profesionales independientes** - Registro de ingresos/egresos con voz
- ?? **Gesti��n dom��stica** - Control de gastos familiares multiplataforma
- ?? **Trabajadores m��viles** - Registro desde cualquier lugar sin conexi��n
- ?? **Equipos distribuidos** - Colaboraci��n con roles y permisos granulares

**Soporte y Comunidad**:
- ?? Documentaci��n completa y actualizada constantemente
- ?? Diagn��sticos automatizados para resoluci��n r��pida de problemas
- ?? Optimizaci��n espec��fica por plataforma (Android/iOS/Desktop)
- ?? Gu��as detalladas de reconocimiento de voz por dispositivo
- ?? Mejores pr��cticas de seguridad para entornos de producci��n