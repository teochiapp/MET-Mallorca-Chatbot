<?php
/**
 * Generador de URLs de checkout de WooCommerce
 * Crea enlaces directos al checkout con productos y datos prellenados
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Checkout_Generator {
    
    /**
     * ID del producto de WooCommerce para traslados
     * Este producto debe existir en tu tienda
     */
    private $transfer_product_id;
    private $default_image_url = 'https://i0.wp.com/metmallorca.com/wp-content/uploads/2024/10/Banner-home2-e1730723844783.png?fit=1437%2C708&ssl=1';
    private $production_checkout_url = 'https://metmallorca.com/es/finalizar-compra/';
    private $default_image_source_option = 'met_chatbot_default_booking_image_source';
    
    /**
     * Constructor
     */
    public function __construct() {
        // Obtener ID del producto desde opciones o usar uno por defecto
        $this->transfer_product_id = get_option('met_chatbot_transfer_product_id', 0);
        
        // Si no existe el producto, intentar crearlo automáticamente
        if (!$this->transfer_product_id || !$this->product_exists($this->transfer_product_id)) {
            $this->transfer_product_id = $this->create_transfer_product();
        }
    }
    
    /**
     * Generar URL de checkout con todos los datos de la reserva
     * 
     * @param array $booking_data Datos de la reserva
     * @param array $price_breakdown Desglose de precio
     * @return string URL del checkout
     */
    public function generate_checkout_url($booking_data, $price_breakdown) {
        if (!function_exists('WC')) {
            return $this->get_checkout_redirect_url();
        }

        if (null === WC()->cart && function_exists('wc_load_cart')) {
            wc_load_cart();
        }

        // Limpiar carrito actual
        if (WC()->cart) {
            WC()->cart->empty_cart();
        }
        
        // Crear producto único para esta reserva
        $product_id = $this->create_booking_product($booking_data, $price_breakdown);
        
        if (!$product_id) {
            error_log('MET Chatbot: ERROR - No se pudo crear el producto de reserva');
            return $this->get_checkout_redirect_url();
        }
        
        error_log('MET Chatbot: Producto de reserva creado con ID: ' . $product_id);
        
        // Verificar el precio del producto antes de agregar al carrito
        $product_obj = wc_get_product($product_id);
        if ($product_obj) {
            error_log('MET Chatbot: Precio del producto antes de agregar: €' . $product_obj->get_price());
        }
        
        // Agregar el producto al carrito
        if (WC()->cart) {
            $cart_item_key = WC()->cart->add_to_cart($product_id, 1);
            
            if ($cart_item_key) {
                error_log('MET Chatbot: Producto agregado al carrito con key: ' . $cart_item_key);
                
                // Verificar el precio en el carrito
                $cart_item = WC()->cart->get_cart_item($cart_item_key);
                if ($cart_item) {
                    error_log('MET Chatbot: Precio en el carrito: €' . $cart_item['data']->get_price());
                }
                
                error_log('MET Chatbot: Subtotal del carrito: ' . WC()->cart->get_subtotal());
                error_log('MET Chatbot: Total del carrito: ' . WC()->cart->get_total(''));

                // Guardar sesión y cookies del carrito antes de redirigir
                WC()->cart->set_session();
                WC()->cart->maybe_set_cart_cookies();
                
                // Dar un pequeño margen para que WooCommerce persista el carrito
                if (function_exists('wp_sleep')) {
                    wp_sleep(1);
                }
            } else {
                error_log('MET Chatbot: ERROR - No se pudo agregar el producto al carrito');
            }
        }
        
        // Retornar URL del checkout directamente
        return $this->get_checkout_redirect_url();
    }

    /**
     * Obtener la URL de checkout a la que debe redirigir el chatbot
     */
    private function get_checkout_redirect_url() {
        $default_checkout_url = $this->get_dynamic_checkout_url();

        if (empty($default_checkout_url) && !empty($this->production_checkout_url)) {
            $default_checkout_url = $this->production_checkout_url;
        }

        $configured_url = get_option(
            'met_chatbot_checkout_url',
            $default_checkout_url
        );

        $custom_url = apply_filters('met_chatbot_checkout_redirect_url', $configured_url);

        if (!empty($custom_url)) {
            return esc_url_raw($custom_url);
        }

        if (!empty($default_checkout_url)) {
            return esc_url_raw($default_checkout_url);
        }

        if (function_exists('wc_get_cart_url')) {
            $cart_url = wc_get_cart_url();
            if (!empty($cart_url)) {
                return esc_url_raw($cart_url);
            }
        }

        return esc_url_raw(home_url('/'));
    }

    /**
     * Detectar dinámicamente la URL del checkout de WooCommerce
     */
    private function get_dynamic_checkout_url() {
        if (function_exists('wc_get_checkout_url')) {
            $checkout_url = wc_get_checkout_url();

            if (!empty($checkout_url)) {
                return $checkout_url;
            }
        }

        $checkout_page_id = null;

        if (function_exists('wc_get_page_id')) {
            $checkout_page_id = wc_get_page_id('checkout');
        }

        if (!$checkout_page_id) {
            $checkout_page_id = get_option('woocommerce_checkout_page_id');
        }

        if ($checkout_page_id) {
            $permalink = get_permalink($checkout_page_id);

            if (!empty($permalink)) {
                return $permalink;
            }
        }

        if (function_exists('wc_get_page_permalink')) {
            $page_permalink = wc_get_page_permalink('checkout');

            if (!empty($page_permalink)) {
                return $page_permalink;
            }
        }

        if (!empty($this->production_checkout_url)) {
            return esc_url_raw($this->production_checkout_url);
        }

        return trailingslashit(site_url('/checkout/'));
    }
    
    /**
     * Crear un producto único para esta reserva
     */
    private function create_booking_product($booking_data, $price_breakdown) {
        // Obtener número de reserva autoincremental
        $booking_number = $this->get_next_booking_number();
        
        // Crear título descriptivo según el tipo de ruta
        $route_label = 'Traslado';
        $route_type = isset($booking_data['route_type']) ? $booking_data['route_type'] : '';

        switch ($route_type) {
            case 'point_to_airport':
                $route_label = 'Traslado Punto → Aeropuerto';
                break;
            case 'airport':
                $route_label = 'Traslado Aeropuerto → Punto';
                break;
            default:
                $route_label = 'Traslado Privado';
                break;
        }

        $origin = !empty($booking_data['origin']) ? $booking_data['origin'] : __('Origen sin definir', 'met-chatbot');
        $destination = !empty($booking_data['destination']) ? $booking_data['destination'] : __('Destino sin definir', 'met-chatbot');

        $datetime_label = '';
        if (!empty($booking_data['datetime'])) {
            $datetime_label = ' - ' . $booking_data['datetime'];
        }

        $title = sprintf(
            '%s #%s%s (%s → %s)',
            $route_label,
            $booking_number,
            $datetime_label,
            $origin,
            $destination
        );
        
        // Crear descripción detallada
        $description = $this->generate_product_description($booking_data, $price_breakdown);
        
        // Crear el producto
        $product = new WC_Product_Simple();
        $product->set_name($title);
        $product->set_slug('traslado-' . $booking_number . '-' . time());
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden'); // Oculto del catálogo
        $product->set_description($description);
        $product->set_short_description('Servicio de traslado privado en Mallorca');
        
        // Establecer el precio calculado
        $price = floatval($price_breakdown['total']);
        
        // Asegurar que el precio tenga exactamente 2 decimales
        $price = round($price, 2);
        
        $product->set_price($price);
        $product->set_regular_price($price);
        
        $product->set_sold_individually(true);
        $product->set_virtual(true);
        $product->set_manage_stock(false);
        $product->set_tax_status('none'); // Sin impuestos
        
        // Guardar el producto
        $product_id = $product->save();
        
        if ($product_id) {
            // Forzar actualización de precios en la base de datos con formato correcto
            update_post_meta($product_id, '_price', wc_format_decimal($price));
            update_post_meta($product_id, '_regular_price', wc_format_decimal($price));
            update_post_meta($product_id, '_sale_price', '');
            
            // Guardar precio como string para evitar problemas de conversión
            update_post_meta($product_id, '_met_original_price', strval($price));
            
            // Guardar metadata adicional
            update_post_meta($product_id, '_met_booking_number', $booking_number);
            update_post_meta($product_id, '_met_booking_data', $booking_data);
            update_post_meta($product_id, '_met_price_breakdown', $price_breakdown);
            update_post_meta($product_id, '_met_created_at', current_time('mysql'));

            // Asociar imagen predeterminada
            $image_id = $this->get_default_booking_image_id();
            if ($image_id) {
                set_post_thumbnail($product_id, $image_id);
            }
            
            // Guardar opciones extras como metadatos individuales (para visualización en WooCommerce)
            if (isset($booking_data['extras']) && is_array($booking_data['extras'])) {
                $extras = $booking_data['extras'];
                
                // Extras informativos (gratis)
                update_post_meta($product_id, '_extra_equipaje_mano', isset($extras['equipaje_de_mano']) ? intval($extras['equipaje_de_mano']) : 0);
                update_post_meta($product_id, '_extra_valijas', isset($extras['valijas']) ? intval($extras['valijas']) : 0);
                update_post_meta($product_id, '_extra_alzadores', isset($extras['alzadores']) ? intval($extras['alzadores']) : 0);
                update_post_meta($product_id, '_extra_sillas_bebe', isset($extras['sillas_bebe']) ? intval($extras['sillas_bebe']) : 0);
                
                // Extras con costo
                if (isset($extras['bolsa_golf']) && is_array($extras['bolsa_golf'])) {
                    update_post_meta($product_id, '_extra_bolsa_golf_cantidad', intval($extras['bolsa_golf']['cantidad']));
                    update_post_meta($product_id, '_extra_bolsa_golf_subtotal', floatval($extras['bolsa_golf']['subtotal']));
                } else {
                    update_post_meta($product_id, '_extra_bolsa_golf_cantidad', 0);
                    update_post_meta($product_id, '_extra_bolsa_golf_subtotal', 0);
                }
                
                if (isset($extras['bicicleta']) && is_array($extras['bicicleta'])) {
                    update_post_meta($product_id, '_extra_bicicleta_cantidad', intval($extras['bicicleta']['cantidad']));
                    update_post_meta($product_id, '_extra_bicicleta_subtotal', floatval($extras['bicicleta']['subtotal']));
                } else {
                    update_post_meta($product_id, '_extra_bicicleta_cantidad', 0);
                    update_post_meta($product_id, '_extra_bicicleta_subtotal', 0);
                }
                
                // Total de extras
                update_post_meta($product_id, '_extra_total', isset($extras['total_extras']) ? floatval($extras['total_extras']) : 0);
            }
            
            // Limpiar caché de WooCommerce
            wc_delete_product_transients($product_id);
            
            error_log('MET Chatbot: Producto creado - ID: ' . $product_id . ', Precio: €' . wc_format_decimal($price));
            error_log('MET Chatbot: Precio guardado en _price: ' . get_post_meta($product_id, '_price', true));
        }
        
        return $product_id;
    }

    /**
     * Obtener (o descargar) la imagen predeterminada para las reservas
     */
    private function get_default_booking_image_id() {
        $option_key = 'met_chatbot_default_booking_image_id';
        $stored_source = get_option($this->default_image_source_option, '');
        $attachment_id = get_option($option_key, 0);

        $attachment_exists = $attachment_id && get_post($attachment_id);
        $needs_refresh = empty($stored_source) || $stored_source !== $this->default_image_url;

        if ($attachment_exists && !$needs_refresh) {
            return $attachment_id;
        }

        $new_attachment_id = $this->download_default_booking_image();
        if ($new_attachment_id) {
            update_option($option_key, $new_attachment_id);
            update_option($this->default_image_source_option, $this->default_image_url);
            return $new_attachment_id;
        }

        if ($attachment_exists) {
            return $attachment_id;
        }

        delete_option($option_key);
        delete_option($this->default_image_source_option);

        return 0;
    }

    /**
     * Descargar la imagen y guardarla como adjunto
     */
    private function download_default_booking_image() {
        if (empty($this->default_image_url)) {
            return 0;
        }

        if (!function_exists('media_sideload_image')) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }
        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $result = media_sideload_image($this->default_image_url, 0, 'MET Mallorca Transfers', 'id');

        if (is_wp_error($result)) {
            error_log('MET Chatbot: No se pudo descargar la imagen predeterminada - ' . $result->get_error_message());
            return 0;
        }

        return intval($result);
    }
    
    /**
     * Obtener el siguiente número de reserva
     */
    private function get_next_booking_number() {
        $current_number = get_option('met_chatbot_last_booking_number', 0);
        $next_number = $current_number + 1;
        update_option('met_chatbot_last_booking_number', $next_number);
        
        // Formato: MET-2025-0001
        return sprintf('MET-%s-%04d', date('Y'), $next_number);
    }
    
    /**
     * Generar descripción del producto
     */
    private function generate_product_description($booking_data, $price_breakdown) {
        $description = '<h3>Detalles de la Reserva</h3>';
        $description .= '<ul>';
        $description .= '<li><strong>Origen:</strong> ' . esc_html($booking_data['origin']) . '</li>';
        $description .= '<li><strong>Destino:</strong> ' . esc_html($booking_data['destination']) . '</li>';
        
        if (isset($booking_data['datetime'])) {
            $description .= '<li><strong>Fecha y Hora:</strong> ' . esc_html($booking_data['datetime']) . '</li>';
        }
        
        if (isset($booking_data['passengers'])) {
            $description .= '<li><strong>Pasajeros:</strong> ' . esc_html($booking_data['passengers']) . '</li>';
        }
        
        if (isset($booking_data['pet']) && $booking_data['pet'] !== 'no') {
            $pet_labels = array(
                'small_dog' => 'Perro pequeño',
                'large_dog' => 'Perro grande',
                'cat' => 'Gato'
            );
            $pet_label = isset($pet_labels[$booking_data['pet']]) ? $pet_labels[$booking_data['pet']] : $booking_data['pet'];
            $description .= '<li><strong>Mascota:</strong> ' . esc_html($pet_label) . '</li>';
        }
        
        if (isset($booking_data['flight_number']) && !empty($booking_data['flight_number'])) {
            $description .= '<li><strong>Número de Vuelo:</strong> ' . esc_html($booking_data['flight_number']) . '</li>';
        }
        
        $description .= '</ul>';
        
        // Desglose de precio
        $description .= '<h3>Desglose del Precio</h3>';
        $description .= '<ul>';
        $description .= '<li>Precio base: €' . number_format($price_breakdown['base_price'], 2) . '</li>';
        
        if ($price_breakdown['vehicle_supplement'] > 0) {
            $description .= '<li>Suplemento vehículo: €' . number_format($price_breakdown['vehicle_supplement'], 2) . '</li>';
        }
        

        
        if ($price_breakdown['extra_passengers'] > 0) {
            $description .= '<li>Pasajeros extra: €' . number_format($price_breakdown['extra_passengers'], 2) . '</li>';
        }
        
        if (!empty($price_breakdown['extras'])) {
            foreach ($price_breakdown['extras'] as $extra => $price) {
                $description .= '<li>' . ucfirst(str_replace('_', ' ', $extra)) . ': €' . number_format($price, 2) . '</li>';
            }
        }
        
        $description .= '<li><strong>TOTAL: €' . number_format($price_breakdown['total'], 2) . '</strong></li>';
        $description .= '</ul>';
        
        return $description;
    }
    
    /**
     * Generar hash único para la reserva
     */
    private function generate_booking_hash($booking_data) {
        $data_string = json_encode($booking_data) . time();
        return substr(md5($data_string), 0, 16);
    }
    
    /**
     * Guardar datos de reserva en sesión de WooCommerce
     */
    private function save_booking_to_session($hash, $booking_data, $price_breakdown) {
        // Asegurar que la sesión esté inicializada
        if (!WC()->session) {
            if (function_exists('WC') && class_exists('WC_Session_Handler')) {
                WC()->initialize_session();
            }
        }
        
        if (!WC()->session) {
            error_log('MET Chatbot: No se pudo inicializar la sesión de WooCommerce');
            return;
        }
        
        $session_data = array(
            'booking_data' => $booking_data,
            'price_breakdown' => $price_breakdown,
            'timestamp' => time()
        );
        
        WC()->session->set('met_booking_' . $hash, $session_data);
        
        // Forzar guardado de sesión
        if (method_exists(WC()->session, 'save_data')) {
            WC()->session->save_data();
        }
    }
    
    /**
     * Recuperar datos de reserva desde sesión
     */
    public function get_booking_from_session($hash) {
        if (!WC()->session) {
            return null;
        }
        
        $session_data = WC()->session->get('met_booking_' . $hash);
        
        // Verificar que no haya expirado (30 minutos)
        if ($session_data && isset($session_data['timestamp'])) {
            if (time() - $session_data['timestamp'] > 1800) {
                WC()->session->set('met_booking_' . $hash, null);
                return null;
            }
        }
        
        return $session_data;
    }
    
    /**
     * Verificar si un producto existe
     */
    private function product_exists($product_id) {
        if (!$product_id) {
            return false;
        }
        
        $product = wc_get_product($product_id);
        return $product && $product->exists();
    }
    
    /**
     * Crear producto de traslado automáticamente
     */
    private function create_transfer_product() {
        if (!function_exists('wc_get_product')) {
            return 0;
        }
        
        // Verificar si ya existe un producto con este nombre
        $existing = get_page_by_title('Servicio de Traslado', OBJECT, 'product');
        if ($existing) {
            update_option('met_chatbot_transfer_product_id', $existing->ID);
            return $existing->ID;
        }
        
        // Crear nuevo producto
        $product = new WC_Product_Simple();
        $product->set_name('Servicio de Traslado');
        $product->set_slug('servicio-traslado-met');
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden'); // Oculto del catálogo
        $product->set_description('Servicio de traslado privado en Mallorca. El precio se calcula según la ruta, vehículo y extras seleccionados.');
        $product->set_short_description('Traslado privado en Mallorca');
        $product->set_price(0); // El precio se establece dinámicamente
        $product->set_regular_price(0);
        $product->set_sold_individually(true); // Solo 1 por pedido
        $product->set_virtual(true); // Es un servicio
        
        $product_id = $product->save();
        
        if ($product_id) {
            update_option('met_chatbot_transfer_product_id', $product_id);
        }
        
        return $product_id;
    }
    
    /**
     * Generar HTML del botón de checkout
     */
    public function generate_checkout_button($booking_data, $price_breakdown) {
        $checkout_url = $this->generate_checkout_url($booking_data, $price_breakdown);
        
        $html = '<div class="met-checkout-button-container">';
        $html .= '<a href="' . esc_url($checkout_url) . '" class="met-checkout-button" target="_blank">';
        $html .= '<i class="fas fa-shopping-cart"></i> ';
        $html .= 'Ir al Checkout (€' . number_format($price_breakdown['total'], 2) . ')';
        $html .= '</a>';
        $html .= '<p class="met-checkout-note">';
        $html .= '<small>Serás redirigido al checkout seguro de WooCommerce para completar el pago.</small>';
        $html .= '</p>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Ajustar el precio del carrito para reflejar el total calculado por el chatbot
     */
    public function modify_cart_item_price($cart = null) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (did_action('woocommerce_before_calculate_totals') >= 2 && $cart instanceof WC_Cart) {
            return;
        }

        if (!$cart || !($cart instanceof WC_Cart)) {
            if (function_exists('WC') && WC()->cart instanceof WC_Cart) {
                $cart = WC()->cart;
            } else {
                return;
            }
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (!isset($cart_item['product_id']) || intval($cart_item['product_id']) !== intval($this->transfer_product_id)) {
                continue;
            }

            $custom_price = null;

            if (isset($cart_item['met_custom_price']) && $cart_item['met_custom_price'] > 0) {
                $custom_price = floatval($cart_item['met_custom_price']);
            } elseif (isset($cart_item['met_booking_hash'])) {
                $hash = $cart_item['met_booking_hash'];
                $session_data = $this->get_booking_from_session($hash);

                if ($session_data && isset($session_data['price_breakdown']['total'])) {
                    $custom_price = floatval($session_data['price_breakdown']['total']);
                }
            }

            if ($custom_price && $custom_price > 0) {
                $custom_price = wc_format_decimal($custom_price, 2);

                if (isset($cart_item['data']) && is_object($cart_item['data'])) {
                    $cart_item['data']->set_price($custom_price);
                    $cart_item['data']->set_regular_price($custom_price);
                }

                $cart->cart_contents[$cart_item_key]['met_custom_price'] = $custom_price;
            }
        }
    }
    
    /**
     * Agregar metadata personalizada al item del pedido
     */
    public function add_order_item_meta($item, $cart_item_key, $values, $order) {
        if (isset($values['met_booking_data'])) {
            $booking_data = $values['met_booking_data'];
            
            // Agregar metadata visible
            $item->add_meta_data('Origen', $booking_data['origin'], true);
            $item->add_meta_data('Destino', $booking_data['destination'], true);
            
            if (isset($booking_data['datetime'])) {
                $item->add_meta_data('Fecha y Hora', $booking_data['datetime'], true);
            }
            
            if (isset($booking_data['passengers'])) {
                $item->add_meta_data('Pasajeros', $booking_data['passengers'], true);
            }
            
            if (isset($booking_data['pet']) && $booking_data['pet'] !== 'no') {
                $pet_labels = array(
                    'small_dog' => 'Perro pequeño',
                    'large_dog' => 'Perro grande',
                    'cat' => 'Gato'
                );
                $pet_label = isset($pet_labels[$booking_data['pet']]) ? $pet_labels[$booking_data['pet']] : $booking_data['pet'];
                $item->add_meta_data('Mascota', $pet_label, true);
            }
            
            if (isset($booking_data['flight_number']) && !empty($booking_data['flight_number'])) {
                $item->add_meta_data('Número de Vuelo', $booking_data['flight_number'], true);
            }
            
            // Guardar datos completos como metadata oculta
            $item->add_meta_data('_met_booking_data', $booking_data, false);
        }
        
        if (isset($values['met_price_breakdown'])) {
            $item->add_meta_data('_met_price_breakdown', $values['met_price_breakdown'], false);
        }
    }
    
    /**
     * Agregar datos de reserva al carrito desde URL
     */
    public function add_booking_to_cart($cart_item_data, $product_id) {
        // Solo para nuestro producto de traslado
        if ($product_id != $this->transfer_product_id) {
            return $cart_item_data;
        }
        
        // Verificar si hay un hash de reserva en la URL
        if (isset($_GET['met_booking_hash'])) {
            $hash = sanitize_text_field($_GET['met_booking_hash']);
            $session_data = $this->get_booking_from_session($hash);
            
            if ($session_data) {
                $cart_item_data['met_booking_data'] = $session_data['booking_data'];
                $cart_item_data['met_price_breakdown'] = $session_data['price_breakdown'];
                $cart_item_data['met_custom_price'] = $session_data['price_breakdown']['total'];
                $cart_item_data['met_booking_hash'] = $hash;
                $cart_item_data['unique_key'] = $hash;
            }
        }
        
        return $cart_item_data;
    }
    
    /**
     * Mostrar precio personalizado en el carrito
     */
    public function display_custom_price_in_cart($price, $cart_item, $cart_item_key) {
        if (isset($cart_item['met_custom_price']) && $cart_item['met_custom_price'] > 0) {
            return wc_price($cart_item['met_custom_price']);
        }
        return $price;
    }
    
    /**
     * Recuperar item del carrito desde sesión con precio personalizado
     */
    public function get_cart_item_from_session($cart_item, $values, $key) {
        if (isset($values['met_custom_price']) && $values['met_custom_price'] > 0) {
            $cart_item['met_custom_price'] = $values['met_custom_price'];
            $cart_item['met_booking_data'] = isset($values['met_booking_data']) ? $values['met_booking_data'] : array();
            $cart_item['met_price_breakdown'] = isset($values['met_price_breakdown']) ? $values['met_price_breakdown'] : array();
            $cart_item['met_booking_hash'] = isset($values['met_booking_hash']) ? $values['met_booking_hash'] : '';
            
            // Establecer el precio inmediatamente con formato correcto
            if (isset($cart_item['data'])) {
                $formatted_price = wc_format_decimal(floatval($values['met_custom_price']), 2);
                $cart_item['data']->set_price($formatted_price);
                $cart_item['data']->set_regular_price($formatted_price);
            }
        }
        return $cart_item;
    }
    
    /**
     * Inicializar hooks de WooCommerce
     */
    public function init_hooks() {
        // Modificar precio en el carrito (múltiples hooks para asegurar que funcione)
        add_action('woocommerce_before_calculate_totals', array($this, 'modify_cart_item_price'), 10, 1);
        add_action('woocommerce_cart_loaded_from_session', array($this, 'modify_cart_item_price'), 10, 1);
        
        // Hook adicional para asegurar precios antes del checkout
        add_action('woocommerce_checkout_before_order_review', array($this, 'modify_cart_item_price'), 10);
        add_action('woocommerce_before_checkout_form', array($this, 'modify_cart_item_price'), 10);
        
        // Agregar metadata al pedido
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_order_item_meta'), 10, 4);
        
        // Agregar datos al carrito desde URL
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_booking_to_cart'), 10, 2);
        
        // Mostrar precio personalizado en el carrito
        add_filter('woocommerce_cart_item_price', array($this, 'display_custom_price_in_cart'), 10, 3);
        add_filter('woocommerce_cart_item_subtotal', array($this, 'display_custom_price_in_cart'), 10, 3);
        
        // Hook adicional para asegurar que el precio se mantiene
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'get_cart_item_from_session'), 10, 3);
    }
}
