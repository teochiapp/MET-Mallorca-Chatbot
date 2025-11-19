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
        
        // 1. Calcular precio base por distancia
        $distance = $this->calculate_distance($booking_data);
        $breakdown['base_price'] = $this->get_distance_rate($distance);
        $breakdown['distance_km'] = $distance;
        
        // 2. Determinar vehículo necesario según pasajeros
        $passengers = isset($booking_data['passengers']) ? intval($booking_data['passengers']) : 1;
        $vehicle_type = $this->determine_vehicle_type($passengers);
        $breakdown['vehicle_type'] = $vehicle_type;
        $breakdown['vehicle_supplement'] = $this->config['vehicle_supplements'][$vehicle_type];
        
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
        
        // Otros extras (si se agregan en el futuro)
        if (isset($booking_data['extras']) && is_array($booking_data['extras'])) {
            foreach ($booking_data['extras'] as $extra) {
                if (isset($this->config['extras'][$extra])) {
                    $extras_cost[$extra] = $this->config['extras'][$extra];
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
        $html .= '📏 Distancia: ~' . $breakdown['distance_km'] . ' km<br>';
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
                    'meet_greet' => '👋 Meet & Greet'
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
