<?php
/**
 * Clase para manejar el flujo de conversación del chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Conversation_Flow {
    
    /**
     * Procesar mensaje del usuario
     */
    public function process_message($message, $step, $data) {
        $response = array(
            'message' => '',
            'nextStep' => '',
            'options' => array(),
            'data' => $data
        );
        
        switch ($step) {
            case 'welcome':
                $response = $this->step_welcome();
                break;
                
            case 'route_type':
                $response = $this->step_route_type($message, $data);
                break;
                
            case 'airport_origin':
                $response = $this->step_airport_origin($message, $data);
                break;
                
            case 'destination':
                $response = $this->step_destination($message, $data);
                break;
                
            case 'passengers':
                $response = $this->step_passengers($message, $data);
                break;
                
            case 'pet':
                $response = $this->step_pet($message, $data);
                break;
                
            case 'datetime':
                $response = $this->step_datetime($message, $data);
                break;
                
            case 'flight_number':
                $response = $this->step_flight_number($message, $data);
                break;
                
            case 'point_to_point_origin':
                $response = $this->step_point_to_point_origin($message, $data);
                break;
                
            case 'point_to_point_destination':
                $response = $this->step_point_to_point_destination($message, $data);
                break;
                
            case 'verify_booking_code':
                $response = $this->step_verify_booking_code($message, $data);
                break;
                
            case 'custom_origin':
                $response = $this->step_custom_origin($message, $data);
                break;
                
            case 'custom_destination':
                $response = $this->step_custom_destination($message, $data);
                break;
                
            default:
                $response = $this->step_welcome();
        }
        
        return $response;
    }
    
    /**
     * Paso 1: Bienvenida
     */
    private function step_welcome() {
        return array(
            'message' => '👋 ¡Hola! Soy el asistente de MET Mallorca. Te ayudo a reservar en 1 minuto.<br><br>¿De dónde a dónde viajas?',
            'nextStep' => 'route_type',
            'options' => array(
                array('text' => '<i class="fas fa-plane"></i> Aeropuerto ↔ Punto (hotel o casa)', 'value' => 'airport'),
                array('text' => '<i class="fas fa-car"></i> Punto ↔ Punto dentro de Mallorca', 'value' => 'point_to_point'),
                array('text' => '<i class="fas fa-search"></i> Verificar mi reserva', 'value' => 'verify')
            ),
            'data' => array()
        );
    }
    
    /**
     * Paso 2: Tipo de ruta
     */
    private function step_route_type($message, $data) {
        $data['route_type'] = $message;
        
        if ($message === 'verify') {
            return array(
                'message' => '🔍 Por favor, escribe tu número de reserva (ej. MET-123456) y tu email separados por coma.<br><br>Ejemplo: MET-123456, email@ejemplo.com',
                'nextStep' => 'verify_booking_code',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text'
            );
        }
        
        if ($message === 'point_to_point') {
            return array(
                'message' => '📍 Perfecto. ¿Desde qué punto de Mallorca sales?<br><br>Escribe la ciudad o dirección de origen:',
                'nextStep' => 'point_to_point_origin',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text'
            );
        }
        
        // Ruta aeropuerto
        $origin_options = $this->get_origin_options();
        return array(
            'message' => '✈️ Perfecto. ¿Desde dónde te recogemos?',
            'nextStep' => 'airport_origin',
            'options' => $origin_options,
            'data' => $data
        );
    }
    
    /**
     * Paso 3: Origen aeropuerto
     */
    private function step_airport_origin($message, $data) {
        $data['origin'] = $message;
        
        if ($message === 'other_location') {
            return array(
                'message' => '📍 Escribe el nombre de tu ubicación de origen:',
                'nextStep' => 'custom_origin',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text'
            );
        }
        
        // Obtener opciones de destino según el origen
        $destination_options = $this->get_destination_options($message);
        
        return array(
            'message' => '📍 ¿Cuál es tu destino?',
            'nextStep' => 'destination',
            'options' => $destination_options,
            'data' => $data
        );
    }
    
    /**
     * Paso 4: Destino
     */
    private function step_destination($message, $data) {
        if ($message === 'other_city') {
            return array(
                'message' => '📍 Escribe el nombre de tu destino:',
                'nextStep' => 'custom_destination',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text'
            );
        }
        
        $data['destination'] = $message;
        
        return array(
            'message' => '👥 ¿Cuántas personas viajan?',
            'nextStep' => 'passengers',
            'options' => array(),
            'data' => $data,
            'inputType' => 'number'
        );
    }
    
    /**
     * Paso 5: Número de pasajeros
     */
    private function step_passengers($message, $data) {
        $passengers = intval($message);
        $data['passengers'] = $passengers;
        
        // Si son más de 20 personas, derivar a presupuesto
        if ($passengers > 20) {
            return array(
                'message' => '👥 Son ' . $passengers . ' pasajeros, perfecto.<br><br>Para grupos de más de 20 personas, gestionamos el traslado mediante un presupuesto personalizado.',
                'nextStep' => 'complete_group',
                'options' => array(
                    array('text' => '📋 Solicitar presupuesto de grupo', 'value' => 'request_quote')
                ),
                'data' => $data
            );
        }
        
        // Si es punto a punto, ir directo al formulario
        if (isset($data['route_type']) && $data['route_type'] === 'point_to_point') {
            // Generar resumen
            $summary = $this->generate_summary($data);
            
            return array(
                'message' => '✅ ' . $summary . '<br><br>Completa tu solicitud de presupuesto en el siguiente formulario:',
                'nextStep' => 'show_booking_form',
                'options' => array(
                    array('text' => '<i class="fas fa-check-circle"></i> Continuar con la solicitud', 'value' => 'show_form')
                ),
                'data' => $data
            );
        }
        
        // Flujo normal de aeropuerto
        return array(
            'message' => '🐾 ¿Viajas con mascota?',
            'nextStep' => 'pet',
            'options' => array(
                array('text' => '<i class="fas fa-dog"></i> Sí, perro pequeño', 'value' => 'small_dog'),
                array('text' => '<i class="fas fa-dog"></i> Sí, perro grande', 'value' => 'large_dog'),
                array('text' => '<i class="fas fa-cat"></i> Sí, gato', 'value' => 'cat'),
                array('text' => '<i class="fas fa-times-circle"></i> No', 'value' => 'no')
            ),
            'data' => $data
        );
    }
    
    /**
     * Paso 6: Mascota
     */
    private function step_pet($message, $data) {
        $data['pet'] = $message;
        
        return array(
            'message' => '📅 ¿Fecha y hora del traslado?<br><br>Formato: DD/MM/YYYY - HH:MM<br>Ejemplo: 15/11/2025 - 09:00',
            'nextStep' => 'datetime',
            'options' => array(),
            'data' => $data,
            'inputType' => 'text'
        );
    }
    
    /**
     * Paso 7: Fecha y hora
     */
    private function step_datetime($message, $data) {
        $data['datetime'] = $message;
        
        return array(
            'message' => '✈️ ¿Número de vuelo? (opcional)<br><br>Si no tienes, escribe "No" o "Skip"',
            'nextStep' => 'flight_number',
            'options' => array(),
            'data' => $data,
            'inputType' => 'text'
        );
    }
    
    /**
     * Paso 8: Número de vuelo y resumen
     */
    private function step_flight_number($message, $data) {
        $data['flight_number'] = ($message === 'No' || $message === 'Skip') ? '' : $message;
        
        // Generar resumen
        $summary = $this->generate_summary($data);
        
        return array(
            'message' => '✅ ' . $summary . '<br><br>Completa tu reserva en el siguiente formulario:',
            'nextStep' => 'show_booking_form',
            'options' => array(
                array('text' => '<i class="fas fa-check-circle"></i> Continuar con la reserva', 'value' => 'show_form')
            ),
            'data' => $data
        );
    }
    
    /**
     * Punto a punto - Origen
     */
    private function step_point_to_point_origin($message, $data) {
        $data['origin'] = $message;
        
        return array(
            'message' => '📍 ¿Cuál es tu destino?<br><br>Escribe la ciudad o dirección de destino:',
            'nextStep' => 'point_to_point_destination',
            'options' => array(),
            'data' => $data,
            'inputType' => 'text'
        );
    }
    
    /**
     * Punto a punto - Destino
     */
    private function step_point_to_point_destination($message, $data) {
        $data['destination'] = $message;
        
        return array(
            'message' => '👥 ¿Cuántas personas viajan?',
            'nextStep' => 'passengers',
            'options' => array(),
            'data' => $data,
            'inputType' => 'number'
        );
    }
    
    /**
     * Mostrar formulario de reserva
     */
    private function step_show_booking_form($message, $data) {
        // Generar el shortcode del formulario con los datos prellenados
        $booking_form_html = $this->generate_booking_form($data);
        
        return array(
            'message' => $booking_form_html,
            'nextStep' => 'form_displayed',
            'options' => array(),
            'data' => $data,
            'showForm' => true
        );
    }
    
    /**
     * Verificar código de reserva
     */
    private function step_verify_booking_code($message, $data) {
        // El mensaje debe contener: código, email
        $parts = explode(',', $message);
        
        if (count($parts) < 2) {
            return array(
                'message' => '❌ Por favor, proporciona el código de reserva y email separados por coma.<br><br>Ejemplo: MET-123456, email@ejemplo.com',
                'nextStep' => 'verify_booking_code',
                'options' => array(),
                'data' => $data,
                'inputType' => 'text'
            );
        }
        
        $data['booking_code'] = trim($parts[0]);
        $data['email'] = trim($parts[1]);
        
        return array(
            'message' => '🔍 Verificando tu reserva...',
            'nextStep' => 'verify_result',
            'options' => array(),
            'data' => $data,
            'action' => 'verify_booking'
        );
    }
    
    /**
     * Generar resumen de la reserva
     */
    private function generate_summary($data) {
        $summary = '<strong>Resumen de tu reserva:</strong><br>';
        
        if (isset($data['route_type']) && $data['route_type'] === 'airport') {
            $summary .= '✈️ ' . $data['origin'] . ' → ' . $data['destination'] . '<br>';
        } else {
            $summary .= '🚗 ' . $data['origin'] . ' → ' . $data['destination'] . '<br>';
        }
        
        $summary .= '👥 ' . $data['passengers'] . ' persona(s)<br>';
        
        if (isset($data['pet']) && $data['pet'] !== 'no') {
            $summary .= '🐾 Mascota: ' . $this->format_pet($data['pet']) . '<br>';
        }
        
        $summary .= '📅 ' . $data['datetime'] . '<br>';
        
        if (!empty($data['flight_number'])) {
            $summary .= '✈️ Vuelo: ' . $data['flight_number'] . '<br>';
        }
        
        return $summary;
    }
    
    /**
     * Formatear tipo de mascota
     */
    private function format_pet($pet) {
        $pets = array(
            'small_dog' => 'Perro pequeño',
            'large_dog' => 'Perro grande',
            'cat' => 'Gato',
            'no' => 'No'
        );
        
        return isset($pets[$pet]) ? $pets[$pet] : $pet;
    }
    
    /**
     * Generar URL de reserva con datos prellenados
     */
    private function generate_booking_url($data) {
        // Aquí debes poner la URL real de tu formulario de reservas
        $base_url = home_url('/reservar/');
        
        $params = array(
            'origin' => urlencode($data['origin']),
            'destination' => urlencode($data['destination']),
            'passengers' => $data['passengers'],
            'datetime' => urlencode($data['datetime'])
        );
        
        if (!empty($data['pet']) && $data['pet'] !== 'no') {
            $params['pet'] = $data['pet'];
        }
        
        if (!empty($data['flight_number'])) {
            $params['flight'] = urlencode($data['flight_number']);
        }
        
        return add_query_arg($params, $base_url);
    }
    
    /**
     * Generar formulario de reserva embebido
     */
    private function generate_booking_form($data) {
        // ID del formulario de reservas (ajusta según tu configuración)
        $form_id = 4327;
        
        // Generar el shortcode básico
        $shortcode = '[chbs_booking_form booking_form_id="' . $form_id . '"]';
        
        // Simular parámetros GET para que el plugin Chauffeur Booking System los detecte
        $this->set_chauffeur_get_params($data);
        
        // Procesar el shortcode
        $form_html = do_shortcode($shortcode);
        
        // Limpiar parámetros GET simulados
        $this->clear_chauffeur_get_params($data);
        
        // Si el shortcode no se procesa (plugin no activo), mostrar mensaje alternativo
        if ($form_html === $shortcode) {
            $form_html = '<div class="met-booking-form-fallback">';
            $form_html .= '<p><strong>📋 Resumen de tu reserva:</strong></p>';
            $form_html .= $this->generate_summary($data);
            $form_html .= '<br><br>';
            $form_html .= '<p>Para completar tu reserva, por favor <a href="' . $this->generate_booking_url($data) . '" target="_blank" class="met-booking-link">haz clic aquí</a>.</p>';
            $form_html .= '</div>';
        }
        
        return $form_html;
    }
    
    /**
     * Simular parámetros GET para que Chauffeur Booking System los detecte
     * El plugin usa CHBSRequestData::get() que lee de $_GET
     */
    private function set_chauffeur_get_params($data) {
        // Determinar el tipo de servicio (1 = Distance, 2 = Hourly, 3 = Point to Point)
        $service_type_id = isset($data['route_type']) && $data['route_type'] === 'point_to_point' ? 3 : 1;
        
        // Establecer tipo de servicio
        $_GET['service_type_id'] = $service_type_id;
        
        // Parsear fecha y hora
        if (!empty($data['datetime'])) {
            $datetime_parts = explode(' - ', $data['datetime']);
            if (count($datetime_parts) == 2) {
                // Usar nombres de campos específicos para el tipo de servicio
                $_GET['pickup_date'] = $datetime_parts[0];
                $_GET['pickup_time'] = $datetime_parts[1];
                
                // También establecer para el tipo de servicio específico
                $_GET['pickup_date_service_type_' . $service_type_id] = $datetime_parts[0];
                $_GET['pickup_time_service_type_' . $service_type_id] = $datetime_parts[1];
            }
        }
        
        // Número de pasajeros
        if (!empty($data['passengers'])) {
            $_GET['passenger'] = intval($data['passengers']);
            $_GET['passenger_service_type_' . $service_type_id] = intval($data['passengers']);
        }
        
        // Intentar prellenar ubicaciones si el plugin está activo
        if (class_exists('CHBSLocation')) {
            // Buscar ID de ubicación de origen
            if (!empty($data['origin'])) {
                $pickup_location_id = $this->find_chauffeur_location_id($data['origin']);
                if ($pickup_location_id) {
                    $_GET['fixed_location_pickup'] = $pickup_location_id;
                    $_GET['fixed_location_pickup_service_type_' . $service_type_id] = $pickup_location_id;
                }
            }
            
            // Buscar ID de ubicación de destino
            if (!empty($data['destination'])) {
                $dropoff_location_id = $this->find_chauffeur_location_id($data['destination']);
                if ($dropoff_location_id) {
                    $_GET['fixed_location_dropoff'] = $dropoff_location_id;
                    $_GET['fixed_location_dropoff_service_type_' . $service_type_id] = $dropoff_location_id;
                }
            }
        }
    }
    
    /**
     * Buscar ID de ubicación en Chauffeur Booking System por nombre
     */
    private function find_chauffeur_location_id($location_name) {
        if (!class_exists('CHBSLocation')) {
            return null;
        }
        
        // Mapeo de nombres comunes a nombres exactos del plugin
        $common_mappings = array(
            'aeropuerto' => 'Airport - PMI',
            'aeropuerto de palma' => 'Airport - PMI',
            'aeropuerto pmi' => 'Airport - PMI',
            'pmi' => 'Airport - PMI',
            'airport' => 'Airport - PMI',
            'puerto pollensa' => 'Puerto Pollesa',
            'port pollensa' => 'Puerto Pollesa',
            'puerto de pollensa' => 'Puerto Pollesa',
            'alcudia' => 'Alcudia',
            'puerto de alcudia' => 'Puerto de Alcudia',
            'port alcudia' => 'Puerto de Alcudia',
            'palma nova' => 'Palma Nova',
            'magaluf' => 'Magalluf',
            'cala millor' => 'Cala Millor',
            'cala dor' => 'Cala D´or',
            'cala d\'or' => 'Cala D´or'
        );
        
        $Location = new CHBSLocation();
        $locations = $Location->getDictionary();
        
        // Normalizar el nombre de búsqueda
        $search_name = strtolower(trim($location_name));
        
        // Primero intentar con el mapeo de nombres comunes
        if (isset($common_mappings[$search_name])) {
            $search_name = strtolower($common_mappings[$search_name]);
        }
        
        // Buscar coincidencia exacta
        foreach ($locations as $location_id => $location_data) {
            $location_title = strtolower($location_data['post']->post_title);
            
            if ($location_title === $search_name) {
                return $location_id;
            }
        }
        
        // Buscar coincidencia parcial (contiene)
        foreach ($locations as $location_id => $location_data) {
            $location_title = strtolower($location_data['post']->post_title);
            
            // Buscar si el título contiene el nombre de búsqueda
            if (strpos($location_title, $search_name) !== false) {
                return $location_id;
            }
            
            // Buscar si el nombre de búsqueda contiene el título
            if (strpos($search_name, $location_title) !== false) {
                return $location_id;
            }
        }
        
        // Búsqueda por palabras clave (para casos como "Aeropuerto" → "Airport")
        $keywords = array(
            'aeropuerto' => 'airport',
            'puerto' => 'port',
            'playa' => 'playa',
            'cala' => 'cala'
        );
        
        foreach ($keywords as $spanish => $english) {
            if (strpos($search_name, $spanish) !== false) {
                foreach ($locations as $location_id => $location_data) {
                    $location_title = strtolower($location_data['post']->post_title);
                    if (strpos($location_title, $english) !== false) {
                        return $location_id;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Limpiar parámetros GET simulados
     */
    private function clear_chauffeur_get_params($data) {
        $service_type_id = isset($data['route_type']) && $data['route_type'] === 'point_to_point' ? 3 : 1;
        
        // Limpiar parámetros generales
        unset($_GET['service_type_id']);
        unset($_GET['pickup_date']);
        unset($_GET['pickup_time']);
        unset($_GET['passenger']);
        unset($_GET['fixed_location_pickup']);
        unset($_GET['fixed_location_dropoff']);
        
        // Limpiar parámetros específicos del tipo de servicio
        unset($_GET['pickup_date_service_type_' . $service_type_id]);
        unset($_GET['pickup_time_service_type_' . $service_type_id]);
        unset($_GET['passenger_service_type_' . $service_type_id]);
        unset($_GET['fixed_location_pickup_service_type_' . $service_type_id]);
        unset($_GET['fixed_location_dropoff_service_type_' . $service_type_id]);
    }
    
    /**
     * Obtener ubicaciones del plugin Chauffeur Booking System categorizadas
     */
    private function get_chauffeur_locations() {
        if (!class_exists('CHBSLocation')) {
            return array(
                'airport' => array(),
                'cities' => array(),
                'all' => array()
            );
        }
        
        $Location = new CHBSLocation();
        $locations = $Location->getDictionary();
        
        $categorized = array(
            'airport' => array(),
            'cities' => array(),
            'all' => array()
        );
        
        foreach ($locations as $location_id => $location_data) {
            $name = $location_data['post']->post_title;
            $name_lower = strtolower($name);
            
            // Categorizar ubicaciones
            if (strpos($name_lower, 'airport') !== false || 
                strpos($name_lower, 'aeropuerto') !== false ||
                strpos($name_lower, 'pmi') !== false) {
                $categorized['airport'][$location_id] = $name;
            } else {
                $categorized['cities'][$location_id] = $name;
            }
            
            $categorized['all'][$location_id] = $name;
        }
        
        return $categorized;
    }
    
    /**
     * Obtener opciones de destino según el origen seleccionado
     */
    private function get_destination_options($origin) {
        $locations = $this->get_chauffeur_locations();
        $options = array();
        
        // Si el origen es el aeropuerto, mostrar todas las ciudades como destino
        if (strpos(strtolower($origin), 'airport') !== false || 
            strpos(strtolower($origin), 'aeropuerto') !== false ||
            strpos(strtolower($origin), 'pmi') !== false) {
            
            // Destinos populares primero
            $popular_destinations = array(
                'Palma', 'Alcudia', 'Cala Millor', 'Magalluf', 
                'Palma Nova', 'Santa Ponsa', 'Port de Soller',
                'Cala Ratjada', 'Cala D´or', 'Puerto de Alcudia'
            );
            
            foreach ($popular_destinations as $dest) {
                foreach ($locations['cities'] as $id => $name) {
                    if (strtolower($name) === strtolower($dest)) {
                        $options[] = array(
                            'text' => '📍 ' . $name,
                            'value' => $name
                        );
                        break;
                    }
                }
            }
            
            // Agregar "Otras ciudades" al final
            $options[] = array(
                'text' => '📍 Otra ciudad...',
                'value' => 'other_city'
            );
            
        } else {
            // Si el origen es una ciudad, el aeropuerto es el principal destino
            foreach ($locations['airport'] as $id => $name) {
                $options[] = array(
                    'text' => '✈️ ' . $name,
                    'value' => $name
                );
            }
            
            // También permitir otras ciudades
            $options[] = array(
                'text' => '📍 Otra ciudad...',
                'value' => 'other_city'
            );
        }
        
        return $options;
    }
    
    /**
     * Obtener opciones de origen
     */
    private function get_origin_options() {
        $locations = $this->get_chauffeur_locations();
        $options = array();
        
        // Opción principal: Aeropuerto
        foreach ($locations['airport'] as $id => $name) {
            $options[] = array(
                'text' => '✈️ ' . $name,
                'value' => $name
            );
            break; // Solo mostrar el primer aeropuerto
        }
        
        // Opción: Desde otra ubicación
        $options[] = array(
            'text' => '📍 Desde otra ubicación...',
            'value' => 'other_location'
        );
        
        return $options;
    }
    
    /**
     * Paso personalizado: Origen personalizado
     */
    private function step_custom_origin($message, $data) {
        $data['origin'] = $message;
        
        // Obtener opciones de destino según el origen personalizado
        $destination_options = $this->get_destination_options($message);
        
        return array(
            'message' => '📍 ¿Cuál es tu destino?',
            'nextStep' => 'destination',
            'options' => $destination_options,
            'data' => $data
        );
    }
    
    /**
     * Paso personalizado: Destino personalizado
     */
    private function step_custom_destination($message, $data) {
        $data['destination'] = $message;
        
        return array(
            'message' => '👥 ¿Cuántas personas viajan?',
            'nextStep' => 'passengers',
            'options' => array(),
            'data' => $data,
            'inputType' => 'number'
        );
    }
    
    /**
     * Generar resumen de reserva en formato estructurado (para usar en emails o notificaciones)
     */
    public function generate_booking_summary_structured($data) {
        return array(
            'tipo_servicio' => isset($data['route_type']) && $data['route_type'] === 'point_to_point' ? 'Punto a Punto' : 'Aeropuerto',
            'origen' => $data['origin'],
            'destino' => $data['destination'],
            'pasajeros' => $data['passengers'],
            'mascota' => isset($data['pet']) && $data['pet'] !== 'no' ? $this->format_pet($data['pet']) : 'No',
            'fecha_hora' => $data['datetime'],
            'vuelo' => !empty($data['flight_number']) ? $data['flight_number'] : 'N/A'
        );
    }
}
