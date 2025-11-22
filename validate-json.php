<?php
$json = file_get_contents('precios_locations_data.json');
$data = json_decode($json, true);

if ($data === null) {
    echo 'JSON Error: ' . json_last_error_msg() . PHP_EOL;
    echo 'Error code: ' . json_last_error() . PHP_EOL;
} else {
    echo 'JSON OK: ' . count($data) . ' locations loaded' . PHP_EOL;
}
