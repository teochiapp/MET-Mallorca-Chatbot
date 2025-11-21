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
        // Si seleccionó "custom_origin", mostrar buscador de ubicaciones
        if ($message === 'custom_origin') {
            return array(
                'message' => '📍 <strong>Ubicación de Origen</strong><br><br>' .
                            'Busca y selecciona tu ubicación de recogida:',
                'nextStep' => 'origin_text',
                'options' => array(),
                'data' => $data,
                'inputType' => 'location',
                'showBackButton' => true,
                'placeholder' => 'Buscar ubicación de origen...'
            );
        }
        
        // Guardar origen
        $data['origin'] = $message;
        
        // Siempre mostrar buscador inteligente para destino (120+ ubicaciones)
        return array(
            'message' => '📍 <strong>¿Cuál es tu destino?</strong><br><br>' .
                        'Busca y selecciona tu ubicación de destino:',
            'nextStep' => 'destination_text',
            'options' => array(),
            'data' => $data,
            'inputType' => 'location',
            'showBackButton' => true,
            'placeholder' => 'Buscar ubicación de destino...'
        );
    }
    
    /**
     * Step: Origen como texto libre (desde buscador)
     */
    public function step_origin_text($message, $data) {
        // El mensaje ya viene validado del buscador, solo guardarlo
        $data['origin'] = $message;
        
        // Continuar al destino con buscador
        return array(
            'message' => '📍 <strong>¿Cuál es tu destino?</strong><br><br>' .
                        'Busca y selecciona tu ubicación de destino:',
            'nextStep' => 'destination_text',
            'options' => array(),
            'data' => $data,
            'inputType' => 'location',
            'showBackButton' => true,
            'placeholder' => 'Buscar ubicación de destino...'
        );
    }
    
    /**
     * Step: Destino
     * NOTA: Este step ya no se usa, se va directo a destination_text con el buscador
     */
    public function step_destination($message, $data) {
        // Guardar destino
        $data['destination'] = $message;
        
        // Continuar a fecha
        return $this->ask_for_date($data);
    }
    
    /**
     * Step: Destino como texto libre (desde buscador)
     */
    public function step_destination_text($message, $data) {
        // El mensaje ya viene validado del buscador, solo guardarlo
        $data['destination'] = $message;
        
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
     * MÉTODO DEPRECADO: Ya no se usan listas de destinos populares
     * Ahora siempre se usa el buscador inteligente para las 120+ ubicaciones
     */
    private function get_popular_destinations() {
        // Método mantenido por compatibilidad pero ya no se usa
        return array();
    }
}
