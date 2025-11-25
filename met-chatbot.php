<?php
/**
 * Plugin Name: MET Mallorca Chatbot
 * Plugin URI: https://metmallorca.com
 * Description: Asistente inteligente de reservas con formulario embebido, presupuestos y verificación para MET Mallorca
 * Version: 2.0.0
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
define('MET_CHATBOT_VERSION', '2.0.0');
define('MET_CHATBOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MET_CHATBOT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Clase principal del plugin
 */
class MET_Chatbot {
    /**
     * Instancia del generador de checkout
     */
    private $checkout_generator;
    
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
        add_action('wp_ajax_met_get_locations', array($this, 'get_locations'));
        add_action('wp_ajax_nopriv_met_get_locations', array($this, 'get_locations'));
        add_action('wp_ajax_met_get_time_slots', array($this, 'get_time_slots'));
        add_action('wp_ajax_nopriv_met_get_time_slots', array($this, 'get_time_slots'));

        // REST API routes
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    /**
     * Cargar dependencias
     */
    private function load_dependencies() {
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-booking-handler.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-conversation-controller.php';
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-checkout-generator.php';

        // Registrar hooks de checkout cuando WooCommerce esté listo
        if (did_action('woocommerce_init')) {
            $this->register_checkout_hooks();
        } else {
            add_action('woocommerce_init', array($this, 'register_checkout_hooks'));
        }
    }

    /**
     * Inicializar hooks relacionados con el checkout
     */
    public function register_checkout_hooks() {
        if ($this->checkout_generator instanceof MET_Checkout_Generator) {
            return;
        }

        $this->checkout_generator = new MET_Checkout_Generator();
        $this->checkout_generator->init_hooks();
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
        
        // CSS adicional para sistema de reservas
        wp_enqueue_style(
            'met-chatbot-booking-style',
            MET_CHATBOT_PLUGIN_URL . 'assets/css/chatbot-booking.css',
            array('met-chatbot-style'),
            MET_CHATBOT_VERSION
        );
        
        // CSS para selector de extras
        wp_enqueue_style(
            'met-extras-selector-style',
            MET_CHATBOT_PLUGIN_URL . 'assets/css/extras-selector.css',
            array('met-chatbot-style'),
            MET_CHATBOT_VERSION
        );
        
        // JavaScript del location searcher
        wp_enqueue_script(
            'met-location-searcher',
            MET_CHATBOT_PLUGIN_URL . 'assets/js/location-searcher.js',
            array('jquery'),
            MET_CHATBOT_VERSION,
            true
        );
        
        // JavaScript del time searcher
        wp_enqueue_script(
            'met-time-searcher',
            MET_CHATBOT_PLUGIN_URL . 'assets/js/time-searcher.js',
            array('jquery'),
            MET_CHATBOT_VERSION,
            true
        );
        
        // JavaScript del extras selector
        wp_enqueue_script(
            'met-extras-selector',
            MET_CHATBOT_PLUGIN_URL . 'assets/js/extras-selector.js',
            array('jquery'),
            MET_CHATBOT_VERSION,
            true
        );
        
        // JavaScript del chatbot
        wp_enqueue_script(
            'met-chatbot-script',
            MET_CHATBOT_PLUGIN_URL . 'assets/js/chatbot.js',
            array('jquery', 'met-location-searcher', 'met-time-searcher', 'met-extras-selector'),
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
        
        $step = isset($_POST['step']) ? sanitize_text_field($_POST['step']) : 'welcome';
        
        // Para la mayoría de los pasos, tratamos el mensaje como texto plano.
        // Para el paso 'extras', necesitamos el JSON crudo que envía el frontend
        // (no se debe pasar por sanitize_text_field porque rompe la estructura JSON).
        if ($step === 'extras') {
            $message = isset($_POST['message']) ? wp_unslash($_POST['message']) : '';
        } else {
            $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
        }

        $data = isset($_POST['data']) ? json_decode(stripslashes($_POST['data']), true) : array();
        
        // Usar el nuevo controlador conversacional
        $controller = new MET_Conversation_Controller();
        $response = $controller->process_message($message, $step, $data);
        
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
    
    /**
     * Obtener ubicaciones disponibles
     */
    public function get_locations() {
        check_ajax_referer('met_chatbot_nonce', 'nonce');
    
    // Capturar logs de error
    ob_start();
    
    // Inicializar array de depuración
    $debug = [
        'messages' => [],
        'file_path' => MET_CHATBOT_PLUGIN_DIR . 'precios_locations_data.json',
        'file_exists' => file_exists(MET_CHATBOT_PLUGIN_DIR . 'precios_locations_data.json'),
        'is_readable' => is_readable(MET_CHATBOT_PLUGIN_DIR . 'precios_locations_data.json'),
        'file_size' => file_exists(MET_CHATBOT_PLUGIN_DIR . 'precios_locations_data.json') ? filesize(MET_CHATBOT_PLUGIN_DIR . 'precios_locations_data.json') : 0,
    ];
    
    $debug['messages'][] = 'Iniciando carga de ubicaciones...';
    
    // Leer el JSON directamente para verificar su contenido
    $json_file = MET_CHATBOT_PLUGIN_DIR . 'precios_locations_data.json';
    $json_contents = file_get_contents($json_file);
    $raw_data = json_decode($json_contents, true);
    
    $debug['json_decode_error'] = json_last_error_msg();
    $debug['raw_data_count'] = is_array($raw_data) ? count($raw_data) : 0;
    $debug['raw_data_keys'] = is_array($raw_data) ? array_slice(array_keys($raw_data), 0, 5) : [];
    
    require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-pricing-engine.php';
    $pricing_engine = new MET_Pricing_Engine();
    
    // Forzar la recarga de las ubicaciones
    $reflection = new ReflectionClass($pricing_engine);
    $property = $reflection->getProperty('location_prices');
    $property->setAccessible(true);
    $current_prices = $property->getValue($pricing_engine);
    
    $debug['location_prices_count'] = is_array($current_prices) ? count($current_prices) : 0;
    $debug['location_prices_keys'] = is_array($current_prices) ? array_slice(array_keys($current_prices), 0, 5) : [];
    $debug['messages'][] = 'Estado de location_prices: ' . (empty($current_prices) ? 'vacío' : count($current_prices) . ' elementos');
    
    // Obtener las ubicaciones
    $locations = $pricing_engine->get_all_locations();
    
    // Obtener información de depuración adicional
    $debug['location_count'] = count($locations);
    $debug['first_five_locations'] = array_slice($locations, 0, 5);
    
    // Incluir información de la ruta para depuración
    $debug['plugin_dir'] = MET_CHATBOT_PLUGIN_DIR;
    $debug['full_path'] = realpath($json_file);
    
    // Capturar cualquier salida de error
    $error_output = ob_get_clean();
    if (!empty($error_output)) {
        $debug['error_output'] = $error_output;
    }
    
    // Devolver la respuesta con información de depuración
        wp_send_json_success([
            'locations' => $locations,
            'total' => count($locations),
            'debug' => $debug
        ]);
    }

    /**
     * Obtener horarios disponibles en intervalos de 30 minutos
     */
    public function get_time_slots() {
        check_ajax_referer('met_chatbot_nonce', 'nonce');

        $time_slots = array();
        $file_path = MET_CHATBOT_PLUGIN_DIR . 'time_slots_data.json';

        if (file_exists($file_path) && is_readable($file_path)) {
            $json_contents = file_get_contents($file_path);
            $decoded = json_decode($json_contents, true);

            if (is_array($decoded)) {
                foreach ($decoded as $slot) {
                    $slot = trim((string) $slot);
                    if (preg_match('/^\d{2}:\d{2}$/', $slot)) {
                        $time_slots[] = $slot;
                    }
                }
            }
        }

        if (empty($time_slots)) {
            for ($hour = 0; $hour < 24; $hour++) {
                foreach (array(0, 30) as $minute) {
                    $time_slots[] = sprintf('%02d:%02d', $hour, $minute);
                }
            }
        }

        wp_send_json_success(array(
            'time_slots' => $time_slots,
            'total' => count($time_slots)
        ));
    }

    /**
     * Registrar rutas REST personalizadas
     */
    public function register_rest_routes() {
        register_rest_route(
            'met-chatbot/v1',
            '/test-pricing',
            array(
                'methods' => \WP_REST_Server::READABLE,
                'callback' => array($this, 'rest_test_pricing'),
                'permission_callback' => '__return_true'
            )
        );
    }

    /**
     * Endpoint REST para probar cálculos de precios
     *
     * Ejemplo: /wp-json/met-chatbot/v1/test-pricing?locations=Palma,Alcudia&passengers=2,6
     *
     * @param \WP_REST_Request $request
     * @return array|\WP_Error
     */
    public function rest_test_pricing( $request ) {
        require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-pricing-engine.php';

        $pricing_engine = new MET_Pricing_Engine();
        $available_locations = $pricing_engine->get_all_locations();

        $locations_param = $request->get_param('locations');
        $max_locations = intval($request->get_param('max_locations'));
        if ($max_locations <= 0) {
            $max_locations = 10;
        } elseif ($max_locations > 50) {
            $max_locations = 50;
        }

        $selected_locations = array();
        $unmatched_locations = array();

        if (!empty($locations_param)) {
            $requested_locations = is_array($locations_param)
                ? $locations_param
                : explode(',', $locations_param);

            foreach ($requested_locations as $raw_location) {
                $raw_location = sanitize_text_field(wp_unslash($raw_location));
                $match = $this->match_available_location($raw_location, $available_locations);

                if ($match !== null) {
                    $selected_locations[$match] = array(
                        'requested' => $raw_location,
                        'matched' => $match
                    );
                } else {
                    $unmatched_locations[] = $raw_location;
                }
            }
        }

        if (empty($selected_locations)) {
            $limited_locations = array_slice($available_locations, 0, $max_locations);
            foreach ($limited_locations as $location_name) {
                $selected_locations[$location_name] = array(
                    'requested' => $location_name,
                    'matched' => $location_name
                );
            }
        }

        $passengers_param = $request->get_param('passengers');
        $passenger_values = array();

        if (!empty($passengers_param)) {
            $values = is_array($passengers_param)
                ? $passengers_param
                : explode(',', $passengers_param);

            foreach ($values as $value) {
                $value = intval($value);
                if ($value > 0 && $value <= 50) {
                    $passenger_values[] = $value;
                }
            }
        }

        if (empty($passenger_values)) {
            $passenger_values = array(1, 4, 6, 8);
        }

        $origin = $request->get_param('origin');
        $origin = !empty($origin) ? sanitize_text_field(wp_unslash($origin)) : 'Aeropuerto de Palma';

        $datetime = $request->get_param('datetime');
        if (empty($datetime)) {
            // Fijar fecha y hora diurna para evitar suplemento nocturno
            $datetime = date('Y-m-d 12:00:00');
        } else {
            $datetime = sanitize_text_field(wp_unslash($datetime));
        }

        $include_breakdown = filter_var($request->get_param('include_breakdown'), FILTER_VALIDATE_BOOLEAN);

        $results = array();

        foreach ($selected_locations as $location_name => $meta) {
            $location_results = array();

            foreach ($passenger_values as $passengers) {
                $booking_data = array(
                    'origin' => $origin,
                    'destination' => $location_name,
                    'passengers' => $passengers,
                    'datetime' => $datetime,
                    'extras' => array()
                );

                $calculation = $pricing_engine->calculate_price($booking_data);

                $entry = array(
                    'passengers' => $passengers,
                    'base_price' => $calculation['base_price'],
                    'total' => $calculation['total'],
                    'pricing_method' => $calculation['pricing_method'],
                    'vehicle_type' => $calculation['vehicle_type']
                );

                if ($include_breakdown) {
                    $entry['breakdown'] = $calculation;
                }

                $location_results[] = $entry;
            }

            $results[] = array(
                'location' => $location_name,
                'requested' => $meta['requested'],
                'matches' => $meta['matched'],
                'passengers' => $location_results
            );
        }

        $response = array(
            'origin' => $origin,
            'datetime' => $datetime,
            'passenger_counts' => $passenger_values,
            'locations_requested' => array_values($selected_locations),
            'total_locations_tested' => count($results),
            'available_locations' => count($available_locations),
            'results' => $results
        );

        if (!empty($unmatched_locations)) {
            $response['unmatched_locations'] = $unmatched_locations;
        }

        return rest_ensure_response($response);
    }

    /**
     * Intentar emparejar una ubicación solicitada con las disponibles
     *
     * @param string $requested
     * @param array $available
     * @return string|null
     */
    private function match_available_location($requested, $available) {
        if ($requested === '') {
            return null;
        }

        foreach ($available as $candidate) {
            if (strcasecmp($candidate, $requested) === 0) {
                return $candidate;
            }
        }

        $requested_lower = strtolower($requested);

        foreach ($available as $candidate) {
            if (strpos(strtolower($candidate), $requested_lower) !== false) {
                return $candidate;
            }
        }

        return null;
    }
}

// Inicializar el plugin
function met_chatbot_init() {
    new MET_Chatbot();
}
add_action('plugins_loaded', 'met_chatbot_init');
