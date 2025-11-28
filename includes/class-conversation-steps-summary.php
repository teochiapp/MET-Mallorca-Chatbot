<?php
/**
 * Steps de resumen, cálculo de precio y generación de checkout
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Conversation_Steps_Summary {
    
    private $pricing_engine;
    private $checkout_generator;
    private $translations;
    
    public function __construct() {
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-translations.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-pricing-engine.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-checkout-generator.php';
        
        $this->pricing_engine = new MET_Pricing_Engine();
        $this->checkout_generator = new MET_Checkout_Generator();
        $this->translations = new MET_Translations();
    }
    
    /**
     * Step: Resumen y cálculo de precio
     */
    public function step_summary($message, $data) {
        MET_Translations::init_from_data($data);
        
        // Validar que tengamos todos los datos necesarios
        $required_fields = array('origin', 'destination', 'date', 'time', 'passengers');
        $missing_fields = array();
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            return array(
                'message' => '❌ <strong>' . MET_Translations::t('summary_error_missing') . '</strong><br><br>' .
                            MET_Translations::t('summary_error_fields') . ': ' . implode(', ', $missing_fields) . '<br><br>',
                'nextStep' => 'welcome',
                'options' => array(
                    array('text' => '🔄 ' . MET_Translations::t('btn_restart'), 'value' => 'restart')
                ),
                'data' => array()
            );
        }
        
        // Calcular precio
        try {
            $price_breakdown = $this->pricing_engine->calculate_price($data);
        } catch (Exception $e) {
            return array(
                'message' => '❌ <strong>' . MET_Translations::t('summary_error_calculate') . ':</strong><br><br>' .
                            $e->getMessage() . '<br><br>',
                'nextStep' => 'welcome',
                'options' => array(
                    array('text' => '🔄 ' . MET_Translations::t('btn_restart'), 'value' => 'restart')
                ),
                'data' => array()
            );
        }
        
        // Guardar desglose en data
        $data['price_breakdown'] = $price_breakdown;
        
        // Generar resumen visual
        $summary_html = $this->generate_summary_html($data, $price_breakdown);
        
        // Generar desglose de precio
        $price_html = $this->pricing_engine->format_price_breakdown($price_breakdown);
        
        $message = '✅ <strong>' . MET_Translations::t('summary_title') . '</strong><br><br>' .
                   $summary_html . '<br>' .
                   $price_html . '<br><br>' .
                   MET_Translations::t('summary_question');
        
        return array(
            'message' => $message,
            'nextStep' => 'confirm',
            'options' => array(
                array(
                    'text' => MET_Translations::t('summary_continue_checkout'),
                    'value' => 'confirm'
                ),
                array(
                    'text' => MET_Translations::t('summary_modify_data'),
                    'value' => 'modify'
                )
            ),
            'data' => $data,
            'showBackButton' => false
        );
    }
    
    /**
     * Step: Confirmación
     */
    public function step_confirm($message, $data) {
        MET_Translations::init_from_data($data);
        
        if ($message === 'modify') {
            return array(
                'message' => '✏️ <strong>' . MET_Translations::t('modify_title') . '</strong>',
                'nextStep' => 'modify_choice',
                'options' => array(
                    array('text' => '📍 ' . MET_Translations::t('modify_locations'), 'value' => 'locations'),
                    array('text' => '📅 ' . MET_Translations::t('modify_datetime'), 'value' => 'datetime'),
                    array('text' => '👥 ' . MET_Translations::t('modify_passengers'), 'value' => 'passengers'),
                    array('text' => '🔄 ' . MET_Translations::t('modify_start_over'), 'value' => 'restart')
                ),
                'data' => $data,
                'showBackButton' => true
            );
        }
        
        // Generar URL de checkout
        if (!isset($data['price_breakdown'])) {
            return array(
                'message' => '❌ ' . MET_Translations::t('summary_error_calculate'),
                'nextStep' => 'welcome',
                'options' => array(
                    array('text' => '🔄 ' . MET_Translations::t('modify_start_over'), 'value' => 'restart')
                ),
                'data' => array()
            );
        }
        
        $checkout_url = $this->checkout_generator->generate_checkout_url($data, $data['price_breakdown']);
        
        $message = '🎉 <strong>' . MET_Translations::t('confirm_perfect') . '</strong><br><br>' .
                   MET_Translations::t('confirm_message') . '<br><br>' .
                   '<div class="met-checkout-link-container">' .
                   '<a href="' . esc_url($checkout_url) . '" class="met-checkout-link" target="_blank">' .
                   '<i class="fas fa-shopping-cart"></i> ' . MET_Translations::t('confirm_checkout_button') . ' (€' . number_format($data['price_breakdown']['total'], 2) . ')' .
                   '</a>' .
                   '</div><br>' .
                   '<small>💳 ' . MET_Translations::t('confirm_payment_secure') . '</small><br>' .
                   '<small>🔒 ' . MET_Translations::t('confirm_data_protected') . '</small>';
        
        return array(
            'message' => $message,
            'nextStep' => 'end',
            'options' => array(
                array('text' => '🔄 ' . MET_Translations::t('confirm_another_booking'), 'value' => 'restart')
            ),
            'data' => $data,
            'showBackButton' => false,
            'checkoutUrl' => $checkout_url
        );
    }
    
    /**
     * Step: Elección de modificación
     */
    public function step_modify_choice($message, $data) {
        MET_Translations::init_from_data($data);
        
        switch ($message) {
            case 'locations':
                // Volver a origen
                unset($data['origin'], $data['destination']);
                return array(
                    'message' => '📍 <strong>' . MET_Translations::t('modify_locations_title') . '</strong><br><br>' . MET_Translations::t('modify_locations_question'),
                    'nextStep' => 'origin',
                    'options' => array(
                        array('text' => MET_Translations::t('location_airport'), 'value' => 'Aeropuerto de Palma'),
                        array('text' => MET_Translations::t('location_hotel'), 'value' => 'custom_origin')
                    ),
                    'data' => $data,
                    'showBackButton' => true
                );
                
            case 'datetime':
                // Volver a fecha
                unset($data['date'], $data['time'], $data['datetime']);
                return array(
                    'message' => '📅 <strong>' . MET_Translations::t('modify_datetime_title') . '</strong><br><br>' . MET_Translations::t('modify_datetime_question') . '<br><br><em>' . MET_Translations::t('date_format') . '</em>',
                    'nextStep' => 'date',
                    'options' => array(),
                    'data' => $data,
                    'inputType' => 'text',
                    'showBackButton' => true,
                    'placeholder' => 'DD/MM/YYYY'
                );
                
            case 'passengers':
                // Volver a pasajeros
                unset($data['passengers']);
                return array(
                    'message' => '👥 <strong>' . MET_Translations::t('modify_passengers_title') . '</strong><br><br>' . MET_Translations::t('modify_passengers_question'),
                    'nextStep' => 'passengers',
                    'options' => array(),
                    'data' => $data,
                    'inputType' => 'number',
                    'showBackButton' => true
                );
                
            case 'restart':
                // Reiniciar todo
                return $this->restart_conversation();
                
            default:
                return $this->step_summary('', $data);
        }
    }
    
    /**
     * Generar HTML del resumen
     */
    private function generate_summary_html($data, $price_breakdown) {
        MET_Translations::init_from_data($data);
        
        $html = '<div class="met-booking-summary">';
        
        // Ruta
        $html .= '<strong>📍 ' . MET_Translations::t('summary_route') . ':</strong><br>';
        $html .= $data['origin'] . ' <i class="fas fa-arrow-right"></i> ' . $data['destination'] . '<br><br>';
        
        // Fecha y hora
        $html .= '<strong>📅 ' . MET_Translations::t('summary_datetime') . ':</strong><br>';
        $html .= $data['datetime'] . '<br><br>';
        
        // Pasajeros y vehículo
        $html .= '<strong>👥 ' . MET_Translations::t('summary_passengers') . ':</strong> ' . $data['passengers'] . '<br>';
        $html .= '<strong>🚗 ' . MET_Translations::t('summary_vehicle') . ':</strong> ' . $this->get_vehicle_name($price_breakdown['vehicle_type']) . '<br><br>';
        
        // Opciones extras (si existen)
        if (isset($data['extras']) && is_array($data['extras'])) {
            $has_extras = false;
            $extras_html = '<strong>🎁 ' . MET_Translations::t('summary_extras') . ':</strong><br>';
            
            // Extras informativos (gratis)
            if (!empty($data['extras']['equipaje_de_mano']) && $data['extras']['equipaje_de_mano'] > 0) {
                $extras_html .= '🎒 Equipaje de mano: ' . $data['extras']['equipaje_de_mano'] . '<br>';
                $has_extras = true;
            }
            if (!empty($data['extras']['valijas']) && $data['extras']['valijas'] > 0) {
                $extras_html .= '🧳 Valijas: ' . $data['extras']['valijas'] . '<br>';
                $has_extras = true;
            }
            if (!empty($data['extras']['alzadores']) && $data['extras']['alzadores'] > 0) {
                $extras_html .= '🪑 Alzadores: ' . $data['extras']['alzadores'] . '<br>';
                $has_extras = true;
            }
            if (!empty($data['extras']['sillas_bebe']) && $data['extras']['sillas_bebe'] > 0) {
                $extras_html .= '👶 Sillas de bebé: ' . $data['extras']['sillas_bebe'] . '<br>';
                $has_extras = true;
            }
            
            // Extras con costo
            if (!empty($data['extras']['bolsa_golf']['cantidad']) && $data['extras']['bolsa_golf']['cantidad'] > 0) {
                $extras_html .= '⛳ Bolsa de Golf: ' . $data['extras']['bolsa_golf']['cantidad'] . '<br>';
                $has_extras = true;
            }
            if (!empty($data['extras']['bicicleta']['cantidad']) && $data['extras']['bicicleta']['cantidad'] > 0) {
                $extras_html .= '🚴 Bicicleta: ' . $data['extras']['bicicleta']['cantidad'] . '<br>';
                $has_extras = true;
            }
            
            if ($has_extras) {
                $html .= $extras_html . '<br>';
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Obtener nombre del vehículo
     */
    private function get_vehicle_name($vehicle_type) {
        $key = 'vehicle_' . $vehicle_type;
        $translated = MET_Translations::t($key);
        
        // Si no hay traducción, devolver el tipo con formato
        if ($translated === $key) {
            return ucfirst($vehicle_type);
        }
        
        return $translated;
    }
    
    /**
     * Reiniciar conversación
     */
    private function restart_conversation() {
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-conversation-steps-welcome.php';
        $welcome_steps = new MET_Conversation_Steps_Welcome();
        return $welcome_steps->step_welcome();
    }
}
