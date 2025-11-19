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
                    'text' => '<i class="fas fa-map-marked-alt"></i> Punto a Punto en Mallorca',
                    'value' => 'point_to_point'
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
        
        // Flujo de punto a punto
        if ($message === 'point_to_point') {
            return array(
                'message' => '📍 <strong>Traslado Punto a Punto</strong><br><br>' .
                            'Perfecto. ¿Desde qué ubicación en Mallorca te recogemos?<br><br>' .
                            '<em>Escribe la ciudad, hotel o dirección de origen:</em>',
                'nextStep' => 'origin',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text',
                'showBackButton' => true,
                'placeholder' => 'Ej: Hotel Maricel, Palma'
            );
        }
        
        // Flujo de aeropuerto (por defecto)
        return array(
            'message' => '✈️ <strong>Traslado desde/hacia Aeropuerto</strong><br><br>' .
                        '¿Desde dónde te recogemos?',
            'nextStep' => 'origin',
            'options' => array(
                array(
                    'text' => '<i class="fas fa-plane"></i> Aeropuerto de Palma (PMI)',
                    'value' => 'Aeropuerto de Palma'
                ),
                array(
                    'text' => '<i class="fas fa-hotel"></i> Hotel / Alojamiento',
                    'value' => 'custom_origin'
                )
            ),
            'data' => $data,
            'showBackButton' => true
        );
    }
}
