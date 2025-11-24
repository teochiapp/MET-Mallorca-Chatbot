<?php
/**
 * Motor de cálculo de precios para reservas
 * Sistema configurable y extensible para calcular tarifas
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Pricing_Engine {
    
    /**
     * Precios por ubicación desde CSV
     */
    private $location_prices = array();
    
    /**
     * Configuración de precios base
     */
    private $config = array(
        // Precios base por distancia (en km)
        'distance_rates' => array(
            '0-10' => 25,      // Hasta 10km
            '10-20' => 35,     // 10-20km
            '20-30' => 45,     // 20-30km
            '30-50' => 60,     // 30-50km
            '50-100' => 90,    // 50-100km
            '100+' => 120      // Más de 100km
        ),
        
        // Suplementos por tipo de vehículo
        'vehicle_supplements' => array(
            'standard' => 0,        // Vehículo estándar (1-4 pax)
            'van' => 15,            // Van (5-8 pax)
            'minibus' => 30,        // Minibus (9-16 pax)
            'bus' => 50             // Bus (17-20 pax)
        ),
        
        // Suplemento por horario nocturno (22:00 - 06:00)
        'night_supplement' => 10,
        
        // Suplemento por pasajero extra (después del límite del vehículo)
        'extra_passenger_rate' => 5,
        
        // Suplementos por extras
        'extras' => array(
            'child_seat' => 5,
            'booster_seat' => 5,
            'pet_small' => 10,
            'pet_large' => 15,
            'luggage_extra' => 5,
            'meet_greet' => 10
        ),
        
        // Distancias aproximadas desde aeropuerto (PMI) en km
        'airport_distances' => array(
            'Palma' => 10,
            'Palma Nova' => 20,
            'Magalluf' => 22,
            'Magaluf' => 22,
            'Santa Ponsa' => 25,
            'Paguera' => 28,
            'Andratx' => 35,
            'Port Andratx' => 38,
            'Alcudia' => 60,
            'Puerto de Alcudia' => 62,
            'Port de Alcudia' => 62,
            'Puerto Pollensa' => 65,
            'Puerto Pollesa' => 65,
            'Port de Pollensa' => 65,
            'Pollensa' => 63,
            'Cala Millor' => 70,
            'Cala Ratjada' => 80,
            'Cala D\'or' => 65,
            'Cala Dor' => 65,
            'Porto Cristo' => 65,
            'Manacor' => 55,
            'Inca' => 35,
            'Sa Pobla' => 50,
            'Soller' => 35,
            'Port de Soller' => 40,
            'Deia' => 32,
            'Valldemossa' => 25,
            'Cala Bona' => 72,
            'Son Servera' => 68,
            'Capdepera' => 78
        )
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        // Permitir filtrar la configuración desde el tema o plugins
        $this->config = apply_filters('met_pricing_config', $this->config);
        
        // Cargar precios desde JSON
        $this->load_location_prices();
    }
    
    /**
     * Cargar precios de ubicaciones desde JSON
     */
    private function load_location_prices() {
        $json_file = MET_CHATBOT_PLUGIN_DIR . 'precios_locations_data.json';
        
        // Inicializar location_prices como array vacío
        $this->location_prices = array();
        
        // Verificar si el archivo existe
        if (!file_exists($json_file)) {
            error_log('MET DEBUG: El archivo JSON no existe: ' . $json_file);
            return false;
        }
        
        // Verificar permisos del archivo
        if (!is_readable($json_file)) {
            error_log('MET DEBUG: El archivo JSON no es legible: ' . $json_file);
            return false;
        }
        
        // Leer el contenido del archivo
        $json_contents = file_get_contents($json_file);
        if ($json_contents === false) {
            error_log('MET DEBUG: No se pudo leer el contenido del archivo JSON');
            return false;
        }
        
        // Verificar si el JSON está vacío
        if (empty($json_contents)) {
            error_log('MET DEBUG: El archivo JSON está vacío');
            return false;
        }
        
        // Limpiar el contenido - eliminar BOM y caracteres extraños
        $json_contents = trim($json_contents);
        $json_contents = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json_contents);
        
        // Intentar decodificar el JSON con diferentes opciones
        $data = json_decode($json_contents, true);
        
        // Verificar errores de decodificación
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('MET DEBUG: Error JSON - Código: ' . json_last_error() . ', Mensaje: ' . json_last_error_msg());
            error_log('MET DEBUG: Primeros 200 caracteres del JSON: ' . substr($json_contents, 0, 200));
            
            // Intentar修复常见的JSON问题
            $json_contents = str_replace("'", '"', $json_contents); // Reemplazar comillas simples
            $json_contents = preg_replace('/,\s*}/', '}', $json_contents); // Eliminar comas finales
            $json_contents = preg_replace('/,\s*]/', ']', $json_contents); // Eliminar comas finales en arrays
            
            $data = json_decode($json_contents, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('MET DEBUG: Error JSON después de limpieza - Código: ' . json_last_error() . ', Mensaje: ' . json_last_error_msg());
                return false;
            }
        }
        
        // Verificar si los datos son un array
        if (!is_array($data)) {
            error_log('MET DEBUG: Los datos JSON no son un array, tipo: ' . gettype($data));
            return false;
        }
        
        // Verificar si hay datos
        if (empty($data)) {
            error_log('MET DEBUG: El array JSON está vacío');
            return false;
        }
        
        // Contador para depuración
        $processed = 0;
        $skipped = 0;
        $debug_info = array();
        
        // Procesar cada ubicación
        foreach ($data as $original_location => $prices) {
            $location = $this->sanitize_location_label($original_location);
            
            if ($location === '') {
                $skipped++;
                $debug_info[] = "Ubicación vacía después de sanitizar: $original_location";
                continue;
            }
            
            if (isset($this->location_prices[$location])) {
                $skipped++;
                $debug_info[] = "Ubicación duplicada: $location";
                continue;
            }
            
            if (!is_array($prices)) {
                $skipped++;
                $debug_info[] = "Precios no es un array para: $location";
                continue;
            }
            
            // Si llegamos aquí, la ubicación es válida
            $this->location_prices[$location] = $prices;
            $processed++;
            
            // Registrar las primeras 5 ubicaciones para depuración
            if ($processed <= 5) {
                $debug_info[] = "Ubicación procesada: $location";
            }
        }
        
        // Log de depuración
        error_log('MET DEBUG: Total procesadas: ' . $processed . ', Total omitidas: ' . $skipped);
        error_log('MET DEBUG: Total en location_prices: ' . count($this->location_prices));
        if (!empty($debug_info)) {
            error_log('MET DEBUG: ' . implode(' | ', $debug_info));
        }
        
        return true;
    }

    /**
     * Limpiar nombre de ubicación
     */
    private function sanitize_location_label($label) {
        $label = trim((string) $label);
        $label = preg_replace('/\s+/', ' ', $label);

        // Normalizar comas y separadores
        $label = preg_replace('/\s*,\s*/', ', ', $label);
        $label = preg_replace('/\s*\/\s*/', ' / ', $label);

        // Normalizar capitalización a título para coincidir con cálculos posteriores
        $label = ucwords(strtolower($label));

        return $label;
    }

    /**
     * Obtener todas las ubicaciones disponibles
     */
    public function get_all_locations() {
        // Depuración: Verificar si location_prices está vacío
        if (empty($this->location_prices)) {
            error_log('MET DEBUG: location_prices está vacío en get_all_locations()');
            error_log('MET DEBUG: Tipo de location_prices: ' . gettype($this->location_prices));
            error_log('MET DEBUG: Contenido de location_prices: ' . print_r($this->location_prices, true));
        } else {
            error_log('MET DEBUG: location_prices tiene ' . count($this->location_prices) . ' elementos');
            error_log('MET DEBUG: Primeras 5 claves: ' . json_encode(array_slice(array_keys($this->location_prices), 0, 5)));
        }
        
        $locations = array_keys($this->location_prices);
        error_log('MET DEBUG: Número de ubicaciones encontradas: ' . count($locations));
        
        if (empty($locations)) {
            error_log('MET DEBUG: No se encontraron claves en location_prices');
        } else {
            error_log('MET DEBUG: Ejemplo de ubicaciones: ' . json_encode(array_slice($locations, 0, 5)));
        }

        sort($locations, SORT_NATURAL | SORT_FLAG_CASE);
        
        // Depuración adicional para verificar el resultado final
        error_log('MET DEBUG: get_all_locations() devolviendo ' . count($locations) . ' ubicaciones');
        if (!empty($locations)) {
            error_log('MET DEBUG: Ejemplo de ubicaciones ordenadas: ' . json_encode(array_slice($locations, 0, 5)));
        }

        return $locations;
    }
    
    /**
     * Buscar ubicaciones que coincidan con el término de búsqueda
     */
    public function search_locations($search_term) {
        $search_term = strtolower(trim($search_term));
        $results = array();
        
        foreach ($this->location_prices as $location => $prices) {
            if (stripos($location, $search_term) !== false) {
                $results[] = $location;
            }
        }
        
        return $results;
    }
    
    /**
     * Obtener precio para una ubicación y cantidad de pasajeros
     */
    public function get_location_price($location, $passengers) {
        // Intentar coincidencia exacta con el formato almacenado
        $sanitized_location = $this->sanitize_location_label($location);

        if (isset($this->location_prices[$sanitized_location])) {
            return $this->get_price_by_passengers($this->location_prices[$sanitized_location], $passengers);
        }

        // Normalizar nombre de ubicación para tolerar variaciones menores
        $normalized_location = $this->normalize_location_name($location);

        if (isset($this->location_prices[$normalized_location])) {
            return $this->get_price_by_passengers($this->location_prices[$normalized_location], $passengers);
        }

        // Buscar coincidencia parcial
        foreach ($this->location_prices as $known_location => $prices) {
            if (stripos($known_location, $normalized_location) !== false ||
                stripos($normalized_location, $known_location) !== false) {
                return $this->get_price_by_passengers($prices, $passengers);
            }
        }

        return null;
    }
    
    /**
     * Obtener precio según número de pasajeros
     */
    private function get_price_by_passengers($prices, $passengers) {
        $passengers = intval($passengers);
        
        if ($passengers >= 1 && $passengers <= 4) {
            return $prices['1-4'];
        } elseif ($passengers >= 5 && $passengers <= 8) {
            return $prices['5-8'];
        } elseif ($passengers >= 9 && $passengers <= 12) {
            return $prices['9-12'];
        } elseif ($passengers >= 13 && $passengers <= 16) {
            return $prices['13-16'];
        }
        
        // Para más de 16 pasajeros, usar el precio más alto + suplemento
        return $prices['13-16'] + (($passengers - 16) * 10);
    }
    
    /**
     * Calcular precio total de una reserva
     * 
     * @param array $booking_data Datos de la reserva
     * @return array Array con precio y desglose
     */
    public function calculate_price($booking_data) {
        $breakdown = array(
            'base_price' => 0,
            'vehicle_supplement' => 0,
            'night_supplement' => 0,
            'passenger_supplement' => 0,
            'extras' => array(),
            'total' => 0
        );
        
        $passengers = isset($booking_data['passengers']) ? intval($booking_data['passengers']) : 1;
        
        // 1. Intentar obtener precio desde CSV por ubicación
        $location = isset($booking_data['destination']) ? $booking_data['destination'] : '';
        $location_price = $this->get_location_price($location, $passengers);
        
        if ($location_price !== null) {
            // Usar precio de la tabla CSV
            $breakdown['base_price'] = $location_price;
            $breakdown['pricing_method'] = 'location_based';
            $breakdown['location'] = $location;
        } else {
            // Fallback al método anterior (por distancia)
            $distance = $this->calculate_distance($booking_data);
            $breakdown['base_price'] = $this->get_distance_rate($distance);
            $breakdown['distance_km'] = $distance;
            $breakdown['pricing_method'] = 'distance_based';
        }
        
        // 2. Determinar vehículo necesario según pasajeros
        $vehicle_type = $this->determine_vehicle_type($passengers);
        $breakdown['vehicle_type'] = $vehicle_type;
        
        // Solo aplicar suplemento de vehículo si usamos pricing por distancia
        if ($breakdown['pricing_method'] === 'distance_based') {
            $breakdown['vehicle_supplement'] = $this->config['vehicle_supplements'][$vehicle_type];
        } else {
            $breakdown['vehicle_supplement'] = 0; // Ya incluido en precio base
        }
        
        // 3. Suplemento nocturno
        if ($this->is_night_time($booking_data)) {
            $breakdown['night_supplement'] = $this->config['night_supplement'];
        }
        
        // 4. Suplemento por pasajeros extra
        $vehicle_capacity = $this->get_vehicle_capacity($vehicle_type);
        if ($passengers > $vehicle_capacity) {
            $extra_passengers = $passengers - $vehicle_capacity;
            $breakdown['passenger_supplement'] = $extra_passengers * $this->config['extra_passenger_rate'];
        }
        
        // 5. Extras
        $breakdown['extras'] = $this->calculate_extras($booking_data);
        
        // 6. Calcular total
        $breakdown['total'] = $breakdown['base_price'] 
                            + $breakdown['vehicle_supplement'] 
                            + $breakdown['night_supplement'] 
                            + $breakdown['passenger_supplement'] 
                            + array_sum($breakdown['extras']);
        
        // Aplicar redondeo
        $breakdown['total'] = round($breakdown['total'], 2);
        
        return $breakdown;
    }
    
    /**
     * Calcular distancia entre origen y destino
     */
    private function calculate_distance($booking_data) {
        $origin = isset($booking_data['origin']) ? $booking_data['origin'] : '';
        $destination = isset($booking_data['destination']) ? $booking_data['destination'] : '';
        
        // Normalizar nombres
        $origin = $this->normalize_location_name($origin);
        $destination = $this->normalize_location_name($destination);
        
        // Si es desde/hacia aeropuerto, usar tabla de distancias
        if ($this->is_airport($origin)) {
            return $this->get_airport_distance($destination);
        } elseif ($this->is_airport($destination)) {
            return $this->get_airport_distance($origin);
        }
        
        // Para punto a punto, estimar distancia (por defecto 30km)
        // En producción, podrías integrar con Google Maps Distance Matrix API
        return 30;
    }
    
    /**
     * Verificar si una ubicación es el aeropuerto
     */
    private function is_airport($location) {
        $location_lower = strtolower($location);
        return (strpos($location_lower, 'airport') !== false || 
                strpos($location_lower, 'aeropuerto') !== false ||
                strpos($location_lower, 'pmi') !== false);
    }
    
    /**
     * Obtener distancia desde aeropuerto
     */
    private function get_airport_distance($location) {
        $location = $this->normalize_location_name($location);
        
        // Buscar en la tabla de distancias
        if (isset($this->config['airport_distances'][$location])) {
            return $this->config['airport_distances'][$location];
        }
        
        // Buscar coincidencia parcial
        foreach ($this->config['airport_distances'] as $known_location => $distance) {
            if (stripos($location, $known_location) !== false || 
                stripos($known_location, $location) !== false) {
                return $distance;
            }
        }
        
        // Distancia por defecto si no se encuentra
        return 30;
    }
    
    /**
     * Normalizar nombre de ubicación
     */
    private function normalize_location_name($location) {
        // Eliminar prefijos comunes
        $location = preg_replace('/^(puerto de|port de|puerto|port)\s+/i', '', $location);
        
        // Capitalizar primera letra de cada palabra
        $location = ucwords(strtolower(trim($location)));
        
        return $location;
    }
    
    /**
     * Obtener tarifa según distancia
     */
    private function get_distance_rate($distance) {
        if ($distance <= 10) {
            return $this->config['distance_rates']['0-10'];
        } elseif ($distance <= 20) {
            return $this->config['distance_rates']['10-20'];
        } elseif ($distance <= 30) {
            return $this->config['distance_rates']['20-30'];
        } elseif ($distance <= 50) {
            return $this->config['distance_rates']['30-50'];
        } elseif ($distance <= 100) {
            return $this->config['distance_rates']['50-100'];
        } else {
            return $this->config['distance_rates']['100+'];
        }
    }
    
    /**
     * Determinar tipo de vehículo según número de pasajeros
     */
    private function determine_vehicle_type($passengers) {
        if ($passengers <= 4) {
            return 'standard';
        } elseif ($passengers <= 8) {
            return 'van';
        } elseif ($passengers <= 16) {
            return 'minibus';
        } else {
            return 'bus';
        }
    }
    
    /**
     * Obtener capacidad del vehículo
     */
    private function get_vehicle_capacity($vehicle_type) {
        $capacities = array(
            'standard' => 4,
            'van' => 8,
            'minibus' => 16,
            'bus' => 20
        );
        
        return isset($capacities[$vehicle_type]) ? $capacities[$vehicle_type] : 4;
    }
    
    /**
     * Verificar si es horario nocturno (22:00 - 06:00)
     */
    private function is_night_time($booking_data) {
        if (!isset($booking_data['datetime'])) {
            return false;
        }
        
        // Parsear fecha/hora (formato: DD/MM/YYYY - HH:MM)
        $datetime_parts = explode(' - ', $booking_data['datetime']);
        if (count($datetime_parts) < 2) {
            return false;
        }
        
        $time = trim($datetime_parts[1]);
        $time_parts = explode(':', $time);
        if (count($time_parts) < 2) {
            return false;
        }
        
        $hour = intval($time_parts[0]);
        
        // Horario nocturno: 22:00 - 06:00
        return ($hour >= 22 || $hour < 6);
    }
    
    /**
     * Calcular extras
     */
    private function calculate_extras($booking_data) {
        $extras_cost = array();
        
        // Mascota
        if (isset($booking_data['pet']) && $booking_data['pet'] !== 'no') {
            $pet_type = $booking_data['pet'];
            if ($pet_type === 'small_dog' || $pet_type === 'cat') {
                $extras_cost['pet'] = $this->config['extras']['pet_small'];
            } elseif ($pet_type === 'large_dog') {
                $extras_cost['pet'] = $this->config['extras']['pet_large'];
            }
        }
        
        // Opciones extras del nuevo sistema (bolsa golf, bicicleta, etc.)
        if (isset($booking_data['extras']) && is_array($booking_data['extras'])) {
            // Si tiene total_extras, es el nuevo formato
            if (isset($booking_data['extras']['total_extras'])) {
                // Procesar extras con costo
                if (isset($booking_data['extras']['bolsa_golf']) && is_array($booking_data['extras']['bolsa_golf'])) {
                    $extras_cost['bolsa_golf'] = $booking_data['extras']['bolsa_golf']['subtotal'];
                }
                if (isset($booking_data['extras']['bicicleta']) && is_array($booking_data['extras']['bicicleta'])) {
                    $extras_cost['bicicleta'] = $booking_data['extras']['bicicleta']['subtotal'];
                }
            } else {
                // Formato antiguo: array simple de extras
                foreach ($booking_data['extras'] as $extra) {
                    if (isset($this->config['extras'][$extra])) {
                        $extras_cost[$extra] = $this->config['extras'][$extra];
                    }
                }
            }
        }
        
        return $extras_cost;
    }
    
    /**
     * Formatear desglose de precio para mostrar al usuario
     */
    public function format_price_breakdown($breakdown) {
        $html = '<div class="met-price-breakdown">';
        $html .= '<strong>💰 Desglose del precio:</strong><br><br>';
        
        // Precio base
        if (isset($breakdown['location'])) {
            $html .= '📍 Ubicación: ' . $breakdown['location'] . '<br>';
        }
        if (isset($breakdown['distance_km'])) {
            $html .= '📏 Distancia: ~' . $breakdown['distance_km'] . ' km<br>';
        }
        $html .= '💵 Precio base: €' . number_format($breakdown['base_price'], 2) . '<br>';
        
        // Vehículo
        $vehicle_names = array(
            'standard' => 'Vehículo estándar (1-4 pax)',
            'van' => 'Van (5-8 pax)',
            'minibus' => 'Minibus (9-16 pax)',
            'bus' => 'Bus (17-20 pax)'
        );
        $html .= '🚗 Vehículo: ' . $vehicle_names[$breakdown['vehicle_type']];
        if ($breakdown['vehicle_supplement'] > 0) {
            $html .= ' (+€' . number_format($breakdown['vehicle_supplement'], 2) . ')';
        }
        $html .= '<br>';
        
        // Suplemento nocturno
        if ($breakdown['night_supplement'] > 0) {
            $html .= '🌙 Suplemento nocturno: +€' . number_format($breakdown['night_supplement'], 2) . '<br>';
        }
        
        // Pasajeros extra
        if ($breakdown['passenger_supplement'] > 0) {
            $html .= '👥 Pasajeros extra: +€' . number_format($breakdown['passenger_supplement'], 2) . '<br>';
        }
        
        // Extras
        if (!empty($breakdown['extras'])) {
            foreach ($breakdown['extras'] as $extra_name => $extra_cost) {
                $extra_labels = array(
                    'pet' => '🐾 Mascota',
                    'child_seat' => '👶 Silla infantil',
                    'booster_seat' => '🪑 Elevador',
                    'luggage_extra' => '🧳 Equipaje extra',
                    'meet_greet' => '👋 Meet & Greet',
                    'bolsa_golf' => '⛳ Bolsa de Golf',
                    'bicicleta' => '🚴 Bicicleta'
                );
                $label = isset($extra_labels[$extra_name]) ? $extra_labels[$extra_name] : ucfirst($extra_name);
                $html .= $label . ': +€' . number_format($extra_cost, 2) . '<br>';
            }
        }
        
        $html .= '<br>';
        $html .= '<strong style="font-size: 1.2em;">💳 TOTAL: €' . number_format($breakdown['total'], 2) . '</strong>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Obtener nombre legible del vehículo
     */
    public function get_vehicle_name($vehicle_type) {
        $names = array(
            'standard' => 'Vehículo Estándar',
            'van' => 'Van',
            'minibus' => 'Minibus',
            'bus' => 'Bus'
        );
        
        return isset($names[$vehicle_type]) ? $names[$vehicle_type] : 'Vehículo';
    }
}
