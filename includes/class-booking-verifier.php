<?php
if (!defined('ABSPATH')) {
    exit;
}

class MET_Booking_Verifier {
    const SHORTCODE = 'met_verificar_reserva';

    public function __construct() {
        add_shortcode(self::SHORTCODE, array($this, 'render_shortcode'));
    }

    /**
     * Formulario para incrustar dentro del chatbot (sin recargar página)
     */
    public static function render_inline_form() {
        $description = sprintf(
            '<p style="margin:0 0 12px;">%s<br><small>%s</small></p>',
            esc_html__('Ingresa tu código MET para consultar el estado.', 'met-chatbot'),
            esc_html__('Ejemplo: MET-1234', 'met-chatbot')
        );

        ob_start();
        ?>
        <div class="met-chatbot-booking-verifier" data-form-type="booking_verifier">
            <?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <form class="met-chatbot-booking-verifier-form" autocomplete="off">
                <label for="met_chatbot_booking_code" style="display:block;margin-bottom:6px;font-weight:600;">
                    <?php esc_html_e('Código de reserva', 'met-chatbot'); ?>
                </label>
                <input
                    type="text"
                    id="met_chatbot_booking_code"
                    name="met_booking_code"
                    placeholder="MET-1234"
                    required
                    pattern="MET-[0-9]+"
                    style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;margin-bottom:10px;"
                />
                <button type="submit" class="met-chatbot-booking-verifier-submit" style="width:100%;padding:10px;background:#007cba;color:#fff;border:none;border-radius:4px;font-size:15px;font-weight:600;cursor:pointer;">
                    <?php esc_html_e('Verificar', 'met-chatbot'); ?>
                </button>
            </form>
            <div class="met-chatbot-booking-verifier-result" style="margin-top:12px;"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Verificar un código de reserva y devolver datos públicos
     */
    public static function verify_code_data($booking_code) {
        $booking_code = sanitize_text_field($booking_code);

        if (empty($booking_code)) {
            return array(
                'verified' => false,
                'message' => __('Ingresa un código válido.', 'met-chatbot'),
                'error_code' => 'empty_code'
            );
        }

        if (!preg_match('/^MET-(\d+)$/i', $booking_code, $matches)) {
            return array(
                'verified' => false,
                'message' => __('El código debe tener el formato MET-1234.', 'met-chatbot'),
                'error_code' => 'invalid_format'
            );
        }

        $order_id = intval($matches[1]);

        if (!function_exists('wc_get_order')) {
            return array(
                'verified' => false,
                'message' => __('WooCommerce no está disponible en este sitio.', 'met-chatbot'),
                'error_code' => 'woocommerce_missing'
            );
        }

        $order = wc_get_order($order_id);

        if (!$order) {
            return array(
                'verified' => false,
                'message' => __('No encontramos una reserva con ese código.', 'met-chatbot'),
                'error_code' => 'not_found'
            );
        }

        $status_label = function_exists('wc_get_order_status_name')
            ? wc_get_order_status_name($order->get_status())
            : ucfirst($order->get_status());

        $datetime = $order->get_date_created();
        $formatted_date = $datetime
            ? wc_format_datetime($datetime, get_option('date_format') . ' ' . get_option('time_format'))
            : '';

        $total_html = $order->get_formatted_order_total();

        $customer_name = trim(sprintf('%s %s', $order->get_billing_first_name(), $order->get_billing_last_name()));
        if (empty($customer_name)) {
            $customer_name = __('Sin nombre', 'met-chatbot');
        }

        $view_url = '';
        if (function_exists('wc_get_endpoint_url')) {
            $view_url = wc_get_endpoint_url('view-order', $order->get_id(), wc_get_page_permalink('myaccount'));
        }

        $transfer_datetime = $order->get_meta('_datetime');

        $items = array();
        foreach ($order->get_items() as $item) {
            $line = $item->get_name();
            $qty = $item->get_quantity();
            if ($qty > 1) {
                $line .= ' (x' . $qty . ')';
            }
            $items[] = $line;
        }
        $items_text = !empty($items) ? implode(', ', $items) : __('Sin productos', 'met-chatbot');

        return array(
            'verified' => true,
            'error_code' => null,
            'order' => array(
                'id' => $order->get_id(),
                'code' => 'MET-' . $order->get_id(),
                'status' => $order->get_status(),
                'status_label' => $status_label,
                'customer' => $customer_name,
                'date' => $formatted_date,
                'total_html' => $total_html,
                'total_text' => wp_strip_all_tags($total_html),
                'view_url' => $view_url,
                'transfer_datetime' => $transfer_datetime,
                'items' => $items,
                'items_text' => $items_text,
            )
        );
    }

    public function render_shortcode($atts = array()) {
        $atts = shortcode_atts(
            array(
                'success_link' => '',
                'success_link_label' => __('Ver mi reserva', 'met-chatbot'),
            ),
            $atts,
            self::SHORTCODE
        );

        $message = '';
        $booking_code_value = '';

        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['met_booking_verifier_action'])) {
            $message = $this->handle_submission($atts);
            $booking_code_value = isset($_POST['met_booking_code']) ? sanitize_text_field(wp_unslash($_POST['met_booking_code'])) : '';
        }

        ob_start();
        ?>
        <div class="met-booking-verifier" style="max-width:420px;margin:0 auto;">
            <form method="post" class="met-booking-verifier-form" style="border:1px solid #ddd;padding:20px;border-radius:8px;background:#fff;">
                <?php wp_nonce_field('met_booking_verifier', 'met_booking_nonce'); ?>
                <input type="hidden" name="met_booking_verifier_action" value="1" />

                <label for="met_booking_code" style="display:block;margin-bottom:8px;font-weight:600;">
                    <?php esc_html_e('Ingresa tu código de reserva', 'met-chatbot'); ?>
                </label>
                <input
                    type="text"
                    id="met_booking_code"
                    name="met_booking_code"
                    placeholder="MET-1234"
                    required
                    pattern="MET-[0-9]+"
                    style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;margin-bottom:12px;"
                    value="<?php echo esc_attr($booking_code_value); ?>"
                />

                <button type="submit" style="width:100%;padding:10px;background:#007cba;color:#fff;border:none;border-radius:4px;font-size:16px;cursor:pointer;">
                    <?php esc_html_e('Verificar', 'met-chatbot'); ?>
                </button>
            </form>
            <div class="met-booking-verifier-response">
                <?php echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function handle_submission($atts) {
        if (!isset($_POST['met_booking_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['met_booking_nonce'])), 'met_booking_verifier')) {
            return self::render_message(__('La sesión expiró. Intenta nuevamente.', 'met-chatbot'), 'error');
        }

        $booking_code = isset($_POST['met_booking_code']) ? sanitize_text_field(wp_unslash($_POST['met_booking_code'])) : '';
        $result = self::verify_code_data($booking_code);

        if (empty($result['verified'])) {
            return self::render_message($result['message'], 'error');
        }

        return self::render_success_message($result['order'], $atts);
    }

    public static function render_success_message($order_data, $atts = array()) {
        $content = sprintf(
            '<strong>%s</strong><br>%s',
            esc_html__('¡Reserva encontrada!', 'met-chatbot'),
            self::build_success_details($order_data, $atts)
        );

        return self::render_message($content, 'success');
    }

    public static function render_error_message($content) {
        return self::render_message($content, 'error');
    }

    private static function build_success_details($order_data, $atts) {
        $defaults = array(
            'success_link' => '',
            'success_link_label' => __('Ver mi reserva', 'met-chatbot'),
        );
        $atts = wp_parse_args($atts, $defaults);

        $success_link_url = trim($atts['success_link']);
        if (empty($success_link_url) && !empty($order_data['view_url'])) {
            $success_link_url = $order_data['view_url'];
        }
        if (empty($success_link_url)) {
            $success_link_url = get_permalink();
        }

        $link_html = sprintf(
            '<a href="%s" style="color:#0a7a0a;text-decoration:underline;font-weight:600;">%s</a>',
            esc_url($success_link_url),
            esc_html($atts['success_link_label'])
        );

        $details  = '<p style="margin:0 0 8px;"><strong>' . esc_html__('Código:', 'met-chatbot') . '</strong> ' . esc_html($order_data['code']) . '</p>';
        $details .= '<p style="margin:0 0 4px;"><strong>' . esc_html__('Estado:', 'met-chatbot') . '</strong> ' . esc_html($order_data['status_label']) . '</p>';
        $details .= '<p style="margin:0 0 4px;"><strong>' . esc_html__('Producto(s):', 'met-chatbot') . '</strong> ' . esc_html($order_data['items_text']) . '</p>';
        $details .= '<p style="margin:0 0 4px;"><strong>' . esc_html__('Cliente:', 'met-chatbot') . '</strong> ' . esc_html($order_data['customer']) . '</p>';

        if (!empty($order_data['transfer_datetime'])) {
            $details .= '<p style="margin:0 0 4px;"><strong>' . esc_html__('Fecha reservada:', 'met-chatbot') . '</strong> ' . esc_html($order_data['transfer_datetime']) . '</p>';
        }

        $details .= '<p style="margin:0 0 4px;"><strong>' . esc_html__('Fecha de realización del pedido:', 'met-chatbot') . '</strong> ' . esc_html(trim(sprintf('%s · %s', $order_data['date'], $order_data['total_text']))) . '</p>';
        $details .= '<p style="margin:12px 0 0;">' . $link_html . '</p>';

        return $details;
    }

    private static function render_message($content, $type = 'error') {
        $styles = array(
            'success' => 'border:1px solid #b0e9c1;background:#f2fff5;color:#135c1c;',
            'error'   => 'border:1px solid #f5c6cb;background:#fff5f5;color:#721c24;',
        );

        $style = isset($styles[$type]) ? $styles[$type] : $styles['error'];

        return sprintf(
            '<div style="margin-top:15px;padding:15px;border-radius:6px;%s">%s</div>',
            esc_attr($style),
            wp_kses_post($content)
        );
    }
}
