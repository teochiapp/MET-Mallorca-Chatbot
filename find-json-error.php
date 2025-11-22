<?php
$json = file_get_contents('precios_locations_data.json');

// Intentar decodificar
$data = json_decode($json, true);

if ($data === null) {
    echo "JSON tiene error de sintaxis\n\n";
    
    // Buscar el error línea por línea
    $lines = explode("\n", $json);
    $partial = '';
    
    foreach ($lines as $i => $line) {
        $partial .= $line . "\n";
        $test = json_decode($partial, true);
        
        if ($test === null && json_last_error() !== JSON_ERROR_NONE) {
            echo "Error encontrado cerca de la línea " . ($i + 1) . "\n";
            echo "Línea: " . trim($line) . "\n";
            echo "Error: " . json_last_error_msg() . "\n";
            
            // Mostrar contexto
            echo "\nContexto (5 líneas antes):\n";
            for ($j = max(0, $i - 5); $j <= $i; $j++) {
                echo ($j + 1) . ": " . $lines[$j] . "\n";
            }
            break;
        }
    }
} else {
    echo "JSON válido: " . count($data) . " ubicaciones\n";
}
