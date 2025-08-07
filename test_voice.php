<?php
// test_voice.php - Test específico para reconocimiento de voz (PROTEGIDO)

// Verificar autenticación
require_once 'auth.php';
requerirAutenticacion();

require_once 'database.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Test Reconocimiento de Voz</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }";
echo ".test-case { margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }";
echo ".success { color: #4CAF50; background: #f0f8f0; }";
echo ".error { color: #f44336; background: #fdf0f0; }";
echo ".info { color: #2196F3; background: #f0f8ff; }";
echo "pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }";
echo ".highlight { background: #ffeb3b; padding: 2px 4px; border-radius: 3px; }";
echo "</style>";
echo "</head>";
echo "<body>";

$usuario = obtenerUsuarioActual();

echo "<div style='background: linear-gradient(135deg, #4CAF50, #45a049); color: white; padding: 15px; margin-bottom: 20px; border-radius: 10px;'>";
echo "<div style='float: right; font-size: 12px;'>👤 {$usuario['nombre']} ({$usuario['username']}) | <a href='auth.php?action=logout' style='color: white;'>🚪 Salir</a></div>";
echo "<h1>🎤 Test de Reconocimiento de Voz</h1>";
echo "<div style='clear: both;'></div>";
echo "</div>";

class VoiceTest {
    
    public function convertirTextoANumero($texto) {
        // Si ya es un número, devolverlo
        if (is_numeric($texto)) {
            return floatval($texto);
        }
        
        // Limpiar y normalizar
        $texto = strtolower(trim($texto));
        
        // Primero, casos especiales directos con regex específicos
        $patronesDirectos = [
            // Números con "mil"
            '/^(?:un\s+)?mil$/' => 1000,
            '/^dos\s+mil$/' => 2000,
            '/^tres\s+mil$/' => 3000,
            '/^cuatro\s+mil$/' => 4000,
            '/^cinco\s+mil$/' => 5000,
            '/^seis\s+mil$/' => 6000,
            '/^siete\s+mil$/' => 7000,
            '/^ocho\s+mil$/' => 8000,
            '/^nueve\s+mil$/' => 9000,
            '/^diez\s+mil$/' => 10000,
            '/^quince\s+mil$/' => 15000,
            '/^veinte\s+mil$/' => 20000,
            '/^veinticinco\s+mil$/' => 25000,
            '/^treinta\s+mil$/' => 30000,
            '/^cuarenta\s+mil$/' => 40000,
            '/^cincuenta\s+mil$/' => 50000,
            '/^sesenta\s+mil$/' => 60000,
            '/^setenta\s+mil$/' => 70000,
            '/^ochenta\s+mil$/' => 80000,
            '/^noventa\s+mil$/' => 90000,
            '/^cien\s+mil$/' => 100000,
            '/^doscientos\s+mil$/' => 200000,
            '/^trescientos\s+mil$/' => 300000,
            '/^cuatrocientos\s+mil$/' => 400000,
            '/^quinientos\s+mil$/' => 500000,
            '/^seiscientos\s+mil$/' => 600000,
            '/^setecientos\s+mil$/' => 700000,
            '/^ochocientos\s+mil$/' => 800000,
            '/^novecientos\s+mil$/' => 900000,
            
            // Millones
            '/^(?:un\s+)?millón$/' => 1000000,
            '/^dos\s+millones$/' => 2000000,
            '/^tres\s+millones$/' => 3000000,
            '/^cuatro\s+millones$/' => 4000000,
            '/^cinco\s+millones$/' => 5000000,
        ];
        
        // Probar patrones directos primero
        foreach ($patronesDirectos as $patron => $valor) {
            if ($valor > 0 && preg_match($patron, $texto)) {
                return $valor;
            }
        }
        
        // Si contiene números separados por espacios (ej: "50 000"), juntarlos
        if (preg_match('/^\d+(\s+\d+)+$/', $texto)) {
            $numeroLimpio = str_replace(' ', '', $texto);
            if (is_numeric($numeroLimpio)) {
                return floatval($numeroLimpio);
            }
        }
        
        // Si hay un número directo al inicio, tomarlo
        if (preg_match('/^(\d+(?:\.\d+)?)/', $texto, $matches)) {
            return floatval($matches[1]);
        }
        
        // Procesar números compuestos como "ciento cincuenta mil"
        if (preg_match('/ciento\s+(\w+)\s+mil/', $texto, $matches)) {
            $diccionarioDecenas = [
                'diez' => 10, 'veinte' => 20, 'treinta' => 30, 'cuarenta' => 40,
                'cincuenta' => 50, 'sesenta' => 60, 'setenta' => 70, 'ochenta' => 80, 'noventa' => 90
            ];
            
            if (isset($diccionarioDecenas[$matches[1]])) {
                return (100 + $diccionarioDecenas[$matches[1]]) * 1000;
            }
        }
        
        // Diccionario básico
        $numeros = [
            'cero' => 0, 'un' => 1, 'uno' => 1, 'dos' => 2, 'tres' => 3, 'cuatro' => 4, 'cinco' => 5,
            'seis' => 6, 'siete' => 7, 'ocho' => 8, 'nueve' => 9, 'diez' => 10,
            'once' => 11, 'doce' => 12, 'trece' => 13, 'catorce' => 14, 'quince' => 15,
            'dieciséis' => 16, 'diecisiete' => 17, 'dieciocho' => 18, 'diecinueve' => 19,
            'veinte' => 20, 'veintiuno' => 21, 'veintidós' => 22, 'veintitrés' => 23,
            'veinticuatro' => 24, 'veinticinco' => 25, 'veintiséis' => 26, 'veintisiete' => 27,
            'veintiocho' => 28, 'veintinueve' => 29, 'treinta' => 30, 'cuarenta' => 40, 
            'cincuenta' => 50, 'sesenta' => 60, 'setenta' => 70, 'ochenta' => 80, 'noventa' => 90,
            'cien' => 100, 'ciento' => 100, 'doscientos' => 200, 'trescientos' => 300,
            'cuatrocientos' => 400, 'quinientos' => 500, 'seiscientos' => 600,
            'setecientos' => 700, 'ochocientos' => 800, 'novecientos' => 900,
            'mil' => 1000, 'millón' => 1000000, 'millones' => 1000000
        ];
        
        // Como último recurso, usar el diccionario palabra por palabra
        $palabras = preg_split('/\s+/', $texto);
        $total = 0;
        $numero_actual = 0;
        
        foreach ($palabras as $palabra) {
            $palabra = preg_replace('/[^a-záéíóúñü\d]/', '', $palabra);
            
            if (isset($numeros[$palabra])) {
                $valor = $numeros[$palabra];
                
                if ($valor == 1000000) {
                    $total += ($numero_actual == 0 ? 1 : $numero_actual) * $valor;
                    $numero_actual = 0;
                } elseif ($valor == 1000) {
                    $total += ($numero_actual == 0 ? 1 : $numero_actual) * $valor;
                    $numero_actual = 0;
                } elseif ($valor >= 100 && $valor < 1000) {
                    $numero_actual += $valor;
                } else {
                    $numero_actual += $valor;
                }
            } elseif (is_numeric($palabra)) {
                $numero_actual += floatval($palabra);
            }
        }
        
        return $total + $numero_actual;
    }
    
    public function testearComando($texto) {
        echo "<div class='test-case'>";
        echo "<h3>🎤 Texto de entrada: \"$texto\"</h3>";
        
        // Normalizar el texto
        $textoNormalizado = strtolower(trim($texto));
        echo "<p><strong>Texto normalizado:</strong> \"$textoNormalizado\"</p>";
        
        // Limpiar números con espacios
        $textoLimpio = preg_replace('/(\d+)\s+(\d+)/', '$1$2', $textoNormalizado);
        echo "<p><strong>Después de limpiar espacios en números:</strong> \"$textoLimpio\"</p>";
        
        // Probar patrones simplificados
        $patrones = [
            'ingreso' => '/(?:crear|nuevo|agregar)?\s*(?:un\s+)?(?:nuevo\s+)?ingreso\s+(?:de\s+)([^,]+?)(?:\s*pesos?)?\s*(?:,?\s*(?:con\s+)?(?:el\s+)?(?:título|titulo)\s+(.+?))?$/i',
            'egreso' => '/(?:crear|nuevo|agregar)?\s*(?:un\s+)?(?:nuevo\s+)?egreso\s+(?:de\s+)([^,]+?)(?:\s*pesos?)?\s*(?:,?\s*(?:con\s+)?(?:el\s+)?(?:título|titulo)\s+(.+?))?$/i'
        ];
        
        $encontrado = false;
        foreach ($patrones as $tipo => $patron) {
            if (preg_match($patron, $textoLimpio, $matches)) {
                $encontrado = true;
                echo "<div class='success'>";
                echo "<p><strong>✅ Patrón '$tipo' coincide!</strong></p>";
                echo "<p><strong>Matches encontrados:</strong></p>";
                echo "<pre>" . print_r($matches, true) . "</pre>";
                
                $montoTexto = trim($matches[1]);
                $titulo = isset($matches[2]) ? trim($matches[2]) : ucfirst($tipo) . ' automático';
                
                echo "<p><strong>Monto extraído:</strong> \"<span class='highlight'>$montoTexto</span>\"</p>";
                echo "<p><strong>Título extraído:</strong> \"<span class='highlight'>$titulo</span>\"</p>";
                
                // Convertir a número
                $monto = $this->convertirTextoANumero($montoTexto);
                echo "<p><strong>Monto convertido:</strong> <span class='highlight'>$monto</span></p>";
                
                if ($monto > 0) {
                    $montoFormateado = number_format($monto, 0, ',', '.');
                    echo "<p><strong>✅ Resultado final:</strong> $tipo de $$$montoFormateado con título '$titulo'</p>";
                } else {
                    echo "<p><strong>❌ Error:</strong> No se pudo convertir '$montoTexto' a número</p>";
                    
                    // Debug adicional para entender qué pasó
                    echo "<p><strong>🔍 Debug adicional:</strong></p>";
                    echo "<ul>";
                    echo "<li>Texto original del monto: '$montoTexto'</li>";
                    echo "<li>Longitud: " . strlen($montoTexto) . " caracteres</li>";
                    echo "<li>Es numérico: " . (is_numeric($montoTexto) ? 'Sí' : 'No') . "</li>";
                    if (!empty($montoTexto)) {
                        echo "<li>Primera palabra: '" . explode(' ', $montoTexto)[0] . "'</li>";
                        echo "<li>Última palabra: '" . explode(' ', $montoTexto)[count(explode(' ', $montoTexto))-1] . "'</li>";
                    }
                    echo "</ul>";
                }
                echo "</div>";
                break;
            }
        }
        
        if (!$encontrado) {
            echo "<div class='error'>";
            echo "<p><strong>❌ Ningún patrón coincide</strong></p>";
            echo "<p><strong>Debug de patrones:</strong></p>";
            foreach ($patrones as $tipo => $patron) {
                echo "<p><small><strong>$tipo:</strong> $patron</small></p>";
            }
            echo "</div>";
        }
        
        echo "</div>";
    }
}

$tester = new VoiceTest();

// Casos de prueba completos
$casos = [
    "egreso de 50 000 pesos con título compras",
    "egreso de 50000 pesos con título compras", 
    "ingreso de cincuenta mil título salario",
    "nuevo egreso de 25000 título transporte",
    "crear ingreso de 100 000 con título freelance",
    "egreso de quinientos mil título casa",
    "ingreso de doscientos mil título bonus",
    "egreso de setenta mil título reparaciones",
    "crear ingreso de un millón título lotería",
    "ingreso de 250000 título ventas",
    "egreso de ciento cincuenta mil título equipos"
];

echo "<h2>🧪 Casos de Prueba</h2>";
echo "<p>Probando el sistema de reconocimiento de voz con diferentes tipos de entrada:</p>";

foreach ($casos as $i => $caso) {
    echo "<hr>";
    echo "<h4>Caso " . ($i + 1) . " de " . count($casos) . "</h4>";
    $tester->testearComando($caso);
}

echo "<hr>";
echo "<h2>📊 Resumen</h2>";
echo "<p>Este test verifica que el sistema pueda procesar correctamente:</p>";
echo "<ul>";
echo "<li>✅ Números con espacios (50 000)</li>";
echo "<li>✅ Números directos (50000)</li>";
echo "<li>✅ Números en palabras (cincuenta mil)</li>";
echo "<li>✅ Centenas + mil (quinientos mil)</li>";
echo "<li>✅ Millones (un millón)</li>";
echo "<li>✅ Números compuestos (ciento cincuenta mil)</li>";
echo "</ul>";

echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>💡 Comandos Recomendados para Dictado</h3>";
echo "<p><strong>Mejor resultado:</strong></p>";
echo "<ul>";
echo "<li>\"Ingreso de cincuenta mil título salario\"</li>";
echo "<li>\"Egreso de quinientos mil título casa\"</li>";
echo "<li>\"Ingreso de doscientos mil título bonus\"</li>";
echo "</ul>";
echo "<p><strong>También funciona:</strong></p>";
echo "<ul>";
echo "<li>\"Egreso de 50000 título compras\"</li>";
echo "<li>\"Ingreso de 250 000 título ventas\"</li>";
echo "</ul>";
echo "</div>";

echo "<p><small>Test de Reconocimiento de Voz v2.0 - Actualizado con patrones simplificados</small></p>";
echo "</body>";
echo "</html>";
?>