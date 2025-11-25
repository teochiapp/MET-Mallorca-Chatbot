<?php
/**
 * Steps de detalles de la reserva (fecha, hora, pasajeros, extras)
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Conversation_Steps_Details {
    
    private $validator;
    
    public function __construct() {
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-booking-validator.php';
        $this->validator = new MET_Booking_Validator();
    }
    
    /**
     * Step: Fecha
     */
    public function step_date($message, $data) {
        // Validar fecha
        $validation = $this->validator->validate_date($message);
        
        if (!$validation['valid']) {
            return array(
                'message' => $validation['error'] . '<br><br>Por favor, intenta de nuevo:',
                'nextStep' => 'date',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text',
                'showBackButton' => true,
                'placeholder' => 'DD/MM/YYYY'
            );
        }
        
        $data['date'] = $validation['date'];
        
        // Continuar a hora
        return array(
            'message' => '🕐 <strong>¿A qué hora necesitas el traslado?</strong><br><br>' .
                        'Selecciona una hora disponible (intervalos de 30 minutos).<br>' .
                        '<em>Ejemplo: 14:00, 14:30, 15:00…</em>',
            'nextStep' => 'time',
            'options' => array(),
            'data' => $data,
            'inputType' => 'time_searcher',
            'showBackButton' => true,
            'placeholder' => 'Buscar horario (ej: 14:30)'
        );
    }
    
    /**
     * Step: Hora
     */
    public function step_time($message, $data) {
        // Validar hora
        $validation = $this->validator->validate_time($message);
        
        if (!$validation['valid']) {
            return array(
                'message' => $validation['error'] . '<br><br>Por favor, intenta de nuevo:',
                'nextStep' => 'time',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text',
                'showBackButton' => true,
                'placeholder' => 'HH:MM'
            );
        }
        
        $data['time'] = $validation['time'];
        $data['datetime'] = $data['date'] . ' - ' . $data['time'];
        
        // Continuar a pasajeros
        return array(
            'message' => '👥 <strong>¿Cuántas personas viajan?</strong><br><br>' .
                        'Escribe el número de pasajeros:',
            'nextStep' => 'passengers',
            'options' => array(),
            'data' => $data,
            'inputType' => 'number',
            'showBackButton' => true,
            'placeholder' => 'Ej: 4'
        );
    }
    
    /**
     * Step: Pasajeros
     */
    public function step_passengers($message, $data) {
        // Validar pasajeros
        $validation = $this->validator->validate_passengers($message);
        
        if (!$validation['valid']) {
            return array(
                'message' => $validation['error'] . '<br><br>Por favor, intenta de nuevo:',
                'nextStep' => 'passengers',
                'options' => array(),
                'data' => $data,
                'inputType' => 'number',
                'showBackButton' => true
            );
        }
        
        $data['passengers'] = $validation['passengers'];
        
        // Si son más de 20 personas, derivar a presupuesto personalizado
        if ($data['passengers'] > 20) {
            return array(
                'message' => '👥 <strong>Grupo Grande</strong><br><br>' .
                            'Para grupos de más de 20 personas, te recomendamos solicitar un presupuesto personalizado.<br><br>' .
                            'Por favor, contacta con nosotros en:<br>' .
                            '📞 <a href="tel:+34971123456">+34 971 123 456</a><br>' .
                            '📧 <a href="mailto:reservas@metmallorca.com">reservas@metmallorca.com</a>',
                'nextStep' => 'end',
                'options' => array(
                    array('text' => '🔄 Empezar nueva reserva', 'value' => 'restart')
                ),
                'data' => $data,
                'showBackButton' => false
            );
        }
        
        // Establecer valores por defecto para campos eliminados
        $data['pet'] = 'no';
        $data['flight_number'] = '';
        
        // Ir al paso de opciones extras
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-conversation-steps-extras.php';
        $extras_steps = new MET_Conversation_Steps_Extras();
        return $extras_steps->step_extras('', $data);
    }
    
    /**
     * Ir al paso de resumen
     */
    private function go_to_summary($data) {
        // Cargar el módulo de resumen y ejecutar directamente
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-conversation-steps-summary.php';
        $summary_steps = new MET_Conversation_Steps_Summary();
        return $summary_steps->step_summary('', $data);
    }
    
    /**
     * Verificar si es una ruta de aeropuerto
     */
    private function is_airport_route($data) {
        if (!isset($data['origin']) || !isset($data['destination'])) {
            return false;
        }
        
        $origin_lower = strtolower($data['origin']);
        $destination_lower = strtolower($data['destination']);
        
        return (strpos($origin_lower, 'aeropuerto') !== false || 
                strpos($origin_lower, 'airport') !== false ||
                strpos($destination_lower, 'aeropuerto') !== false || 
                strpos($destination_lower, 'airport') !== false);
    }
}
