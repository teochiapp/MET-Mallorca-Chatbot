<?php
// Leer el JSON con BOM
$json = file_get_contents('precios_locations_data.json');

// Remover BOM UTF-8 (EF BB BF)
$json = preg_replace('/^\xEF\xBB\xBF/', '', $json);

// Guardar backup
copy('precios_locations_data.json', 'precios_locations_data.json.backup');

// Guardar JSON limpio
file_put_contents('precios_locations_data.json', $json);

// Validar
$data = json_decode($json, true);
if ($data === null) {
    echo "ERROR: Aún hay problemas con el JSON\n";
    echo json_last_error_msg() . "\n";
} else {
    echo "✓ JSON corregido exitosamente\n";
    echo "✓ " . count($data) . " ubicaciones cargadas\n";
    echo "✓ Backup guardado en: precios_locations_data.json.backup\n";
}
