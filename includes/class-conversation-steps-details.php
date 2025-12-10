<?php
/**
 * Steps de detalles de la reserva (fecha, hora, pasajeros, extras)
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Conversation_Steps_Details {
    
    private $validator;
    private $translations;
    
    public function __construct() {
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-translations.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-booking-validator.php';
        $this->validator = new MET_Booking_Validator();
        $this->translations = new MET_Translations();
    }
    
    /**
     * Step: Fecha
     */
    public function step_date($message, $data) {
        MET_Translations::init_from_data($data);
        
        // Validar fecha
        $validation = $this->validator->validate_date($message);
        
        if (!$validation['valid']) {
            return array(
                'message' => $validation['error'] . '<br><br>' . MET_Translations::t('date_error_retry'),
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
            'message' => '🕐 <strong>' . MET_Translations::t('time_title') . '</strong><br><br>' .
                        MET_Translations::t('time_message') . '<br>' .
                        '<em>' . MET_Translations::t('time_example') . '</em>',
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
        MET_Translations::init_from_data($data);
        
        // Validar hora
        $validation = $this->validator->validate_time($message);
        
        if (!$validation['valid']) {
            return array(
                'message' => $validation['error'] . '<br><br>' . MET_Translations::t('time_error_retry'),
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
            'message' => '👥 <strong>' . MET_Translations::t('passengers_title') . '</strong><br><br>' .
                        MET_Translations::t('passengers_question'),
            'nextStep' => 'passengers',
            'options' => array(),
            'data' => $data,
            'inputType' => 'number',
            'showBackButton' => true,
            'placeholder' => MET_Translations::t('passengers_example')
        );
    }
    
    /**
     * Step: Pasajeros
     */
    public function step_passengers($message, $data) {
        MET_Translations::init_from_data($data);
        
        // Validar pasajeros
        $validation = $this->validator->validate_passengers($message);
        
        if (!$validation['valid']) {
            return array(
                'message' => $validation['error'] . '<br><br>' . MET_Translations::t('passengers_error_retry'),
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
                'message' => '👥 <strong>' . MET_Translations::t('passengers_large_group') . '</strong><br><br>' .
                            MET_Translations::t('passengers_large_message') . '<br><br>' .
                            MET_Translations::t('passengers_contact') . '<br>' .
                            '📧 <a href="mailto:metmallorca@yahoo.com">metmallorca@yahoo.com</a><br><br>' .
                            '<a href="https://metmallorca.com/es/presupuesto/" target="_blank" rel="noopener" style="color:#E86A1C;font-weight:600;">Solicitar presupuesto personalizado</a>',
                'nextStep' => 'end',
                'options' => array(
                    array('text' => '🔄 ' . MET_Translations::t('modify_start_over'), 'value' => 'restart')
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
