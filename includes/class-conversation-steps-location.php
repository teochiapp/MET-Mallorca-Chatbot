<?php
/**
 * Steps de selección de ubicaciones (origen y destino)
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Conversation_Steps_Location {
    
    private $validator;
    
    public function __construct() {
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-booking-validator.php';
        $this->validator = new MET_Booking_Validator();
    }
    
    /**
     * Step: Origen
     */
    public function step_origin($message, $data) {
        // Si seleccionó "custom_origin", pedir texto
        if ($message === 'custom_origin') {
            return array(
                'message' => '📍 <strong>Ubicación de Origen</strong><br><br>' .
                            'Escribe el nombre de tu hotel, dirección o ubicación de recogida:',
                'nextStep' => 'origin_text',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text',
                'showBackButton' => true,
                'placeholder' => 'Ej: Hotel Son Caliu, Palmanova'
            );
        }
        
        // Guardar origen
        $data['origin'] = $message;
        
        // Determinar opciones de destino según el origen
        $is_airport_origin = $this->is_airport($message);
        
        if ($is_airport_origin) {
            // Si viene del aeropuerto, mostrar destinos populares
            return array(
                'message' => '📍 <strong>¿Cuál es tu destino?</strong><br><br>' .
                            'Selecciona una zona o escribe tu destino:',
                'nextStep' => 'destination',
                'options' => $this->get_popular_destinations(),
                'data' => $data,
                'showBackButton' => true
            );
        } else {
            // Si viene de un hotel, el destino principal es el aeropuerto
            return array(
                'message' => '📍 <strong>¿Cuál es tu destino?</strong>',
                'nextStep' => 'destination',
                'options' => array(
                    array(
                        'text' => '<i class="fas fa-plane"></i> Aeropuerto de Palma (PMI)',
                        'value' => 'Aeropuerto de Palma'
                    ),
                    array(
                        'text' => '<i class="fas fa-map-marker-alt"></i> Otra ubicación',
                        'value' => 'custom_destination'
                    )
                ),
                'data' => $data,
                'showBackButton' => true
            );
        }
    }
    
    /**
     * Step: Origen como texto libre
     */
    public function step_origin_text($message, $data) {
        // Validar ubicación
        $validation = $this->validator->validate_location($message);
        
        if (!$validation['valid']) {
            return array(
                'message' => $validation['error'] . '<br><br>Por favor, intenta de nuevo:',
                'nextStep' => 'origin_text',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text',
                'showBackButton' => true
            );
        }
        
        $data['origin'] = $validation['location'];
        
        // Continuar al destino
        return array(
            'message' => '📍 <strong>¿Cuál es tu destino?</strong><br><br>' .
                        'Escribe la ubicación de destino:',
            'nextStep' => 'destination_text',
            'options' => array(),
            'data' => $data,
            'inputType' => 'text',
            'showBackButton' => true,
            'placeholder' => 'Ej: Aeropuerto de Palma'
        );
    }
    
    /**
     * Step: Destino
     */
    public function step_destination($message, $data) {
        // Si seleccionó "custom_destination", pedir texto
        if ($message === 'custom_destination') {
            return array(
                'message' => '📍 <strong>Ubicación de Destino</strong><br><br>' .
                            'Escribe el nombre de tu hotel, dirección o ubicación de destino:',
                'nextStep' => 'destination_text',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text',
                'showBackButton' => true,
                'placeholder' => 'Ej: Cala Millor, Hotel Hipotels'
            );
        }
        
        // Guardar destino
        $data['destination'] = $message;
        
        // Continuar a fecha
        return $this->ask_for_date($data);
    }
    
    /**
     * Step: Destino como texto libre
     */
    public function step_destination_text($message, $data) {
        // Validar ubicación
        $validation = $this->validator->validate_location($message);
        
        if (!$validation['valid']) {
            return array(
                'message' => $validation['error'] . '<br><br>Por favor, intenta de nuevo:',
                'nextStep' => 'destination_text',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text',
                'showBackButton' => true
            );
        }
        
        $data['destination'] = $validation['location'];
        
        // Continuar a fecha
        return $this->ask_for_date($data);
    }
    
    /**
     * Preguntar por la fecha
     */
    private function ask_for_date($data) {
        return array(
            'message' => '📅 <strong>¿Qué día necesitas el traslado?</strong><br><br>' .
                        'Escribe la fecha en formato <strong>DD/MM/YYYY</strong><br><br>' .
                        '<em>Ejemplo: 25/12/2025</em>',
            'nextStep' => 'date',
            'options' => array(),
            'data' => $data,
            'inputType' => 'text',
            'showBackButton' => true,
            'placeholder' => 'DD/MM/YYYY'
        );
    }
    
    /**
     * Verificar si una ubicación es el aeropuerto
     */
    private function is_airport($location) {
        $location_lower = strtolower($location);
        return (strpos($location_lower, 'aeropuerto') !== false || 
                strpos($location_lower, 'airport') !== false ||
                strpos($location_lower, 'pmi') !== false);
    }
    
    /**
     * Obtener destinos populares desde el aeropuerto
     */
    private function get_popular_destinations() {
        return array(
            array('text' => '🏖️ Palma', 'value' => 'Palma'),
            array('text' => '🏖️ Palma Nova', 'value' => 'Palma Nova'),
            array('text' => '🏖️ Magaluf', 'value' => 'Magaluf'),
            array('text' => '🏖️ Santa Ponsa', 'value' => 'Santa Ponsa'),
            array('text' => '🏖️ Alcudia', 'value' => 'Alcudia'),
            array('text' => '🏖️ Puerto Pollensa', 'value' => 'Puerto Pollensa'),
            array('text' => '🏖️ Cala Millor', 'value' => 'Cala Millor'),
            array('text' => '🏖️ Cala D\'or', 'value' => 'Cala D\'or'),
            array('text' => '<i class="fas fa-map-marker-alt"></i> Otra ubicación...', 'value' => 'custom_destination')
        );
    }
}
