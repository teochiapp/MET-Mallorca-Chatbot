<?php
// Test de carga del JSON de locations y del MET_Pricing_Engine

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ABSPATH', true);
define('MET_CHATBOT_PLUGIN_DIR', __DIR__ . '/');

// Stub básico de apply_filters para poder usar MET_Pricing_Engine fuera de WordPress
if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value) {
        return $value;
    }
}

header('Content-Type: text/plain; charset=utf-8');

echo "== MET Chatbot - Test JSON Locations ==\n\n";

// 1) Comprobar archivo JSON directo
$json_file = MET_CHATBOT_PLUGIN_DIR . 'precios_locations_data.json';

echo "Ruta JSON:  $json_file\n";
echo 'Existe:      ' . (file_exists($json_file) ? 'SÍ' : 'NO') . "\n";

if (!file_exists($json_file)) {
    echo "\nERROR: El archivo JSON no existe en la ruta indicada.\n";
    exit;
}

$contents = file_get_contents($json_file);
if ($contents === false) {
    echo "\nERROR: file_get_contents() ha fallado al leer el JSON.\n";
    exit;
}

echo 'Tamaño JSON: ' . strlen($contents) . " bytes\n";

$data = json_decode($contents, true);
if ($data === null) {
    echo "\nERROR: json_decode() ha fallado.\n";
    echo 'json_last_error_msg(): ' . json_last_error_msg() . "\n";
} else {
    echo 'Entradas en JSON (top-level keys): ' . count($data) . "\n";
}

// 2) Probar MET_Pricing_Engine
require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-pricing-engine.php';

$engine = new MET_Pricing_Engine();
$locations = $engine->get_all_locations();

echo "\n== MET_Pricing_Engine::get_all_locations() ==\n";
echo 'Total locations: ' . count($locations) . "\n";

if (!empty($locations)) {
    echo "Primeras 10 locations:\n";
    foreach (array_slice($locations, 0, 10) as $loc) {
        echo ' - ' . $loc . "\n";
    }
} else {
    echo "No se ha cargado ninguna location desde el motor de precios.\n";
}

echo "\n== Fin del test ==\n";
