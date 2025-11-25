<?php
/**
 * Steps de bienvenida y selección de tipo de ruta
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Conversation_Steps_Welcome {
    
    /**
     * Step: Bienvenida inicial
     */
    public function step_welcome() {
        return array(
            'message' => '👋 <strong>¡Bienvenido a MET Mallorca!</strong><br><br>' .
                        'Soy tu asistente de reservas. Te ayudaré a calcular el precio de tu traslado y generar tu reserva en menos de 2 minutos.<br><br>' .
                        '¿Qué tipo de traslado necesitas?',
            'nextStep' => 'route_type',
            'options' => array(
                array(
                    'text' => '<i class="fas fa-plane"></i> Aeropuerto ↔ Destino',
                    'value' => 'airport'
                ),
                array(
                    'text' => '<i class="fas fa-car"></i> Punto → Aeropuerto (PMI)',
                    'value' => 'point_to_airport'
                ),
                array(
                    'text' => '<i class="fas fa-search"></i> Verificar mi reserva',
                    'value' => 'verify'
                )
            ),
            'data' => array(),
            'showBackButton' => false
        );
    }
    
    /**
     * Step: Tipo de ruta seleccionado
     */
    public function step_route_type($message, $data) {
        $data['route_type'] = $message;
        
        // Flujo de verificación
        if ($message === 'verify') {
            return array(
                'message' => '🔍 <strong>Verificar Reserva</strong><br><br>' .
                            'Por favor, escribe tu <strong>número de reserva</strong> y tu <strong>email</strong> separados por coma.<br><br>' .
                            '<em>Ejemplo: MET-123456, email@ejemplo.com</em>',
                'nextStep' => 'verify_booking_code',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text',
                'showBackButton' => true
            );
        }
        
        // Flujo de punto → aeropuerto - Usar buscador inteligente
        if ($message === 'point_to_airport') {
            return array(
                'message' => '🚗 <strong>Traslado hacia el Aeropuerto</strong><br><br>' .
                            'Perfecto. Busca y selecciona tu ubicación de origen:',
                'nextStep' => 'origin_text',
                'options' => array(),
                'data' => $data,
                'inputType' => 'location',
                'showBackButton' => true,
                'placeholder' => 'Buscar ubicación de origen...'
            );
        }
        
        // Flujo de aeropuerto (por defecto) - evitar paso redundante
        $data['origin'] = 'Aeropuerto de Palma';

        return array(
            'message' => '✈️ <strong>Traslado desde el Aeropuerto</strong><br><br>' .
                        'Perfecto, ¿a qué destino te llevamos?',
            'nextStep' => 'destination_text',
            'options' => array(),
            'data' => $data,
            'inputType' => 'location',
            'showBackButton' => true,
            'placeholder' => 'Buscar destino...'
        );
    }
}
