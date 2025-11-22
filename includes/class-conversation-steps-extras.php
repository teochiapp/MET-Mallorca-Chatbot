<?php
/**
 * Steps de selección de opciones extras (equipaje, sillas, golf, bicicletas)
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Conversation_Steps_Extras {
    
    /**
     * Configuración de extras disponibles
     */
    private $extras_config = array(
        'equipaje_de_mano' => array(
            'label' => 'Equipaje de mano',
            'price' => 0,
            'icon' => '🎒',
            'info' => 'Gratis'
        ),
        'valijas' => array(
            'label' => 'Valijas',
            'price' => 0,
            'icon' => '🧳',
            'info' => 'Gratis'
        ),
        'alzadores' => array(
            'label' => 'Alzadores',
            'price' => 0,
            'icon' => '🪑',
            'info' => 'Gratis'
        ),
        'sillas_bebe' => array(
            'label' => 'Sillas de bebé',
            'price' => 0,
            'icon' => '👶',
            'info' => 'Gratis'
        ),
        'bolsa_golf' => array(
            'label' => 'Bolsa de Golf',
            'price' => 3,
            'icon' => '⛳',
            'info' => '€3 c/u'
        ),
        'bicicleta' => array(
            'label' => 'Bicicleta',
            'price' => 6,
            'icon' => '🚴',
            'info' => '€6 c/u'
        )
    );
    
    /**
     * Step: Opciones Extras
     */
    public function step_extras($message, $data) {
        // Si es la primera vez (sin mensaje del usuario y sin extras previos), mostrar el formulario de extras
        if ($message === '' && !isset($data['extras'])) {
            return $this->show_extras_form($data);
        }
        
        // Si viene del formulario, el mensaje será un JSON con las cantidades.
        // Si el JSON no es válido, asumimos "sin extras" para evitar bucles.
        $extras_data = json_decode($message, true);
        if (!is_array($extras_data)) {
            $extras_data = array();
        }

        // Procesar y guardar las opciones extras
        $processed_extras = $this->process_extras_selection($extras_data);
        $data['extras'] = $processed_extras;

        // Mensaje de resumen de extras seleccionados
        $extras_summary_message = $this->build_extras_summary($processed_extras);

        // Ir directamente al resumen de la reserva y anteponer el resumen de extras
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-conversation-steps-summary.php';
        $summary_steps = new MET_Conversation_Steps_Summary();
        $summary_response = $summary_steps->step_summary('', $data);

        if (isset($summary_response['message'])) {
            $summary_response['message'] = $extras_summary_message . '<br><br>' . $summary_response['message'];
        }

        return $summary_response;
    }
    
    /**
     * Mostrar formulario de opciones extras
     */
    private function show_extras_form($data) {
        $message = '🎁 <strong>Opciones Extras</strong><br><br>' .
                  'Selecciona las opciones adicionales que necesites para tu viaje:';
        
        return array(
            'message' => $message,
            'nextStep' => 'extras',
            'options' => array(),
            'data' => $data,
            'inputType' => 'extras_form',
            'showBackButton' => true,
            'extrasConfig' => $this->extras_config
        );
    }
    
    /**
     * Procesar selección de extras
     */
    private function process_extras_selection($extras_data) {
        $processed = array();
        $total_extras = 0;
        
        foreach ($this->extras_config as $key => $config) {
            $cantidad = isset($extras_data[$key]) ? intval($extras_data[$key]) : 0;
            
            if ($cantidad < 0) {
                $cantidad = 0;
            }
            
            if ($config['price'] > 0) {
                // Extras con precio
                $subtotal = $cantidad * $config['price'];
                $processed[$key] = array(
                    'cantidad' => $cantidad,
                    'precio_unitario' => $config['price'],
                    'subtotal' => $subtotal
                );
                $total_extras += $subtotal;
            } else {
                // Extras gratuitos (solo cantidad)
                $processed[$key] = $cantidad;
            }
        }
        
        $processed['total_extras'] = $total_extras;
        
        return $processed;
    }
    
    /**
     * Construir resumen de extras seleccionados
     */
    private function build_extras_summary($extras) {
        $message = '✅ <strong>Opciones extras confirmadas</strong><br><br>';
        
        $has_extras = false;
        
        // Extras gratuitos
        $free_items = array();
        if (!empty($extras['equipaje_de_mano']) && $extras['equipaje_de_mano'] > 0) {
            $free_items[] = '🎒 Equipaje de mano: ' . $extras['equipaje_de_mano'];
            $has_extras = true;
        }
        if (!empty($extras['valijas']) && $extras['valijas'] > 0) {
            $free_items[] = '🧳 Valijas: ' . $extras['valijas'];
            $has_extras = true;
        }
        if (!empty($extras['alzadores']) && $extras['alzadores'] > 0) {
            $free_items[] = '🪑 Alzadores: ' . $extras['alzadores'];
            $has_extras = true;
        }
        if (!empty($extras['sillas_bebe']) && $extras['sillas_bebe'] > 0) {
            $free_items[] = '👶 Sillas de bebé: ' . $extras['sillas_bebe'];
            $has_extras = true;
        }
        
        if (!empty($free_items)) {
            $message .= implode('<br>', $free_items) . '<br><br>';
        }
        
        // Extras con costo
        $paid_items = array();
        if (!empty($extras['bolsa_golf']['cantidad']) && $extras['bolsa_golf']['cantidad'] > 0) {
            $paid_items[] = '⛳ Bolsa de Golf: ' . $extras['bolsa_golf']['cantidad'] . 
                          ' (€' . number_format($extras['bolsa_golf']['subtotal'], 2) . ')';
            $has_extras = true;
        }
        if (!empty($extras['bicicleta']['cantidad']) && $extras['bicicleta']['cantidad'] > 0) {
            $paid_items[] = '🚴 Bicicleta: ' . $extras['bicicleta']['cantidad'] . 
                          ' (€' . number_format($extras['bicicleta']['subtotal'], 2) . ')';
            $has_extras = true;
        }
        
        if (!empty($paid_items)) {
            $message .= implode('<br>', $paid_items) . '<br><br>';
        }
        
        // Total
        if ($extras['total_extras'] > 0) {
            $message .= '<strong>💰 Total extras: €' . number_format($extras['total_extras'], 2) . '</strong><br><br>';
        }
        
        if (!$has_extras) {
            $message = '✅ <strong>Sin opciones extras</strong><br><br>' .
                      'Continuaremos sin servicios adicionales.<br><br>';
        }
        
        $message .= 'Continuemos con el resumen de tu reserva...';
        
        return $message;
    }
    
    /**
     * Obtener configuración de extras para el frontend
     */
    public function get_extras_config() {
        return $this->extras_config;
    }
}
