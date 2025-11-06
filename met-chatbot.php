<?php
/**
 * Plugin Name: MET Mallorca Chatbot
 * Plugin URI: https://metmallorca.com
 * Description: Asistente inteligente de reservas, presupuestos y verificación para MET Mallorca
 * Version: 1.0.0
 * Author: MET Mallorca
 * Author URI: https://metmallorca.com
 * License: GPL v2 or later
 * Text Domain: met-chatbot
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('MET_CHATBOT_VERSION', '1.0.0');
define('MET_CHATBOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MET_CHATBOT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Clase principal del plugin
 */
class MET_Chatbot {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Cargar archivos necesarios
        $this->load_dependencies();
        
        // Hooks de WordPress
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'render_chatbot'));
        
        // AJAX handlers
        add_action('wp_ajax_met_chatbot_message', array($this, 'handle_message'));
        add_action('wp_ajax_nopriv_met_chatbot_message', array($this, 'handle_message'));
        add_action('wp_ajax_met_verify_booking', array($this, 'verify_booking'));
        add_action('wp_ajax_nopriv_met_verify_booking', array($this, 'verify_booking'));
    }
    
    /**
     * Cargar dependencias
     */
    private function load_dependencies() {
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-booking-handler.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-conversation-flow.php';
    }
    
    /**
     * Cargar scripts y estilos
     */
    public function enqueue_scripts() {
        // Font Awesome
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            array(),
            '6.4.0'
        );
        
        // CSS del chatbot
        wp_enqueue_style(
            'met-chatbot-style',
            MET_CHATBOT_PLUGIN_URL . 'assets/css/chatbot.css',
            array('font-awesome'),
            MET_CHATBOT_VERSION
        );
        
        // JavaScript del chatbot
        wp_enqueue_script(
            'met-chatbot-script',
            MET_CHATBOT_PLUGIN_URL . 'assets/js/chatbot.js',
            array('jquery'),
            MET_CHATBOT_VERSION,
            true
        );
        
        // Pasar datos al JavaScript
        wp_localize_script('met-chatbot-script', 'metChatbot', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('met_chatbot_nonce')
        ));
    }
    
    /**
     * Renderizar el HTML del chatbot
     */
    public function render_chatbot() {
        include MET_CHATBOT_PLUGIN_DIR . 'templates/chatbot-widget.php';
    }
    
    /**
     * Manejar mensajes del chatbot
     */
    public function handle_message() {
        check_ajax_referer('met_chatbot_nonce', 'nonce');
        
        $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
        $step = isset($_POST['step']) ? sanitize_text_field($_POST['step']) : 'welcome';
        $data = isset($_POST['data']) ? json_decode(stripslashes($_POST['data']), true) : array();
        
        $conversation = new MET_Conversation_Flow();
        $response = $conversation->process_message($message, $step, $data);
        
        wp_send_json_success($response);
    }
    
    /**
     * Verificar reserva
     */
    public function verify_booking() {
        check_ajax_referer('met_chatbot_nonce', 'nonce');
        
        $booking_code = isset($_POST['booking_code']) ? sanitize_text_field($_POST['booking_code']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        
        $booking_handler = new MET_Booking_Handler();
        $result = $booking_handler->verify_booking($booking_code, $email);
        
        wp_send_json_success($result);
    }
}

// Inicializar el plugin
function met_chatbot_init() {
    new MET_Chatbot();
}
add_action('plugins_loaded', 'met_chatbot_init');
