<?php
/**
 * Script de diagnóstico temporal para el carrito
 * 
 * INSTRUCCIONES:
 * 1. Copia este código al final de functions.php de tu tema (antes del ?>)
 * 2. Haz una reserva en el chatbot
 * 3. Ve al checkout
 * 4. Revisa wp-content/debug.log
 * 5. ELIMINA este código después de diagnosticar
 */

// Activar logging de WooCommerce
add_action('woocommerce_before_calculate_totals', function($cart) {
    error_log('=== MET CHATBOT DEBUG - BEFORE CALCULATE TOTALS ===');
    error_log('Cart items count: ' . count($cart->get_cart()));
    
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        error_log('--- Cart Item ---');
        error_log('Product ID: ' . $cart_item['product_id']);
        error_log('Product Name: ' . $cart_item['data']->get_name());
        error_log('Product Price: ' . $cart_item['data']->get_price());
        
        if (isset($cart_item['met_custom_price'])) {
            error_log('MET Custom Price: ' . $cart_item['met_custom_price']);
        } else {
            error_log('MET Custom Price: NOT SET');
        }
        
        if (isset($cart_item['met_booking_hash'])) {
            error_log('MET Booking Hash: ' . $cart_item['met_booking_hash']);
        }
        
        if (isset($cart_item['met_booking_data'])) {
            error_log('MET Booking Data: ' . print_r($cart_item['met_booking_data'], true));
        }
    }
    error_log('=== END DEBUG ===');
}, 1, 1);

// Debug cuando se agrega al carrito
add_filter('woocommerce_add_cart_item', function($cart_item_data, $cart_item_key) {
    error_log('=== MET CHATBOT DEBUG - ADD TO CART ===');
    error_log('Cart Item Key: ' . $cart_item_key);
    error_log('Cart Item Data: ' . print_r($cart_item_data, true));
    error_log('=== END DEBUG ===');
    return $cart_item_data;
}, 10, 2);

// Debug del carrito completo
add_action('wp_footer', function() {
    if (is_checkout() && WC()->cart) {
        error_log('=== MET CHATBOT DEBUG - CHECKOUT PAGE ===');
        error_log('Cart Total: ' . WC()->cart->get_total(''));
        error_log('Cart Subtotal: ' . WC()->cart->get_subtotal());
        error_log('Cart Contents: ' . print_r(WC()->cart->get_cart(), true));
        error_log('=== END DEBUG ===');
    }
});
