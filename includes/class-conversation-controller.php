<?php
/**
 * Controlador principal del flujo conversacional
 * Orquesta todos los módulos y maneja el FSM (Finite State Machine)
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Conversation_Controller {
    
    /**
     * Módulos de steps
     */
    private $welcome_steps;
    private $location_steps;
    private $details_steps;
    private $extras_steps;
    private $summary_steps;
    
    /**
     * Validador
     */
    private $validator;
    
    /**
     * Estados válidos del FSM
     */
    private $valid_states = array(
        'welcome', 'route_type', 'origin', 'origin_text', 'destination', 'destination_text',
        'date', 'time', 'passengers', 'pet', 'flight_number', 'extras', 'summary', 'confirm',
        'modify_choice', 'verify_booking_code', 'verify_result', 'end'
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->load_modules();
    }
    
    /**
     * Cargar todos los módulos
     */
    private function load_modules() {
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-translations.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-booking-validator.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-conversation-steps-welcome.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-conversation-steps-location.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-conversation-steps-details.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-conversation-steps-extras.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-conversation-steps-summary.php';
        
        $this->validator = new MET_Booking_Validator();
        $this->welcome_steps = new MET_Conversation_Steps_Welcome();
        $this->location_steps = new MET_Conversation_Steps_Location();
        $this->details_steps = new MET_Conversation_Steps_Details();
        $this->extras_steps = new MET_Conversation_Steps_Extras();
        $this->summary_steps = new MET_Conversation_Steps_Summary();
    }
    
    /**
     * Procesar mensaje del usuario
     * 
     * @param string $message Mensaje del usuario
     * @param string $step Estado actual del FSM
     * @param array $data Datos acumulados de la conversación
     * @return array Respuesta con siguiente paso y opciones
     */
    public function process_message($message, $step, $data) {
        // Sanitizar entrada
        $message = trim($message);
        $step = sanitize_text_field($step);
        $current_language = isset($data['language']) ? sanitize_text_field($data['language']) : null;
        
        // Validar estado
        if (!in_array($step, $this->valid_states)) {
            $step = 'welcome';
            $data = array();
        }

        if ($current_language && empty($data['language'])) {
            $data['language'] = $current_language;
        }
        
        // Comandos especiales globales
        $special_command = $this->handle_special_commands($message, $step, $data);
        if ($special_command) {
            return $special_command;
        }
        
        // Delegar al módulo correspondiente
        return $this->delegate_to_module($message, $step, $data);
    }
    
    /**
     * Manejar comandos especiales (volver, reiniciar, ayuda)
     */
    private function handle_special_commands($message, $step, $data) {
        $message_lower = strtolower($message);
        
        // Comando: Reiniciar
        if (in_array($message_lower, array('reiniciar', 'empezar de nuevo', 'restart', 'reset'))) {
            return $this->welcome_steps->step_welcome('', $data);
        }
        
        // Comando: Volver atrás
        if (in_array($message_lower, array('volver', 'atrás', 'back'))) {
            return $this->go_back($step, $data);
        }
        
        // Comando: Ayuda
        if (in_array($message_lower, array('ayuda', 'help', '?'))) {
            return $this->show_help($step, $data);
        }
        
        return null;
    }
    
    /**
     * Delegar procesamiento al módulo correcto
     */
    private function delegate_to_module($message, $step, $data) {
        // Módulo de bienvenida
        if (in_array($step, array('welcome', 'route_type'))) {
            if ($step === 'welcome') {
                return $this->welcome_steps->step_welcome($message, $data);
            }
            return $this->welcome_steps->step_route_type($message, $data);
        }
        
        // Módulo de ubicaciones
        if (in_array($step, array('origin', 'origin_text', 'destination', 'destination_text'))) {
            $method = 'step_' . $step;
            if (method_exists($this->location_steps, $method)) {
                return $this->location_steps->$method($message, $data);
            }
        }
        
        // Módulo de detalles
        if (in_array($step, array('date', 'time', 'passengers'))) {
            $method = 'step_' . $step;
            if (method_exists($this->details_steps, $method)) {
                return $this->details_steps->$method($message, $data);
            }
        }
        
        // Módulo de extras
        if ($step === 'extras') {
            return $this->extras_steps->step_extras($message, $data);
        }
        
        // Módulo de resumen
        if (in_array($step, array('summary', 'confirm', 'modify_choice'))) {
            // Para el paso summary, si el mensaje está vacío, es un auto-avance
            if ($step === 'summary' && empty($message)) {
                return $this->summary_steps->step_summary('', $data);
            }
            
            $method = 'step_' . $step;
            if (method_exists($this->summary_steps, $method)) {
                return $this->summary_steps->$method($message, $data);
            }
        }
        
        // Verificación de reserva
        if ($step === 'verify_booking_code') {
            return $this->handle_booking_verification($message, $data);
        }
        
        // Resultado de verificación (para manejar opciones post-verificación)
        if ($step === 'verify_result') {
            if ($message === 'verify') {
                return $this->welcome_steps->step_route_type('verify', array());
            }
            if ($message === 'restart') {
                return $this->welcome_steps->step_welcome('', array());
            }
            return $this->welcome_steps->step_welcome('', array());
        }
        
        // Estado final
        if ($step === 'end') {
            if ($message === 'restart') {
                return $this->welcome_steps->step_welcome();
            }
            return array(
                'message' => '👋 ¡Gracias por usar MET Mallorca!<br><br>¿Necesitas algo más?',
                'nextStep' => 'welcome',
                'options' => array(
                    array('text' => '🔄 Nueva reserva', 'value' => 'restart')
                ),
                'data' => $data
            );
        }
        
        // Fallback: volver al inicio
        return $this->welcome_steps->step_welcome('', $data);
    }
    
    /**
     * Navegar hacia atrás en el flujo
     */
    private function go_back($current_step, $data) {
        // Mapeo de navegación inversa
        $back_navigation = array(
            'route_type' => 'welcome',
            'origin' => 'route_type',
            'origin_text' => 'route_type',
            'destination' => 'origin',
            'destination_text' => 'origin',
            'date' => 'destination',
            'time' => 'date',
            'passengers' => 'time',
            'extras' => 'passengers',
            'summary' => 'extras',
            'confirm' => 'summary',
            'modify_choice' => 'summary'
        );
        
        $previous_step = isset($back_navigation[$current_step]) ? $back_navigation[$current_step] : 'welcome';
        
        // Limpiar datos del paso actual
        $this->clean_step_data($current_step, $data);
        
        // Obtener respuesta del paso anterior
        $response = $this->delegate_to_module('', $previous_step, $data);
        
        // Agregar mensaje de navegación
        if (isset($response['message'])) {
            $response['message'] = '⬅️ <em>Volviendo atrás...</em><br><br>' . $response['message'];
        }
        
        return $response;
    }
    
    /**
     * Limpiar datos de un paso específico
     */
    private function clean_step_data($step, &$data) {
        $step_data_map = array(
            'route_type' => array('route_type'),
            'origin' => array('origin'),
            'origin_text' => array('origin'),
            'destination' => array('destination'),
            'destination_text' => array('destination'),
            'date' => array('date'),
            'time' => array('time', 'datetime'),
            'passengers' => array('passengers'),
            'summary' => array('price_breakdown')
        );
        
        if (isset($step_data_map[$step])) {
            foreach ($step_data_map[$step] as $key) {
                unset($data[$key]);
            }
        }
    }
    
    /**
     * Mostrar ayuda contextual
     */
    private function show_help($current_step, $data) {
        $help_messages = array(
            'welcome' => 'Selecciona el tipo de traslado que necesitas.',
            'origin' => 'Indica desde dónde te recogemos.',
            'destination' => 'Indica tu destino.',
            'date' => 'Escribe la fecha en formato DD/MM/YYYY (ej: 25/12/2025)',
            'time' => 'Escribe la hora en formato HH:MM (ej: 14:30)',
            'passengers' => 'Indica cuántas personas viajan (número)',
            'summary' => 'Revisa los datos y confirma para continuar.'
        );
        
        $help_text = isset($help_messages[$current_step]) 
            ? $help_messages[$current_step] 
            : 'Sigue las instrucciones en pantalla.';
        
        return array(
            'message' => '❓ <strong>Ayuda</strong><br><br>' . $help_text . '<br><br>Comandos disponibles:<br>' .
                        '• <code>volver</code> - Ir al paso anterior<br>' .
                        '• <code>reiniciar</code> - Empezar de nuevo<br>' .
                        '• <code>ayuda</code> - Mostrar esta ayuda',
            'nextStep' => $current_step,
            'options' => array(
                array('text' => '✅ Continuar', 'value' => 'continue')
            ),
            'data' => $data,
            'showBackButton' => false
        );
    }
    
    /**
     * Manejar verificación de reserva
     */
    private function handle_booking_verification($message, $data) {
        $booking_code = sanitize_text_field(trim($message));
        
        // Validar formato básico
        if (empty($booking_code)) {
            return array(
                'message' => '❌ <strong>Código vacío</strong><br><br>' .
                            'Por favor, ingresa un código de reserva válido.<br><br>' .
                            '<em>Ejemplo: MET-1234</em>',
                'nextStep' => 'verify_booking_code',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text',
                'showBackButton' => true
            );
        }
        
        // Verificar con el nuevo sistema
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-booking-verifier.php';
        $result = MET_Booking_Verifier::verify_code_data($booking_code);
        
        if ($result['verified']) {
            $order = $result['order'];
            
            // Formatear mensaje de éxito estilo chat
            $items_text = isset($order['items_text']) ? $order['items_text'] : '';
            if (empty($items_text) && !empty($order['items'])) {
                $items_text = implode(', ', $order['items']);
            }

            $success_message = '✅ <strong>¡Reserva encontrada!</strong><br><br>' .
                '<div style="background:#f2fff5;border-left:4px solid #28a745;padding:15px;border-radius:8px;margin:10px 0;">' .
                '<p style="margin:0 0 8px;"><strong>📋 Código:</strong> ' . esc_html($order['code']) . '</p>' .
                '<p style="margin:0 0 8px;"><strong>🛒 Producto(s):</strong> ' . esc_html($items_text) . '</p>' .
                '<p style="margin:0 0 8px;"><strong>👤 Cliente:</strong> ' . esc_html($order['customer']) . '</p>' .
                '<p style="margin:0 0 8px;"><strong>📅 Fecha de realización del pedido:</strong> ' . esc_html($order['date']) . '</p>' .
                '<p style="margin:0 0 8px;"><strong>💰 Total:</strong> ' . esc_html($order['total_text']) . '</p>' .
                '<p style="margin:0;"><strong>📊 Estado:</strong> ' . esc_html($order['status_label']) . '</p>' .
                '</div>';
            
            $options = array(
                array('text' => '🏠 Volver al inicio', 'value' => 'restart')
            );
            
            return array(
                'message' => $success_message,
                'nextStep' => 'verify_result',
                'options' => $options,
                'data' => array(),
                'showBackButton' => false
            );
        } else {
            return array(
                'message' => '❌ <strong>Reserva no encontrada</strong><br><br>' . esc_html($result['message']) . '<br><br>¿Quieres intentar de nuevo?',
                'nextStep' => 'route_type',
                'options' => array(
                    array('text' => '🔄 Intentar de nuevo', 'value' => 'verify'),
                    array('text' => '🏠 Volver al inicio', 'value' => 'restart')
                ),
                'data' => $data,
                'showBackButton' => false
            );
        }
    }
    
    /**
     * Mostrar todos los pedidos (debug)
     */
    private function show_debug_orders($data) {
        if (!function_exists('wc_get_orders')) {
            return array(
                'message' => '❌ WooCommerce no está disponible.',
                'nextStep' => 'verify_result',
                'options' => array(
                    array('text' => '🏠 Volver al inicio', 'value' => 'restart')
                ),
                'data' => array()
            );
        }
        
        $orders = wc_get_orders(array(
            'limit' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
        ));
        
        if (empty($orders)) {
            return array(
                'message' => '📦 <strong>No hay pedidos en el sistema</strong><br><br>Aún no se han creado pedidos.',
                'nextStep' => 'verify_result',
                'options' => array(
                    array('text' => '🏠 Volver al inicio', 'value' => 'restart')
                ),
                'data' => array()
            );
        }
        
        $message = '🐞 <strong>Debug: Últimos 10 pedidos</strong><br><br>';
        $message .= '<div style="background:#fff3cd;border-left:4px solid #ffc107;padding:15px;border-radius:8px;margin:10px 0;">';
        
        foreach ($orders as $order) {
            $order_id = $order->get_id();
            $status = $order->get_status();
            $total = $order->get_formatted_order_total();
            $date = $order->get_date_created()->date('d/m/Y H:i');
            $customer = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
            
            $message .= '<p style="margin:0 0 10px;padding-bottom:10px;border-bottom:1px solid #e0e0e0;">';
            $message .= '<strong>MET-' . $order_id . '</strong><br>';
            $message .= '<small>Cliente: ' . esc_html($customer ?: 'Sin nombre') . '<br>';
            $message .= 'Estado: ' . esc_html($status) . ' | Total: ' . wp_strip_all_tags($total) . '<br>';
            $message .= 'Fecha: ' . esc_html($date) . '</small>';
            $message .= '</p>';
        }
        
        $message .= '</div>';
        
        return array(
            'message' => $message,
            'nextStep' => 'verify_result',
            'options' => array(
                array('text' => '🔍 Verificar una reserva', 'value' => 'verify'),
                array('text' => '🏠 Volver al inicio', 'value' => 'restart')
            ),
            'data' => array()
        );
    }
}
