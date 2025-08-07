<?php
// api.php - API COMPLETA CORREGIDA para procesamiento de voz y formateo de moneda

// Iniciar buffer de salida antes que cualquier cosa
ob_start();

// Configurar headers de inmediato
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Configurar manejo de errores
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Verificar autenticación
try {
    require_once 'auth.php';
    requerirAutenticacion();
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Error de autenticación',
        'error_code' => 'AUTH_ERROR'
    ]);
    exit;
}

try {
    require_once 'database.php';
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Error de configuración del servidor',
        'error_code' => 'DB_CONFIG_ERROR'
    ]);
    exit;
}

class FinancialAPI {
    private $conn;
    private $usuario_id;
    
    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->getConnection();
            
            if (!$this->conn) {
                throw new Exception("No se pudo conectar a la base de datos");
            }
            
            // Crear tablas si no existen
            $database->createTables();
            $database->createDefaultUsers();
            
            // Obtener ID del usuario actual
            $usuario = obtenerUsuarioActual();
            $this->usuario_id = $usuario ? $usuario['id'] : 1;
            
        } catch (Exception $e) {
            error_log("Error en constructor FinancialAPI: " . $e->getMessage());
            throw new Exception("Error de inicialización de la API");
        }
    }
    
    public function crearTransaccion($datos) {
        $sql = "INSERT INTO transacciones (usuario_id, tipo, monto, titulo, descripcion, metodo_creacion) 
                VALUES (:usuario_id, :tipo, :monto, :titulo, :descripcion, :metodo_creacion)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':usuario_id', $this->usuario_id);
            $stmt->bindParam(':tipo', $datos['tipo']);
            $stmt->bindParam(':monto', $datos['monto']);
            $stmt->bindParam(':titulo', $datos['titulo']);
            $stmt->bindParam(':descripcion', $datos['descripcion']);
            $stmt->bindParam(':metodo_creacion', $datos['metodo_creacion']);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Transacción creada exitosamente',
                    'id' => $this->conn->lastInsertId(),
                    'monto_formateado' => number_format($datos['monto'], 2, ',', '.')
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Error al ejecutar la consulta'
            ];
            
        } catch(PDOException $e) {
            error_log("Error SQL en crearTransaccion: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al crear transacción: ' . $e->getMessage()
            ];
        }
    }
    
    public function obtenerTransacciones($limit = 50) {
        $sql = "SELECT * FROM transacciones WHERE usuario_id = :usuario_id ORDER BY fecha_creacion DESC LIMIT :limit";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usuario_id', $this->usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $transacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $transacciones
            ];
        } catch(PDOException $e) {
            error_log("Error SQL en obtenerTransacciones: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener transacciones: ' . $e->getMessage()
            ];
        }
    }
    
    public function obtenerResumen() {
        $sql = "SELECT 
                    SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END) as total_ingresos,
                    SUM(CASE WHEN tipo = 'egreso' THEN monto ELSE 0 END) as total_egresos,
                    COUNT(*) as total_transacciones
                FROM transacciones WHERE usuario_id = :usuario_id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usuario_id', $this->usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
            $balance = ($resumen['total_ingresos'] ?? 0) - ($resumen['total_egresos'] ?? 0);
            
            return [
                'success' => true,
                'data' => [
                    'total_ingresos' => $resumen['total_ingresos'] ?? 0,
                    'total_egresos' => $resumen['total_egresos'] ?? 0,
                    'balance' => $balance,
                    'total_transacciones' => $resumen['total_transacciones'] ?? 0
                ]
            ];
        } catch(PDOException $e) {
            error_log("Error SQL en obtenerResumen: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener resumen: ' . $e->getMessage()
            ];
        }
    }
    
    public function eliminarTransaccion($id) {
        $sql = "DELETE FROM transacciones WHERE id = :id AND usuario_id = :usuario_id";
        
        try {
            // Primero verificar que la transacción existe y pertenece al usuario
            $checkSql = "SELECT id, titulo, monto, tipo FROM transacciones WHERE id = :id AND usuario_id = :usuario_id";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->bindParam(':usuario_id', $this->usuario_id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            $transaccion = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$transaccion) {
                return [
                    'success' => false,
                    'message' => 'La transacción no existe o no tienes permisos para eliminarla'
                ];
            }
            
            // Eliminar la transacción
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':usuario_id', $this->usuario_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => "Transacción eliminada: {$transaccion['titulo']} ({$transaccion['tipo']} de $" . number_format($transaccion['monto'], 0, ',', '.') . ")",
                    'transaccion_eliminada' => $transaccion
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar la transacción'
                ];
            }
            
        } catch(PDOException $e) {
            error_log("Error SQL en eliminarTransaccion: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar transacción: ' . $e->getMessage()
            ];
        }
    }
    
    public function procesarComandoVoz($texto) {
        // Normalizar el texto de forma más agresiva
        $textoOriginal = $texto;
        $texto = $this->normalizarTextoVoz($texto);
        
        // Log para debug
        error_log("=== PROCESAMIENTO DE VOZ V4 ===");
        error_log("Original: '$textoOriginal'");
        error_log("Normalizado: '$texto'");
        
        // Patrones SIMPLIFICADOS y MÁS EFECTIVOS
        $patrones = [
            // Patrones para INGRESO
            'ingreso' => [
                // Patrón principal - captura todo después de "ingreso"
                '/^ingreso\s+(.+?)(?:\s+titulo\s+(.+?))?$/i',
                '/^(?:crear|nuevo)\s+ingreso\s+(.+?)(?:\s+titulo\s+(.+?))?$/i',
            ],
            
            // Patrones para EGRESO
            'egreso' => [
                // Patrón principal - captura todo después de "egreso"
                '/^egreso\s+(.+?)(?:\s+titulo\s+(.+?))?$/i',
                '/^(?:crear|nuevo)\s+egreso\s+(.+?)(?:\s+titulo\s+(.+?))?$/i',
            ]
        ];
        
        // Probar todos los patrones
        foreach ($patrones as $tipo => $listaPatrones) {
            foreach ($listaPatrones as $patron) {
                if (preg_match($patron, $texto, $matches)) {
                    error_log("✅ Patrón coincidente para $tipo: $patron");
                    error_log("Matches: " . print_r($matches, true));
                    
                    $montoTexto = isset($matches[1]) ? trim($matches[1]) : '';
                    $titulo = isset($matches[2]) && !empty(trim($matches[2])) ? trim($matches[2]) : ucfirst($tipo) . ' por voz';
                    
                    error_log("Monto extraído RAW: '$montoTexto'");
                    error_log("Título extraído: '$titulo'");
                    
                    if (!empty($montoTexto)) {
                        $monto = $this->convertirTextoANumeroMejorado($montoTexto);
                        
                        error_log("💰 Monto convertido FINAL: $monto");
                        
                        if ($monto > 0) {
                            $datos = [
                                'tipo' => $tipo,
                                'monto' => $monto,
                                'titulo' => $titulo,
                                'descripcion' => 'Creado por voz: "' . $textoOriginal . '"',
                                'metodo_creacion' => 'audio'
                            ];
                            
                            $resultado = $this->crearTransaccion($datos);
                            error_log("Resultado creación: " . print_r($resultado, true));
                            
                            return $resultado;
                        }
                    }
                }
            }
        }
        
        return [
            'success' => false,
            'message' => 'No se pudo procesar el comando. Texto reconocido: "' . $textoOriginal . '"',
            'debug' => [
                'texto_original' => $textoOriginal,
                'texto_normalizado' => $texto
            ]
        ];
    }

    private function normalizarTextoVoz($texto) {
        // Limpiar y normalizar texto
        $texto = strtolower(trim($texto));
        
        // CORRECCIÓN MÓVIL 1: Convertir números con comas múltiples a espacios
        // Móvil: "2,500,000" → "2 500 000"
        $texto = preg_replace('/(\d+),(\d+),(\d+)/', '$1 $2 $3', $texto);
        
        // CORRECCIÓN MÓVIL 2: Convertir números con una coma a espacios  
        // Móvil: "200,000" → "200 000"
        $texto = preg_replace('/(\d+),(\d+)/', '$1 $2', $texto);
        
        // Correcciones específicas ANTES de procesar
        $correcciones = [
            // Fix crítico para títulos (móviles cambian título→títulos)
            'títulos' => 'titulo',
            'título' => 'titulo',
            
            // Normalizaciones de millones
            'millón' => 'millon',
            'millones' => 'millones',
            
            // Otras correcciones básicas
            'ingreso de' => 'ingreso',
            'egreso de' => 'egreso',
        ];
        
        foreach ($correcciones as $buscar => $reemplazar) {
            $texto = str_replace($buscar, $reemplazar, $texto);
        }
        
        // Normalizar espacios múltiples
        $texto = preg_replace('/\s+/', ' ', $texto);
        
        error_log("📝 Texto normalizado (fix móvil completo): '$texto'");
        return trim($texto);
    }
    
    private function convertirTextoANumeroMejorado($texto) {
        error_log("=== CONVERSIÓN MEJORADA V3 ===");
        error_log("🔢 Input: '$texto'");
        
        // Limpiar texto
        $texto = strtolower(trim($texto));
        
        // PRIORIDAD 1: Números grandes con espacios (post-normalización móvil)
        $numerosGrandes = [
            // Millones (después de normalización desde "2,500,000")
            '2 500 000' => 2500000,
            '1 500 000' => 1500000,
            '3 500 000' => 3500000,
            '1 200 000' => 1200000,
            '2 000 000' => 2000000,
            '3 000 000' => 3000000,
            '5 000 000' => 5000000,
            
            // Miles (después de normalización desde "200,000")
            '500 000' => 500000,
            '200 000' => 200000,
            '150 000' => 150000,
            '250 000' => 250000,
            '300 000' => 300000,
            '800 000' => 800000,
            '120 000' => 120000,
            
            // Millones compuestos (desde "un millón 200,000")
            'un millon 200 000' => 1200000,
            'un millon 500 000' => 1500000,
            'un millon 800 000' => 1800000,
            'dos millones 500 000' => 2500000,
            'un millon 300 000' => 1300000,
            'un millon 150 000' => 1150000,
        ];
        
        foreach ($numerosGrandes as $patron => $valor) {
            if (strpos($texto, $patron) !== false) {
                error_log("🎯 Número grande encontrado: '$patron' -> $valor");
                return $valor;
            }
        }
        
        // PRIORIDAD 2: Millones decimales
        if (preg_match('/(\d+(?:\.\d+)?)\s*millones/', $texto, $matches)) {
            $numero = floatval($matches[1]);
            $resultado = $numero * 1000000;
            error_log("🚀 Decimal millones: {$matches[1]} -> $resultado");
            return $resultado;
        }
        
        // PRIORIDAD 3: Patrón de 3 números con espacios (X XXX XXX)
        if (preg_match('/(\d+)\s+(\d+)\s+(\d+)/', $texto, $matches)) {
            $num1 = intval($matches[1]);
            $num2 = intval($matches[2]);
            $num3 = intval($matches[3]);
            
            // Formato millones: 2 500 000
            if ($num2 >= 100 && $num3 == 0) {
                $resultado = ($num1 * 1000000) + ($num2 * 1000);
                error_log("💎 Millones formato: $num1 $num2 $num3 -> $resultado");
                return $resultado;
            }
            // Formato millones simples: 2 000 000  
            else if ($num2 == 0 && $num3 == 0 && $num1 <= 10) {
                $resultado = $num1 * 1000000;
                error_log("💎 Millones simples: $num1 000 000 -> $resultado");
                return $resultado;
            }
            // Formato general
            else {
                $resultado = ($num1 * 100000) + ($num2 * 1000) + $num3;
                error_log("💎 Formato general: $num1 $num2 $num3 -> $resultado");
                return $resultado;
            }
        }
        
        // PRIORIDAD 4: Patrón de 2 números con espacios (XXX XXX)
        if (preg_match('/(\d+)\s+(\d+)/', $texto, $matches)) {
            $num1 = intval($matches[1]);
            $num2 = intval($matches[2]);
            
            // Si el segundo es 000, es formato de miles
            if ($num2 == 0 && $num1 >= 10) {
                $resultado = $num1 * 1000;
                error_log("💰 Miles: $num1 000 -> $resultado");
                return $resultado;
            }
            // Si el primer número es grande, combinar como miles
            else if ($num1 >= 100) {
                $resultado = ($num1 * 1000) + $num2;
                error_log("💰 Miles combinado: $num1 $num2 -> $resultado");
                return $resultado;
            }
            // Si ambos son pequeños, concatenar
            else {
                $resultado = intval($matches[1] . $matches[2]);
                error_log("💰 Concatenado: {$matches[1]}{$matches[2]} -> $resultado");
                return $resultado;
            }
        }
        
        // PRIORIDAD 5: Millones compuestos con palabras
        if (preg_match('/(un|dos|tres)\s+(millon|millones)\s+(\d+)\s+(\d+)/', $texto, $matches)) {
            $multiplicador = ($matches[1] == 'un') ? 1 : 
                           (($matches[1] == 'dos') ? 2 : 3);
            $miles = intval($matches[3]);
            $unidades = intval($matches[4]);
            
            $resultado = ($multiplicador * 1000000) + ($miles * 1000) + $unidades;
            error_log("🚀 Millones con palabras: {$matches[1]} {$matches[2]} {$matches[3]} {$matches[4]} -> $resultado");
            return $resultado;
        }
        
        // PRIORIDAD 6: Números con K
        if (preg_match('/(\d+)\s*k/', $texto, $matches)) {
            $resultado = intval($matches[1]) * 1000;
            error_log("⚡ Con K: {$matches[1]}k -> $resultado");
            return $resultado;
        }
        
        // PRIORIDAD 7: Número simple
        if (preg_match('/(\d+)/', $texto, $matches)) {
            $resultado = intval($matches[1]);
            error_log("🔢 Número simple: $resultado");
            return $resultado;
        }
        
        error_log("❌ No se pudo convertir: '$texto'");
        return 0;
    }
}

// Manejar solicitudes
try {
    $api = new FinancialAPI();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Limpiar output buffer antes de procesar
    ob_end_clean();
    ob_start();
    
    switch ($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['action'])) {
                throw new Exception('Datos de entrada inválidos');
            }
            
            switch ($input['action']) {
                case 'crear_transaccion':
                    if (!isset($input['datos'])) {
                        throw new Exception('Datos de transacción requeridos');
                    }
                    $result = $api->crearTransaccion($input['datos']);
                    break;
                    
                case 'procesar_voz':
                    if (!isset($input['texto'])) {
                        throw new Exception('Texto de voz requerido');
                    }
                    $result = $api->procesarComandoVoz($input['texto']);
                    break;
                    
                case 'eliminar_transaccion':
                    if (!isset($input['id']) || !is_numeric($input['id'])) {
                        throw new Exception('ID de transacción requerido y debe ser numérico');
                    }
                    $result = $api->eliminarTransaccion(intval($input['id']));
                    break;
                
                default:
                    throw new Exception('Acción no válida: ' . $input['action']);
            }
            break;
            
        case 'GET':
            if (!isset($_GET['action'])) {
                throw new Exception('Acción requerida');
            }
            
            switch ($_GET['action']) {
                case 'obtener_transacciones':
                    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
                    if ($limit < 1 || $limit > 1000) {
                        $limit = 50;
                    }
                    $result = $api->obtenerTransacciones($limit);
                    break;
                    
                case 'obtener_resumen':
                    $result = $api->obtenerResumen();
                    break;
                    
                case 'ping':
                    $result = [
                        'success' => true,
                        'message' => 'Servidor funcionando correctamente',
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    break;
                    
                default:
                    throw new Exception('Acción no válida: ' . $_GET['action']);
            }
            break;
            
        default:
            throw new Exception('Método HTTP no permitido: ' . $method);
    }
    
    // Limpiar cualquier output no deseado
    ob_end_clean();
    
    // Enviar respuesta JSON
    echo json_encode($result);
    
} catch (Exception $e) {
    // Limpiar output buffer en caso de error
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    error_log("Error en API: " . $e->getMessage());
    
    $errorResponse = [
        'success' => false, 
        'message' => $e->getMessage(),
        'error_code' => 'API_ERROR',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($errorResponse);
}

// Asegurar que no hay output adicional
exit;
?>