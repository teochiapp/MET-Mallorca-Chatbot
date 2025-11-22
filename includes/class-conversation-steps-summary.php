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
    
    public function __construct() {
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-pricing-engine.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-checkout-generator.php';
        
        $this->pricing_engine = new MET_Pricing_Engine();
        $this->checkout_generator = new MET_Checkout_Generator();
    }
    
    /**
     * Step: Resumen y cálculo de precio
     */
    public function step_summary($message, $data) {
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
                'message' => '❌ <strong>Error:</strong> Faltan datos necesarios para calcular el precio.<br><br>' .
                            'Campos faltantes: ' . implode(', ', $missing_fields) . '<br><br>' .
                            'Por favor, reinicia la conversación.',
                'nextStep' => 'welcome',
                'options' => array(
                    array('text' => '🔄 Reiniciar', 'value' => 'restart')
                ),
                'data' => array()
            );
        }
        
        // Calcular precio
        try {
            $price_breakdown = $this->pricing_engine->calculate_price($data);
        } catch (Exception $e) {
            return array(
                'message' => '❌ <strong>Error al calcular el precio:</strong><br><br>' .
                            $e->getMessage() . '<br><br>' .
                            'Por favor, intenta de nuevo.',
                'nextStep' => 'welcome',
                'options' => array(
                    array('text' => '🔄 Reiniciar', 'value' => 'restart')
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
        
        $message = '✅ <strong>Resumen de tu Reserva</strong><br><br>' .
                   $summary_html . '<br>' .
                   $price_html . '<br><br>' .
                   '¿Todo correcto?';
        
        return array(
            'message' => $message,
            'nextStep' => 'confirm',
            'options' => array(
                array(
                    'text' => '<i class="fas fa-check-circle"></i> Sí, continuar al checkout',
                    'value' => 'confirm'
                ),
                array(
                    'text' => '<i class="fas fa-edit"></i> Modificar datos',
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
        if ($message === 'modify') {
            return array(
                'message' => '✏️ <strong>¿Qué deseas modificar?</strong>',
                'nextStep' => 'modify_choice',
                'options' => array(
                    array('text' => '📍 Origen/Destino', 'value' => 'locations'),
                    array('text' => '📅 Fecha/Hora', 'value' => 'datetime'),
                    array('text' => '👥 Pasajeros', 'value' => 'passengers'),
                    array('text' => '🔄 Empezar de nuevo', 'value' => 'restart')
                ),
                'data' => $data,
                'showBackButton' => true
            );
        }
        
        // Generar URL de checkout
        if (!isset($data['price_breakdown'])) {
            return array(
                'message' => '❌ Error: No se pudo calcular el precio. Por favor, intenta de nuevo.',
                'nextStep' => 'welcome',
                'options' => array(
                    array('text' => '🔄 Empezar de nuevo', 'value' => 'restart')
                ),
                'data' => array()
            );
        }
        
        $checkout_url = $this->checkout_generator->generate_checkout_url($data, $data['price_breakdown']);
        
        $message = '🎉 <strong>¡Perfecto!</strong><br><br>' .
                   'Tu reserva está lista. Haz clic en el botón de abajo para ir al checkout seguro y completar el pago.<br><br>' .
                   '<div class="met-checkout-link-container">' .
                   '<a href="' . esc_url($checkout_url) . '" class="met-checkout-link" target="_blank">' .
                   '<i class="fas fa-shopping-cart"></i> Ir al Checkout (€' . number_format($data['price_breakdown']['total'], 2) . ')' .
                   '</a>' .
                   '</div><br>' .
                   '<small>💳 Pago seguro con Redsys/Getnet a través de WooCommerce</small><br>' .
                   '<small>🔒 Tus datos están protegidos</small>';
        
        return array(
            'message' => $message,
            'nextStep' => 'end',
            'options' => array(
                array('text' => '🔄 Hacer otra reserva', 'value' => 'restart')
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
        switch ($message) {
            case 'locations':
                // Volver a origen
                unset($data['origin'], $data['destination']);
                return array(
                    'message' => '📍 <strong>Modificar Ubicaciones</strong><br><br>¿Desde dónde te recogemos?',
                    'nextStep' => 'origin',
                    'options' => array(
                        array('text' => '<i class="fas fa-plane"></i> Aeropuerto de Palma (PMI)', 'value' => 'Aeropuerto de Palma'),
                        array('text' => '<i class="fas fa-hotel"></i> Hotel / Alojamiento', 'value' => 'custom_origin')
                    ),
                    'data' => $data,
                    'showBackButton' => true
                );
                
            case 'datetime':
                // Volver a fecha
                unset($data['date'], $data['time'], $data['datetime']);
                return array(
                    'message' => '📅 <strong>Modificar Fecha y Hora</strong><br><br>¿Qué día necesitas el traslado?<br><br><em>Formato: DD/MM/YYYY</em>',
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
                    'message' => '👥 <strong>Modificar Pasajeros</strong><br><br>¿Cuántas personas viajan?',
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
        $html = '<div class="met-booking-summary">';
        
        // Ruta
        $html .= '<strong>📍 Ruta:</strong><br>';
        $html .= $data['origin'] . ' <i class="fas fa-arrow-right"></i> ' . $data['destination'] . '<br><br>';
        
        // Fecha y hora
        $html .= '<strong>📅 Fecha y Hora:</strong><br>';
        $html .= $data['datetime'] . '<br><br>';
        
        // Pasajeros y vehículo
        $html .= '<strong>👥 Pasajeros:</strong> ' . $data['passengers'] . '<br>';
        $html .= '<strong>🚗 Vehículo:</strong> ' . $this->get_vehicle_name($price_breakdown['vehicle_type']) . '<br><br>';
        
        // Opciones extras (si existen)
        if (isset($data['extras']) && is_array($data['extras'])) {
            $has_extras = false;
            $extras_html = '<strong>🎁 Opciones Extras:</strong><br>';
            
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
        $names = array(
            'standard' => 'Vehículo Estándar (1-4 pax)',
            'van' => 'Van (5-8 pax)',
            'minibus' => 'Minibus (9-16 pax)',
            'bus' => 'Bus (17-20 pax)'
        );
        
        return isset($names[$vehicle_type]) ? $names[$vehicle_type] : 'Vehículo';
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
