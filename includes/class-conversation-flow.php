<?php
/**
 * Clase para manejar el flujo de conversación del chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Conversation_Flow {
    
    /**
     * Procesar mensaje del usuario
     */
    public function process_message($message, $step, $data) {
        $response = array(
            'message' => '',
            'nextStep' => '',
            'options' => array(),
            'data' => $data
        );
        
        switch ($step) {
            case 'welcome':
                $response = $this->step_welcome();
                break;
                
            case 'route_type':
                $response = $this->step_route_type($message, $data);
                break;
                
            case 'airport_origin':
                $response = $this->step_airport_origin($message, $data);
                break;
                
            case 'destination':
                $response = $this->step_destination($message, $data);
                break;
                
            case 'passengers':
                $response = $this->step_passengers($message, $data);
                break;
                
            case 'pet':
                $response = $this->step_pet($message, $data);
                break;
                
            case 'datetime':
                $response = $this->step_datetime($message, $data);
                break;
                
            case 'flight_number':
                $response = $this->step_flight_number($message, $data);
                break;
                
            case 'point_to_point_origin':
                $response = $this->step_point_to_point_origin($message, $data);
                break;
                
            case 'point_to_point_destination':
                $response = $this->step_point_to_point_destination($message, $data);
                break;
                
            case 'verify_booking_code':
                $response = $this->step_verify_booking_code($message, $data);
                break;
                
            default:
                $response = $this->step_welcome();
        }
        
        return $response;
    }
    
    /**
     * Paso 1: Bienvenida
     */
    private function step_welcome() {
        return array(
            'message' => '👋 ¡Hola! Soy el asistente de MET Mallorca. Te ayudo a reservar en 1 minuto.<br><br>¿De dónde a dónde viajas?',
            'nextStep' => 'route_type',
            'options' => array(
                array('text' => '<i class="fas fa-plane"></i> Aeropuerto ↔ Punto (hotel o casa)', 'value' => 'airport'),
                array('text' => '<i class="fas fa-car"></i> Punto ↔ Punto dentro de Mallorca', 'value' => 'point_to_point'),
                array('text' => '<i class="fas fa-search"></i> Verificar mi reserva', 'value' => 'verify')
            ),
            'data' => array()
        );
    }
    
    /**
     * Paso 2: Tipo de ruta
     */
    private function step_route_type($message, $data) {
        $data['route_type'] = $message;
        
        if ($message === 'verify') {
            return array(
                'message' => '🔍 Por favor, escribe tu número de reserva (ej. MET-123456) y tu email separados por coma.<br><br>Ejemplo: MET-123456, email@ejemplo.com',
                'nextStep' => 'verify_booking_code',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text'
            );
        }
        
        if ($message === 'point_to_point') {
            return array(
                'message' => '📍 Perfecto. ¿Desde qué punto de Mallorca sales?<br><br>Escribe la ciudad o dirección de origen:',
                'nextStep' => 'point_to_point_origin',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text'
            );
        }
        
        // Ruta aeropuerto
        return array(
            'message' => '✈️ Perfecto. ¿Desde qué aeropuerto sales o llegas?',
            'nextStep' => 'airport_origin',
            'options' => array(
                array('text' => '<i class="fas fa-plane-departure"></i> Aeropuerto de Palma (PMI)', 'value' => 'Aeropuerto de Palma'),
                array('text' => '<i class="fas fa-globe"></i> Otro aeropuerto', 'value' => 'other')
            ),
            'data' => $data
        );
    }
    
    /**
     * Paso 3: Origen aeropuerto
     */
    private function step_airport_origin($message, $data) {
        $data['origin'] = $message;
        
        return array(
            'message' => '📍 ¿Cuál es tu destino?<br><br>Escribe el nombre del hotel, dirección o zona:',
            'nextStep' => 'destination',
            'options' => array(),
            'data' => $data,
            'inputType' => 'text'
        );
    }
    
    /**
     * Paso 4: Destino
     */
    private function step_destination($message, $data) {
        $data['destination'] = $message;
        
        return array(
            'message' => '👥 ¿Cuántas personas viajan?',
            'nextStep' => 'passengers',
            'options' => array(),
            'data' => $data,
            'inputType' => 'number'
        );
    }
    
    /**
     * Paso 5: Número de pasajeros
     */
    private function step_passengers($message, $data) {
        $passengers = intval($message);
        $data['passengers'] = $passengers;
        
        // Si son más de 20 personas, derivar a presupuesto
        if ($passengers > 20) {
            return array(
                'message' => '👥 Son ' . $passengers . ' pasajeros, perfecto.<br><br>Para grupos de más de 20 personas, gestionamos el traslado mediante un presupuesto personalizado.',
                'nextStep' => 'complete_group',
                'options' => array(
                    array('text' => '📋 Solicitar presupuesto de grupo', 'value' => 'request_quote')
                ),
                'data' => $data
            );
        }
        
        return array(
            'message' => '🐾 ¿Viajas con mascota?',
            'nextStep' => 'pet',
            'options' => array(
                array('text' => '<i class="fas fa-dog"></i> Sí, perro pequeño', 'value' => 'small_dog'),
                array('text' => '<i class="fas fa-dog"></i> Sí, perro grande', 'value' => 'large_dog'),
                array('text' => '<i class="fas fa-cat"></i> Sí, gato', 'value' => 'cat'),
                array('text' => '<i class="fas fa-times-circle"></i> No', 'value' => 'no')
            ),
            'data' => $data
        );
    }
    
    /**
     * Paso 6: Mascota
     */
    private function step_pet($message, $data) {
        $data['pet'] = $message;
        
        return array(
            'message' => '📅 ¿Fecha y hora del traslado?<br><br>Formato: DD/MM/YYYY - HH:MM<br>Ejemplo: 15/11/2025 - 09:00',
            'nextStep' => 'datetime',
            'options' => array(),
            'data' => $data,
            'inputType' => 'text'
        );
    }
    
    /**
     * Paso 7: Fecha y hora
     */
    private function step_datetime($message, $data) {
        $data['datetime'] = $message;
        
        return array(
            'message' => '✈️ ¿Número de vuelo? (opcional)<br><br>Si no tienes, escribe "No" o "Skip"',
            'nextStep' => 'flight_number',
            'options' => array(),
            'data' => $data,
            'inputType' => 'text'
        );
    }
    
    /**
     * Paso 8: Número de vuelo y resumen
     */
    private function step_flight_number($message, $data) {
        $data['flight_number'] = ($message === 'No' || $message === 'Skip') ? '' : $message;
        
        // Generar resumen
        $summary = $this->generate_summary($data);
        
        return array(
            'message' => '✅ ' . $summary . '<br><br>Todo listo. Puedes reservar y pagar ahora desde nuestro sistema:',
            'nextStep' => 'complete',
            'options' => array(
                array('text' => '<i class="fas fa-check-circle"></i> Reservar ahora', 'value' => 'book_now', 'url' => $this->generate_booking_url($data))
            ),
            'data' => $data
        );
    }
    
    /**
     * Punto a punto - Origen
     */
    private function step_point_to_point_origin($message, $data) {
        $data['origin'] = $message;
        
        return array(
            'message' => '📍 ¿Cuál es tu destino?<br><br>Escribe la ciudad o dirección de destino:',
            'nextStep' => 'point_to_point_destination',
            'options' => array(),
            'data' => $data,
            'inputType' => 'text'
        );
    }
    
    /**
     * Punto a punto - Destino
     */
    private function step_point_to_point_destination($message, $data) {
        $data['destination'] = $message;
        
        return array(
            'message' => '👥 ¿Cuántas personas viajan?',
            'nextStep' => 'passengers',
            'options' => array(),
            'data' => $data,
            'inputType' => 'number'
        );
    }
    
    /**
     * Verificar código de reserva
     */
    private function step_verify_booking_code($message, $data) {
        // El mensaje debe contener: código, email
        $parts = explode(',', $message);
        
        if (count($parts) < 2) {
            return array(
                'message' => '❌ Por favor, proporciona el código de reserva y email separados por coma.<br><br>Ejemplo: MET-123456, email@ejemplo.com',
                'nextStep' => 'verify_booking_code',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text'
            );
        }
        
        $data['booking_code'] = trim($parts[0]);
        $data['email'] = trim($parts[1]);
        
        return array(
            'message' => '🔍 Verificando tu reserva...',
            'nextStep' => 'verify_result',
            'options' => array(),
            'data' => $data,
            'action' => 'verify_booking'
        );
    }
    
    /**
     * Generar resumen de la reserva
     */
    private function generate_summary($data) {
        $summary = '<strong>Resumen de tu reserva:</strong><br>';
        
        if (isset($data['route_type']) && $data['route_type'] === 'airport') {
            $summary .= '✈️ ' . $data['origin'] . ' → ' . $data['destination'] . '<br>';
        } else {
            $summary .= '🚗 ' . $data['origin'] . ' → ' . $data['destination'] . '<br>';
        }
        
        $summary .= '👥 ' . $data['passengers'] . ' persona(s)<br>';
        
        if (isset($data['pet']) && $data['pet'] !== 'no') {
            $summary .= '🐾 Mascota: ' . $this->format_pet($data['pet']) . '<br>';
        }
        
        $summary .= '📅 ' . $data['datetime'] . '<br>';
        
        if (!empty($data['flight_number'])) {
            $summary .= '✈️ Vuelo: ' . $data['flight_number'] . '<br>';
        }
        
        return $summary;
    }
    
    /**
     * Formatear tipo de mascota
     */
    private function format_pet($pet) {
        $pets = array(
            'small_dog' => 'Perro pequeño',
            'large_dog' => 'Perro grande',
            'cat' => 'Gato',
            'no' => 'No'
        );
        
        return isset($pets[$pet]) ? $pets[$pet] : $pet;
    }
    
    /**
     * Generar URL de reserva con datos prellenados
     */
    private function generate_booking_url($data) {
        // Aquí debes poner la URL real de tu formulario de reservas
        $base_url = home_url('/reservar/');
        
        $params = array(
            'origin' => urlencode($data['origin']),
            'destination' => urlencode($data['destination']),
            'passengers' => $data['passengers'],
            'datetime' => urlencode($data['datetime'])
        );
        
        if (!empty($data['pet']) && $data['pet'] !== 'no') {
            $params['pet'] = $data['pet'];
        }
        
        if (!empty($data['flight_number'])) {
            $params['flight'] = urlencode($data['flight_number']);
        }
        
        return add_query_arg($params, $base_url);
    }
}
