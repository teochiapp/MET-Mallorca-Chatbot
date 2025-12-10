<?php
/**
 * Clase para manejar la verificación de reservas
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Booking_Handler {
    
    /**
     * Verificar si una reserva existe
     */
    public function verify_booking($booking_code, $email) {
        // Verificar si el código tiene el formato MET-XXXXXX
        if (!preg_match('/^MET-\d+$/', $booking_code)) {
            return array(
                'found' => false,
                'message' => '❌ No encontramos esa reserva en MET Mallorca.<br><br>¿Podría ser de otra empresa?<br><br>Puedes adjuntar foto del voucher o indicarnos la empresa que figura en tu comprobante.',
                'is_met' => false,
                'error_code' => 'invalid_format'
            );
        }
        
        // Extraer el ID de la orden del código
        $order_id = str_replace('MET-', '', $booking_code);
        
        // Buscar la orden en WooCommerce
        if (!function_exists('wc_get_order')) {
            return array(
                'found' => false,
                'message' => '❌ Error del sistema. Por favor, contacta con soporte.',
                'is_met' => false,
                'error_code' => 'system_error'
            );
        }
        
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return array(
                'found' => false,
                'message' => '❌ Tu reserva no está registrada. Por favor, vuelve a comprobarla o realiza una nueva.',
                'is_met' => false,
                'error_code' => 'not_found'
            );
        }
        
        // Verificar que el email coincida
        $order_email = $order->get_billing_email();
        if (strtolower($order_email) !== strtolower($email)) {
            return array(
                'found' => false,
                'message' => '❌ El email no coincide con la reserva. Por favor, verifica los datos.',
                'is_met' => true,
                'error_code' => 'email_mismatch'
            );
        }
        
        // Reserva encontrada y verificada
        $booking_details = $this->format_booking_details($order);
        
        return array(
            'found' => true,
            'is_met' => true,
            'message' => '✅ Tu reserva fue realizada con MET Mallorca.<br><br>' . $booking_details,
            'order_id' => $order_id,
            'error_code' => null,
            'order' => $this->build_order_payload($order)
        );
    }
    
    /**
     * Formatear detalles de la reserva
     */
    private function format_booking_details($order) {
        $details = '<strong>Detalles de tu reserva:</strong><br>';
        $details .= '📋 Ref: MET-' . $order->get_id() . '<br>';
        $details .= '👤 Cliente: ' . esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) . '<br>';
        $details .= '📧 Email: ' . esc_html($order->get_billing_email()) . '<br>';
        $details .= '📞 Teléfono: ' . esc_html($order->get_billing_phone()) . '<br>';
        $details .= '📅 Fecha: ' . $order->get_date_created()->date('d/m/Y H:i') . '<br>';
        $details .= '💰 Total: ' . $order->get_formatted_order_total() . '<br>';
        $details .= '📊 Estado: ' . $this->format_order_status($order->get_status()) . '<br>';
        
        // Obtener items de la orden
        $items = $order->get_items();
        if (!empty($items)) {
            $details .= '<br><strong>Servicios:</strong><br>';
            foreach ($items as $item) {
                $details .= '• ' . $item->get_name() . ' (x' . $item->get_quantity() . ')<br>';
            }
        }
        
        // Obtener metadata personalizada
        $origin = $order->get_meta('_origin');
        $destination = $order->get_meta('_destination');
        $datetime = $order->get_meta('_datetime');
        $passengers = $order->get_meta('_passengers');
        
        if ($origin || $destination) {
            $details .= '<br><strong>Detalles del traslado:</strong><br>';
            if ($origin) $details .= '📍 Origen: ' . esc_html($origin) . '<br>';
            if ($destination) $details .= '📍 Destino: ' . esc_html($destination) . '<br>';
            if ($datetime) $details .= '📅 Fecha/Hora: ' . esc_html($datetime) . '<br>';
            if ($passengers) $details .= '👥 Pasajeros: ' . esc_html($passengers) . '<br>';
        }
        
        return $details;
    }
    
    /**
     * Formatear estado de la orden
     */
    private function format_order_status($status) {
        $statuses = array(
            'pending' => '⏳ Pendiente de pago',
            'processing' => '⚙️ En proceso',
            'on-hold' => '⏸️ En espera',
            'completed' => '✅ Confirmada',
            'cancelled' => '❌ Cancelada',
            'refunded' => '💸 Reembolsada',
            'failed' => '❌ Fallida'
        );
        
        return isset($statuses[$status]) ? $statuses[$status] : ucfirst($status);
    }
    
    /**
     * Crear nueva reserva desde el chatbot
     */
    public function create_booking($data) {
        if (!function_exists('wc_create_order')) {
            return array(
                'success' => false,
                'message' => 'WooCommerce no está disponible'
            );
        }
        
        try {
            // Crear nueva orden
            $order = wc_create_order();
            
            // Establecer datos del cliente
            if (isset($data['customer_name'])) {
                $names = explode(' ', $data['customer_name'], 2);
                $order->set_billing_first_name($names[0]);
                if (isset($names[1])) {
                    $order->set_billing_last_name($names[1]);
                }
            }
            
            if (isset($data['customer_email'])) {
                $order->set_billing_email($data['customer_email']);
            }
            
            if (isset($data['customer_phone'])) {
                $order->set_billing_phone($data['customer_phone']);
            }
            
            // Guardar metadata personalizada
            if (isset($data['origin'])) {
                $order->update_meta_data('_origin', $data['origin']);
            }
            
            if (isset($data['destination'])) {
                $order->update_meta_data('_destination', $data['destination']);
            }
            
            if (isset($data['datetime'])) {
                $order->update_meta_data('_datetime', $data['datetime']);
            }
            
            if (isset($data['passengers'])) {
                $order->update_meta_data('_passengers', $data['passengers']);
            }
            
            if (isset($data['pet'])) {
                $order->update_meta_data('_pet', $data['pet']);
            }
            
            if (isset($data['flight_number'])) {
                $order->update_meta_data('_flight_number', $data['flight_number']);
            }
            
            // Calcular totales
            $order->calculate_totals();
            
            // Guardar orden
            $order->save();
            
            return array(
                'success' => true,
                'order_id' => $order->get_id(),
                'booking_code' => 'MET-' . $order->get_id(),
                'message' => 'Reserva creada exitosamente'
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error al crear la reserva: ' . $e->getMessage()
            );
        }
    }
}
