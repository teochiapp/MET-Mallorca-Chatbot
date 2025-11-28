<?php
/**
 * Sistema de traducciones para el backend del chatbot
 * Idiomas: Español (ES), English (EN-GB), Deutsch (DE)
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Translations {
    
    private static $current_lang = 'es';
    
    private static $translations = array(
        // ========== ESPAÑOL ==========
        'es' => array(
            // Welcome step
            'welcome_title' => '¡Bienvenido a MET Mallorca!',
            'welcome_message' => 'Soy tu asistente de reservas. Te ayudaré a calcular el precio de tu traslado y generar tu reserva en menos de 2 minutos.',
            'welcome_question' => '¿Qué tipo de traslado necesitas?',
            'option_airport' => '<i class="fas fa-plane"></i> Aeropuerto ↔ Destino',
            'option_point_to_airport' => '<i class="fas fa-car"></i> Punto → Aeropuerto (PMI)',
            'option_verify' => '<i class="fas fa-search"></i> Verificar mi reserva',
            
            // Route type
            'route_airport_title' => 'Traslado desde el Aeropuerto',
            'route_airport_question' => 'Perfecto, ¿a qué destino te llevamos?',
            'route_point_title' => 'Traslado hacia el Aeropuerto',
            'route_point_question' => 'Perfecto. Busca y selecciona tu ubicación de origen:',
            
            // Verify booking
            'verify_title' => 'Verificar Reserva',
            'verify_message' => 'Por favor, escribe tu <strong>número de reserva</strong> y tu <strong>email</strong> separados por coma.',
            'verify_example' => 'Ejemplo: MET-123456, email@ejemplo.com',
            
            // Location
            'location_origin_title' => 'Ubicación de Origen',
            'location_origin_question' => 'Busca y selecciona tu ubicación de recogida:',
            'location_destination_title' => '¿Cuál es tu destino?',
            'location_destination_question' => 'Busca y selecciona tu ubicación de destino:',
            
            // Date
            'date_title' => '¿Qué día necesitas el traslado?',
            'date_format' => 'Escribe la fecha en formato <strong>DD/MM/YYYY</strong>',
            'date_example' => 'Ejemplo: 25/12/2025',
            'date_error_retry' => 'Por favor, intenta de nuevo:',
            
            // Time
            'time_title' => '¿A qué hora necesitas el traslado?',
            'time_message' => 'Selecciona una hora disponible (intervalos de 30 minutos).',
            'time_example' => 'Ejemplo: 14:00, 14:30, 15:00…',
            
            // Passengers
            'passengers_title' => '¿Cuántas personas viajan?',
            'passengers_question' => 'Escribe el número de pasajeros:',
            'passengers_example' => 'Ej: 4',
            'passengers_large_group' => 'Grupo Grande',
            'passengers_large_message' => 'Para grupos de más de 20 personas, te recomendamos solicitar un presupuesto personalizado.',
            'passengers_contact' => 'Por favor, contacta con nosotros en:',
            
            // Extras
            'extras_title' => 'Opciones Extras',
            'extras_message' => 'Selecciona las opciones adicionales que necesites para tu viaje:',
            'extras_selected_summary' => 'Opciones extras seleccionadas',
            'extras_confirmed' => 'Opciones extras confirmadas',
            'extras_none' => 'Sin opciones extras',
            'extras_none_message' => 'Continuaremos sin servicios adicionales.',
            'extras_continue' => 'Continuemos con el resumen de tu reserva...',
            'extras_hand_luggage' => 'Equipaje de mano',
            'extras_suitcases' => 'Valijas',
            'extras_booster_seats' => 'Alzadores',
            'extras_baby_seats' => 'Sillas de bebé',
            'extras_golf_bag' => 'Bolsa de Golf',
            'extras_bicycle' => 'Bicicleta',
            'extras_free' => 'Gratis',
            'extras_each' => 'c/u',
            'extras_confirm' => 'Confirmar opciones',
            'extras_total' => 'Total extras',
            'price_breakdown_title' => 'Desglose del precio',
            'price_location' => 'Ubicación',
            'price_distance' => 'Distancia',
            'price_distance_unit' => 'km',
            'price_base' => 'Precio base',
            'price_vehicle' => 'Vehículo',
            'price_vehicle_supplement' => 'Suplemento de vehículo',
            'price_night_supplement' => 'Suplemento nocturno',
            'price_passenger_supplement' => 'Pasajeros extra',
            'price_total' => 'TOTAL',
            'price_extra_pet' => '🐾 Mascota',
            'price_extra_child_seat' => '👶 Silla infantil',
            'price_extra_booster_seat' => '🪑 Elevador',
            'price_extra_luggage_extra' => '🧳 Equipaje extra',
            'price_extra_meet_greet' => '👋 Meet & Greet',
            'price_extra_bolsa_golf' => '⛳ Bolsa de Golf',
            'price_extra_bicicleta' => '🚴 Bicicleta',
            
            // Summary
            'summary_title' => 'Resumen de tu Reserva',
            'summary_route' => 'Ruta',
            'summary_datetime' => 'Fecha y Hora',
            'summary_passengers' => 'Pasajeros',
            'summary_vehicle' => 'Vehículo',
            'summary_extras' => 'Opciones Extras',
            'summary_question' => '¿Todo correcto?',
            'summary_continue_checkout' => '<i class="fas fa-check-circle"></i> Sí, continuar al checkout',
            'summary_modify_data' => '<i class="fas fa-edit"></i> Modificar datos',
            'summary_error_missing' => 'Error: Faltan datos necesarios para calcular el precio.',
            'summary_error_fields' => 'Campos faltantes',
            
            // Confirm
            'confirm_perfect' => '¡Perfecto!',
            'confirm_message' => 'Tu reserva está lista. Haz clic en el botón de abajo para ir al checkout seguro y completar el pago.',
            'confirm_checkout_button' => 'Ir al Checkout',
            'confirm_payment_secure' => 'Pago seguro con Redsys/Getnet a través de WooCommerce',
            'confirm_data_protected' => 'Tus datos están protegidos',
            'confirm_another_booking' => 'Hacer otra reserva',
            
            // Modify
            'modify_title' => '¿Qué deseas modificar?',
            'modify_locations' => 'Origen/Destino',
            'modify_datetime' => 'Fecha/Hora',
            'modify_passengers' => 'Pasajeros',
            'modify_start_over' => 'Empezar de nuevo',
            
            // Vehicle types
            'vehicle_standard' => 'Vehículo Estándar (1-4 pax)',
            'vehicle_van' => 'Van (5-8 pax)',
            'vehicle_minibus' => 'Minibus (9-16 pax)',
            'vehicle_bus' => 'Bus (17-20 pax)',
            
            // Location options
            'location_airport' => '<i class="fas fa-plane"></i> Aeropuerto',
            'location_hotel' => '<i class="fas fa-hotel"></i> Hotel / Alojamiento',
        ),
        
        // ========== ENGLISH (British) ==========
        'en' => array(
            // Welcome step
            'welcome_title' => 'Welcome to MET Mallorca!',
            'welcome_message' => 'I\'m your booking assistant. I\'ll help you calculate the price of your transfer and generate your booking in less than 2 minutes.',
            'welcome_question' => 'What type of transfer do you need?',
            'option_airport' => '<i class="fas fa-plane"></i> Airport ↔ Destination',
            'option_point_to_airport' => '<i class="fas fa-car"></i> Point → Airport (PMI)',
            'option_verify' => '<i class="fas fa-search"></i> Verify my booking',
            
            // Route type
            'route_airport_title' => 'Transfer from the Airport',
            'route_airport_question' => 'Perfect, where shall we take you?',
            'route_point_title' => 'Transfer to the Airport',
            'route_point_question' => 'Perfect. Search and select your origin location:',
            
            // Verify booking
            'verify_title' => 'Verify Booking',
            'verify_message' => 'Please enter your <strong>booking number</strong> and your <strong>email</strong> separated by a comma.',
            'verify_example' => 'Example: MET-123456, email@example.com',
            
            // Location
            'location_origin_title' => 'Origin Location',
            'location_origin_question' => 'Search and select your pick-up location:',
            'location_destination_title' => 'What is your destination?',
            'location_destination_question' => 'Search and select your destination location:',
            
            // Date
            'date_title' => 'What day do you need the transfer?',
            'date_format' => 'Enter the date in <strong>DD/MM/YYYY</strong> format',
            'date_example' => 'Example: 25/12/2025',
            'date_error_retry' => 'Please try again:',
            
            // Time
            'time_title' => 'What time do you need the transfer?',
            'time_message' => 'Select an available time (30-minute intervals).',
            'time_example' => 'Example: 14:00, 14:30, 15:00…',
            
            // Passengers
            'passengers_title' => 'How many people are travelling?',
            'passengers_question' => 'Enter the number of passengers:',
            'passengers_example' => 'E.g.: 4',
            'passengers_large_group' => 'Large Group',
            'passengers_large_message' => 'For groups of more than 20 people, we recommend requesting a personalised quote.',
            'passengers_contact' => 'Please contact us at:',
            
            // Extras
            'extras_title' => 'Extra Options',
            'extras_message' => 'Select the additional options you need for your journey:',
            'extras_selected_summary' => 'Selected extra options',
            'extras_confirmed' => 'Extra options confirmed',
            'extras_none' => 'No extra options',
            'extras_none_message' => 'We\'ll continue without additional services.',
            'extras_continue' => 'Let\'s continue with your booking summary...',
            'extras_hand_luggage' => 'Hand luggage',
            'extras_suitcases' => 'Suitcases',
            'extras_booster_seats' => 'Booster seats',
            'extras_baby_seats' => 'Baby seats',
            'extras_golf_bag' => 'Golf bag',
            'extras_bicycle' => 'Bicycle',
            'extras_free' => 'Free',
            'extras_each' => 'each',
            'extras_confirm' => 'Confirm options',
            'extras_total' => 'Extras total',
            'price_breakdown_title' => 'Price breakdown',
            'price_location' => 'Location',
            'price_distance' => 'Distance',
            'price_distance_unit' => 'km',
            'price_base' => 'Base price',
            'price_vehicle' => 'Vehicle',
            'price_vehicle_supplement' => 'Vehicle supplement',
            'price_night_supplement' => 'Night supplement',
            'price_passenger_supplement' => 'Extra passengers',
            'price_total' => 'TOTAL',
            'price_extra_pet' => '🐾 Pet',
            'price_extra_child_seat' => '👶 Child seat',
            'price_extra_booster_seat' => '🪑 Booster seat',
            'price_extra_luggage_extra' => '🧳 Extra luggage',
            'price_extra_meet_greet' => '👋 Meet & Greet',
            'price_extra_bolsa_golf' => '⛳ Golf bag',
            'price_extra_bicicleta' => '🚴 Bicycle',
            
            // Summary
            'summary_title' => 'Your Booking Summary',
            'summary_route' => 'Route',
            'summary_datetime' => 'Date and Time',
            'summary_passengers' => 'Passengers',
            'summary_vehicle' => 'Vehicle',
            'summary_extras' => 'Extra Options',
            'summary_question' => 'Is everything correct?',
            'summary_continue_checkout' => '<i class="fas fa-check-circle"></i> Yes, continue to checkout',
            'summary_modify_data' => '<i class="fas fa-edit"></i> Modify details',
            'summary_error_missing' => 'Error: Missing data required to calculate the price.',
            'summary_error_fields' => 'Missing fields',
            
            // Confirm
            'confirm_perfect' => 'Perfect!',
            'confirm_message' => 'Your booking is ready. Click the button below to go to secure checkout and complete payment.',
            'confirm_checkout_button' => 'Go to Checkout',
            'confirm_payment_secure' => 'Secure payment with Redsys/Getnet through WooCommerce',
            'confirm_data_protected' => 'Your data is protected',
            'confirm_another_booking' => 'Make another booking',
            
            // Modify
            'modify_title' => 'What would you like to modify?',
            'modify_locations' => 'Origin/Destination',
            'modify_datetime' => 'Date/Time',
            'modify_passengers' => 'Passengers',
            'modify_start_over' => 'Start over',
            
            // Vehicle types
            'vehicle_standard' => 'Standard Vehicle (1-4 pax)',
            'vehicle_van' => 'Van (5-8 pax)',
            'vehicle_minibus' => 'Minibus (9-16 pax)',
            'vehicle_bus' => 'Bus (17-20 pax)',
            
            // Location options
            'location_airport' => '<i class="fas fa-plane"></i> Airport',
            'location_hotel' => '<i class="fas fa-hotel"></i> Hotel / Accommodation',
        ),
        
        // ========== DEUTSCH ==========
        'de' => array(
            // Welcome step
            'welcome_title' => 'Willkommen bei MET Mallorca!',
            'welcome_message' => 'Ich bin Ihr Buchungsassistent. Ich helfe Ihnen, den Preis Ihres Transfers zu berechnen und Ihre Buchung in weniger als 2 Minuten zu erstellen.',
            'welcome_question' => 'Welche Art von Transfer benötigen Sie?',
            'option_airport' => '<i class="fas fa-plane"></i> Flughafen ↔ Zielort',
            'option_point_to_airport' => '<i class="fas fa-car"></i> Punkt → Flughafen (PMI)',
            'option_verify' => '<i class="fas fa-search"></i> Meine Buchung überprüfen',
            
            // Route type
            'route_airport_title' => 'Transfer vom Flughafen',
            'route_airport_question' => 'Perfekt, wohin sollen wir Sie bringen?',
            'route_point_title' => 'Transfer zum Flughafen',
            'route_point_question' => 'Perfekt. Suchen und wählen Sie Ihren Startort:',
            
            // Verify booking
            'verify_title' => 'Buchung überprüfen',
            'verify_message' => 'Bitte geben Sie Ihre <strong>Buchungsnummer</strong> und Ihre <strong>E-Mail</strong> durch Komma getrennt ein.',
            'verify_example' => 'Beispiel: MET-123456, email@beispiel.com',
            
            // Location
            'location_origin_title' => 'Startort',
            'location_origin_question' => 'Suchen und wählen Sie Ihren Abholort:',
            'location_destination_title' => 'Was ist Ihr Zielort?',
            'location_destination_question' => 'Suchen und wählen Sie Ihren Zielort:',
            
            // Date
            'date_title' => 'An welchem Tag benötigen Sie den Transfer?',
            'date_format' => 'Geben Sie das Datum im Format <strong>TT/MM/JJJJ</strong> ein',
            'date_example' => 'Beispiel: 25/12/2025',
            'date_error_retry' => 'Bitte versuchen Sie es erneut:',
            
            // Time
            'time_title' => 'Um welche Uhrzeit benötigen Sie den Transfer?',
            'time_message' => 'Wählen Sie eine verfügbare Uhrzeit (30-Minuten-Intervalle).',
            'time_example' => 'Beispiel: 14:00, 14:30, 15:00…',
            
            // Passengers
            'passengers_title' => 'Wie viele Personen reisen?',
            'passengers_question' => 'Geben Sie die Anzahl der Passagiere ein:',
            'passengers_example' => 'Z.B.: 4',
            'passengers_large_group' => 'Große Gruppe',
            'passengers_large_message' => 'Für Gruppen von mehr als 20 Personen empfehlen wir, ein individuelles Angebot anzufordern.',
            'passengers_contact' => 'Bitte kontaktieren Sie uns unter:',
            
            // Extras
            'extras_title' => 'Zusatzoptionen',
            'extras_message' => 'Wählen Sie die zusätzlichen Optionen, die Sie für Ihre Reise benötigen:',
            'extras_selected_summary' => 'Ausgewählte Zusatzoptionen',
            'extras_confirmed' => 'Zusatzoptionen bestätigt',
            'extras_none' => 'Keine Zusatzoptionen',
            'extras_none_message' => 'Wir fahren ohne zusätzliche Services fort.',
            'extras_continue' => 'Fahren wir mit Ihrer Buchungszusammenfassung fort...',
            'extras_hand_luggage' => 'Handgepäck',
            'extras_suitcases' => 'Koffer',
            'extras_booster_seats' => 'Sitzerhöhungen',
            'extras_baby_seats' => 'Kindersitze',
            'extras_golf_bag' => 'Golftasche',
            'extras_bicycle' => 'Fahrrad',
            'extras_free' => 'Kostenlos',
            'extras_each' => 'pro Stück',
            'extras_confirm' => 'Optionen bestätigen',
            'extras_total' => 'Extras gesamt',
            'price_breakdown_title' => 'Preisaufschlüsselung',
            'price_location' => 'Standort',
            'price_distance' => 'Entfernung',
            'price_distance_unit' => 'km',
            'price_base' => 'Grundpreis',
            'price_vehicle' => 'Fahrzeug',
            'price_vehicle_supplement' => 'Fahrzeugsupplement',
            'price_night_supplement' => 'Nachtzuschlag',
            'price_passenger_supplement' => 'Zusatzpassagiere',
            'price_total' => 'GESAMT',
            'price_extra_pet' => '🐾 Haustier',
            'price_extra_child_seat' => '👶 Kindersitz',
            'price_extra_booster_seat' => '🪑 Sitzerhöhung',
            'price_extra_luggage_extra' => '🧳 Zusätzliches Gepäck',
            'price_extra_meet_greet' => '👋 Meet & Greet',
            'price_extra_bolsa_golf' => '⛳ Golftasche',
            'price_extra_bicicleta' => '🚴 Fahrrad',
            
            // Summary
            'summary_title' => 'Ihre Buchungszusammenfassung',
            'summary_route' => 'Route',
            'summary_datetime' => 'Datum und Uhrzeit',
            'summary_passengers' => 'Passagiere',
            'summary_vehicle' => 'Fahrzeug',
            'summary_extras' => 'Zusatzoptionen',
            'summary_question' => 'Ist alles korrekt?',
            'summary_continue_checkout' => '<i class="fas fa-check-circle"></i> Ja, zur Kasse gehen',
            'summary_modify_data' => '<i class="fas fa-edit"></i> Details ändern',
            'summary_error_missing' => 'Fehler: Fehlende Daten zur Preisberechnung.',
            'summary_error_fields' => 'Fehlende Felder',
            
            // Confirm
            'confirm_perfect' => 'Perfekt!',
            'confirm_message' => 'Ihre Buchung ist fertig. Klicken Sie auf die Schaltfläche unten, um zur sicheren Kasse zu gehen und die Zahlung abzuschließen.',
            'confirm_checkout_button' => 'Zur Kasse gehen',
            'confirm_payment_secure' => 'Sichere Zahlung mit Redsys/Getnet über WooCommerce',
            'confirm_data_protected' => 'Ihre Daten sind geschützt',
            'confirm_another_booking' => 'Weitere Buchung vornehmen',
            
            // Modify
            'modify_title' => 'Was möchten Sie ändern?',
            'modify_locations' => 'Start/Ziel',
            'modify_datetime' => 'Datum/Uhrzeit',
            'modify_passengers' => 'Passagiere',
            'modify_start_over' => 'Von vorne beginnen',
            
            // Vehicle types
            'vehicle_standard' => 'Standardfahrzeug (1-4 Pax)',
            'vehicle_van' => 'Van (5-8 Pax)',
            'vehicle_minibus' => 'Minibus (9-16 Pax)',
            'vehicle_bus' => 'Bus (17-20 Pax)',
            
            // Location options
            'location_airport' => '<i class="fas fa-plane"></i> Flughafen',
            'location_hotel' => '<i class="fas fa-hotel"></i> Hotel / Unterkunft',
        )
    );
    
    /**
     * Establecer idioma actual
     */
    public static function set_language($lang) {
        if (isset(self::$translations[$lang])) {
            self::$current_lang = $lang;
        }
    }
    
    /**
     * Obtener idioma actual
     */
    public static function get_language() {
        return self::$current_lang;
    }
    
    /**
     * Traducir una clave
     */
    public static function t($key) {
        $lang = self::$current_lang;
        
        if (isset(self::$translations[$lang][$key])) {
            return self::$translations[$lang][$key];
        }
        
        // Fallback a español
        if (isset(self::$translations['es'][$key])) {
            return self::$translations['es'][$key];
        }
        
        return $key;
    }
    
    /**
     * Obtener idioma desde los datos de conversación
     */
    public static function init_from_data($data) {
        if (isset($data['language']) && !empty($data['language'])) {
            self::set_language($data['language']);
        }
    }
}
