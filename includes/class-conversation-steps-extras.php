<?php
/**
 * Steps de selección de opciones extras (equipaje, sillas, golf, bicicletas)
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Conversation_Steps_Extras {
    
    private $translations;
    
    public function __construct() {
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-translations.php';
        $this->translations = new MET_Translations();
    }
    
    /**
     * Configuración de extras disponibles
     */
    private $extras_config = array(
        'equipaje_de_mano' => array(
            'label_key' => 'extras_hand_luggage',
            'price' => 0,
            'icon' => '🎒'
        ),
        'valijas' => array(
            'label_key' => 'extras_suitcases',
            'price' => 0,
            'icon' => '🧳'
        ),
        'alzadores' => array(
            'label_key' => 'extras_booster_seats',
            'price' => 0,
            'icon' => '🪑'
        ),
        'sillas_bebe' => array(
            'label_key' => 'extras_baby_seats',
            'price' => 0,
            'icon' => '👶'
        ),
        'bolsa_golf' => array(
            'label_key' => 'extras_golf_bag',
            'price' => 3,
            'icon' => '⛳'
        ),
        'bicicleta' => array(
            'label_key' => 'extras_bicycle',
            'price' => 6,
            'icon' => '🚴'
        )
    );
    
    /**
     * Step: Opciones Extras
     */
    public function step_extras($message, $data) {
        MET_Translations::init_from_data($data);
        
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
        MET_Translations::init_from_data($data);
        
        $message = '🎁 <strong>' . MET_Translations::t('extras_title') . '</strong><br><br>' .
                  MET_Translations::t('extras_message');
        
        return array(
            'message' => $message,
            'nextStep' => 'extras',
            'options' => array(),
            'data' => $data,
            'inputType' => 'extras_form',
            'showBackButton' => true,
            'extrasConfig' => $this->get_translated_extras_config($data)
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
        $message = '✅ <strong>' . MET_Translations::t('extras_selected_summary') . '</strong><br><br>';
        
        $has_extras = false;
        
        // Extras gratuitos
        $free_items = array();
        $free_keys = array('equipaje_de_mano', 'valijas', 'alzadores', 'sillas_bebe');
        foreach ($free_keys as $key) {
            if (!empty($extras[$key]) && $extras[$key] > 0) {
                $free_items[] = $this->format_extra_summary_line($key, $extras[$key]);
                $has_extras = true;
            }
        }
        
        if (!empty($free_items)) {
            $message .= implode('<br>', $free_items) . '<br><br>';
        }
        
        // Extras con costo
        $paid_items = array('bolsa_golf', 'bicicleta');
        $paid_lines = array();
        foreach ($paid_items as $key) {
            if (!empty($extras[$key]['cantidad']) && $extras[$key]['cantidad'] > 0) {
                $paid_lines[] = $this->format_extra_summary_line(
                    $key,
                    $extras[$key]['cantidad'],
                    $extras[$key]['subtotal']
                );
                $has_extras = true;
            }
        }
        
        if (!empty($paid_lines)) {
            $message .= implode('<br>', $paid_lines) . '<br><br>';
        }
        
        // Total
        if (!empty($extras['total_extras']) && $extras['total_extras'] > 0) {
            $message .= '<strong>💰 ' . MET_Translations::t('extras_total') . ': €' . number_format($extras['total_extras'], 2) . '</strong><br><br>';
        }
        
        if (!$has_extras) {
            $message = '✅ <strong>' . MET_Translations::t('extras_none') . '</strong><br><br>' .
                      MET_Translations::t('extras_none_message') . '<br><br>';
        }
        
        $message .= MET_Translations::t('extras_continue');
        
        return $message;
    }
    
    /**
     * Obtener configuración de extras para el frontend
     */
    public function get_extras_config() {
        return $this->extras_config;
    }

    /**
     * Obtener configuración traducida para el frontend
     */
    private function get_translated_extras_config($data) {
        MET_Translations::init_from_data($data);
        $config = array();

        $free_badge_allowed = array('alzadores', 'sillas_bebe');

        foreach ($this->extras_config as $key => $extra) {
            $label = MET_Translations::t($extra['label_key']);
            if ($extra['price'] > 0) {
                $info = '€' . $extra['price'] . ' ' . MET_Translations::t('extras_each');
            } else {
                $info = in_array($key, $free_badge_allowed, true)
                    ? '<span class="met-extras-free">' . MET_Translations::t('extras_free') . '</span>'
                    : '';
            }

            $config[$key] = array(
                'label' => $label,
                'price' => $extra['price'],
                'icon' => $extra['icon'],
                'info' => $info
            );
        }

        return $config;
    }

    /**
     * Formatear línea de resumen
     */
    private function format_extra_summary_line($key, $quantity, $subtotal = null) {
        $label = isset($this->extras_config[$key]['label_key'])
            ? MET_Translations::t($this->extras_config[$key]['label_key'])
            : ucfirst($key);
        $icon = isset($this->extras_config[$key]['icon']) ? $this->extras_config[$key]['icon'] : '•';

        $line = $icon . ' ' . $label . ': ' . $quantity;

        if (!is_null($subtotal)) {
            $line .= ' (€' . number_format($subtotal, 2) . ')';
        }

        return $line;
    }
}
