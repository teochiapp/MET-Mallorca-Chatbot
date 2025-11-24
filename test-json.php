<?php
$json_file = 'precios_locations_data.json';
$json_contents = file_get_contents($json_file);

echo "Tamaño del archivo: " . strlen($json_contents) . " bytes\n";
echo "Primeros 100 caracteres: " . substr($json_contents, 0, 100) . "\n";

$data = json_decode($json_contents, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error JSON: " . json_last_error_msg() . " (Código: " . json_last_error() . ")\n";
} else {
    echo "JSON válido!\n";
    echo "Número de ubicaciones: " . count($data) . "\n";
    echo "Primeras 5 ubicaciones: " . implode(', ', array_slice(array_keys($data), 0, 5)) . "\n";
}
?>
