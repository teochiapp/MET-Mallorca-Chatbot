<?php
/**
 * Steps de bienvenida y selección de tipo de ruta
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Conversation_Steps_Welcome {
    
    private $translations;
    
    public function __construct() {
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-translations.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-booking-verifier.php';
        $this->translations = new MET_Translations();
    }
    
    /**
     * Step: Bienvenida inicial
     */
    public function step_welcome($message = '', $data = array()) {
        // Inicializar idioma desde datos
        MET_Translations::init_from_data($data);
        
        return array(
            'message' => '👋 <strong>' . MET_Translations::t('welcome_title') . '</strong><br><br>' .
                        MET_Translations::t('welcome_message') . '<br><br>' .
                        MET_Translations::t('welcome_question'),
            'nextStep' => 'route_type',
            'options' => array(
                array(
                    'text' => MET_Translations::t('option_airport'),
                    'value' => 'airport'
                ),
                array(
                    'text' => MET_Translations::t('option_point_to_airport'),
                    'value' => 'point_to_airport'
                ),
                array(
                    'text' => MET_Translations::t('option_verify'),
                    'value' => 'verify'
                )
            ),
            'data' => $data,
            'showBackButton' => false
        );
    }
    
    /**
     * Step: Tipo de ruta seleccionado
     */
    public function step_route_type($message, $data) {
        // Inicializar idioma desde datos
        MET_Translations::init_from_data($data);
        
        $data['route_type'] = $message;
        
        // Flujo de verificación
        if ($message === 'verify') {
            return array(
                'message' => '🔍 <strong>' . MET_Translations::t('verify_title') . '</strong><br><br>' .
                            MET_Translations::t('verify_message') . '<br><br>' .
                            '<em>' . MET_Translations::t('verify_example') . '</em>',
                'nextStep' => 'verify_booking_code',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text',
                'showBackButton' => true
            );
        }
        
        // Flujo de punto → aeropuerto - Usar buscador inteligente
        if ($message === 'point_to_airport') {
            return array(
                'message' => '🚗 <strong>' . MET_Translations::t('route_point_title') . '</strong><br><br>' .
                            MET_Translations::t('route_point_question'),
                'nextStep' => 'origin_text',
                'options' => array(),
                'data' => $data,
                'inputType' => 'location',
                'showBackButton' => true,
                'placeholder' => 'Buscar ubicación de origen...'
            );
        }
        
        // Flujo de aeropuerto (por defecto) - evitar paso redundante
        $data['origin'] = 'Aeropuerto de Palma';

        return array(
            'message' => '✈️ <strong>' . MET_Translations::t('route_airport_title') . '</strong><br><br>' .
                        MET_Translations::t('route_airport_question'),
            'nextStep' => 'destination_text',
            'options' => array(),
            'data' => $data,
            'inputType' => 'location',
            'showBackButton' => true,
            'placeholder' => 'Buscar destino...'
        );
    }
}
