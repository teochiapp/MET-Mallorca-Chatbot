/**
 * Sistema de traducciones para el Chatbot MET Mallorca
 * Idiomas: Español (ES), English (EN-GB), Deutsch (DE)
 */

(function($) {
    'use strict';
    
    window.MetTranslations = {
        // Idioma actual (por defecto español)
        currentLang: 'es',
        
        // Diccionario de traducciones
        translations: {
            // ========== ESPAÑOL ==========
            es: {
                // Header
                'assistant_title': 'Asistente MET Mallorca',
                'status_online': 'En línea',
                
                // Invitación
                'invitation_text': '¿Te ayudo con tu reserva?',
                
                // Input placeholders
                'placeholder_message': 'Escribe tu mensaje...',
                'placeholder_number': 'Escribe un número...',
                'placeholder_email': 'Escribe tu email...',
                'placeholder_location_origin': 'Buscar ubicación de origen...',
                'placeholder_location_destination': 'Buscar destino...',
                'placeholder_time': 'Buscar horario (ej: 14:30)',
                'placeholder_date': 'DD/MM/YYYY',
                
                // Botones comunes
                'btn_back': 'Volver',
                'btn_restart': 'Reiniciar',
                'btn_confirm': 'Confirmar',
                'btn_continue': 'Continuar',
                'btn_modify': 'Modificar',
                
                // Mensajes de error
                'error_connection': 'Lo siento, ha ocurrido un error de conexión. Por favor, intenta de nuevo.',
                'error_general': 'Lo siento, ha ocurrido un error. Por favor, intenta de nuevo.',
                
                // Welcome step
                'welcome_title': '¡Bienvenido a MET Mallorca!',
                'welcome_message': 'Soy tu asistente de reservas. Te ayudaré a calcular el precio de tu traslado y generar tu reserva en menos de 2 minutos.',
                'welcome_question': '¿Qué tipo de traslado necesitas?',
                'option_airport': 'Aeropuerto ↔ Destino',
                'option_point_to_airport': 'Donde Estoy → Aeropuerto (PMI)',
                'option_verify': 'Verificar mi reserva',
                
                // Route type step
                'route_airport_title': 'Traslado desde el Aeropuerto',
                'route_airport_question': 'Perfecto, ¿a qué destino te llevamos?',
                'route_point_title': 'Traslado hacia el Aeropuerto',
                'route_point_question': 'Perfecto. Busca y selecciona tu ubicación de origen:',
                
                // Verify booking
                'verify_title': 'Verificar Reserva',
                'verify_message': 'Por favor, escribe tu <strong>número de pedido</strong> y tu <strong>email</strong> separados por coma.',
                'verify_example': 'Ejemplo: 1234, email@ejemplo.com',
                'verify_error_missing_info': '❌ Por favor, proporciona el número de pedido y email separados por coma. Ejemplo: 1234, email@ejemplo.com',
                'verify_error_invalid_format': '❌ El código debe ser un número válido.',
                'verify_checking': '🔍 Verificando tu reserva...',
                'verify_error_not_met': '❌ No encontramos esa reserva en MET Mallorca. ¿Podría ser de otra empresa? Puedes adjuntar foto del voucher o indicarnos la empresa que figura en tu comprobante.',
                'verify_error_system': '❌ Error del sistema. Por favor, contacta con soporte.',
                'verify_error_not_found': '❌ Tu reserva no está registrada. Por favor, vuelve a comprobarla o realiza una nueva.',
                'verify_error_email': '❌ El email no coincide con la reserva. Por favor, verifica los datos.',
                'verify_success_prefix': '✅ Tu reserva fue realizada con MET Mallorca.',
                'verify_details_title': 'Detalles de tu reserva:',
                'verify_details_ref': 'Ref',
                'verify_details_client': 'Cliente',
                'verify_details_email': 'Email',
                'verify_details_phone': 'Teléfono',
                'verify_details_date': 'Fecha',
                'verify_details_total': 'Total',
                'verify_details_status': 'Estado',
                'verify_details_services': 'Servicios',
                'verify_details_transfer': 'Detalles del traslado',
                'verify_details_origin': 'Origen',
                'verify_details_destination': 'Destino',
                'verify_details_datetime': 'Fecha/Hora',
                'verify_details_passengers': 'Pasajeros',
                'verify_option_view_details': '📋 Ver detalles completos',
                'verify_option_modify': '✏️ Modificar reserva',
                'verify_option_restart': '🏠 Volver al inicio',
                'verify_option_new_booking': '🆕 Hacer nueva reserva',
                'verify_option_support': '📞 Contactar soporte',
                'verify_option_retry': '🔄 Intentar de nuevo',
                'verify_retry_question': '¿Quieres intentar de nuevo?',
                'verify_error_generic': 'Error al verificar la reserva. Por favor, intenta de nuevo.',
                
                // Location step
                'location_origin_title': 'Ubicación de Origen',
                'location_origin_question': 'Busca y selecciona tu ubicación de recogida:',
                'location_destination_title': '¿Cuál es tu destino?',
                'location_destination_question': 'Busca y selecciona tu ubicación de destino:',
                
                // Date step
                'date_title': '¿Qué día necesitas el traslado?',
                'date_format': 'Escribe la fecha en formato <strong>DD/MM/YYYY</strong>',
                'date_example': 'Ejemplo: 25/12/2025',
                'date_error_retry': 'Por favor, intenta de nuevo:',
                
                // Time step
                'time_title': '¿A qué hora necesitas el traslado?',
                'time_message': 'Selecciona una hora disponible (intervalos de 30 minutos).',
                'time_example': 'Ejemplo: 2:00 PM, 2:30 PM, 3:00 PM…',
                'time_error_retry': 'Por favor, intenta de nuevo:',
                
                // Passengers step
                'passengers_title': '¿Cuántas personas viajan?',
                'passengers_question': 'Escribe el número de pasajeros:',
                'passengers_example': 'Ej: 4',
                'passengers_error_retry': 'Por favor, intenta de nuevo:',
                'passengers_large_group': 'Grupo Grande',
                'passengers_large_message': 'Para grupos de más de 20 personas, te recomendamos solicitar un presupuesto personalizado.',
                'passengers_contact': 'Por favor, contacta con nosotros en:',
                
                // Extras step
                'extras_title': 'Opciones Extras',
                'extras_message': 'Selecciona las opciones adicionales que necesites para tu viaje:',
                'extras_hand_luggage': 'Equipaje de mano',
                'extras_suitcases': 'Valijas',
                'extras_booster_seats': 'Alzadores',
                'extras_baby_seats': 'Sillas de bebé',
                'extras_golf_bag': 'Bolsa de Golf',
                'extras_bicycle': 'Bicicleta',
                'extras_free': 'Gratis',
                'extras_each': 'c/u',
                'extras_confirm': 'Confirmar opciones',
                'extras_selected_summary': 'Opciones extras seleccionadas',
                'extras_confirmed': 'Opciones extras confirmadas',
                'extras_none': 'Sin opciones extras',
                'extras_none_message': 'Continuaremos sin servicios adicionales.',
                'extras_continue': 'Continuemos con el resumen de tu reserva...',
                'extras_total': 'Total extras',
                
                // Price breakdown
                'price_breakdown_title': 'Desglose del precio',
                'price_location': 'Ubicación',
                'price_distance': 'Distancia',
                'price_distance_unit': 'km',
                'price_base': 'Precio base',
                'price_vehicle': 'Vehículo',
                'price_vehicle_supplement': 'Suplemento de vehículo',

                'price_passenger_supplement': 'Pasajeros extra',
                'price_total': 'TOTAL',
                'price_extra_pet': '🐾 Mascota',
                'price_extra_child_seat': '👶 Silla infantil',
                'price_extra_booster_seat': '🪑 Elevador',
                'price_extra_luggage_extra': '🧳 Equipaje extra',
                'price_extra_meet_greet': '👋 Meet & Greet',
                'price_extra_bolsa_golf': '⛳ Bolsa de Golf',
                'price_extra_bicicleta': '🚴 Bicicleta',
                
                // Summary step
                'summary_title': 'Resumen de tu Reserva',
                'summary_route': 'Ruta',
                'summary_datetime': 'Fecha y Hora',
                'summary_passengers': 'Pasajeros',
                'summary_vehicle': 'Vehículo',
                'summary_extras': 'Opciones Extras',
                'summary_question': '¿Todo correcto?',
                'summary_continue_checkout': 'Sí, continuar al checkout',
                'summary_modify_data': 'Modificar datos',
                'summary_error_missing': 'Error: Faltan datos necesarios para calcular el precio.',
                'summary_error_fields': 'Campos faltantes',
                'summary_error_calculate': 'Error al calcular el precio',
                
                // Confirm step
                'confirm_perfect': '¡Perfecto!',
                'confirm_message': 'Tu reserva está lista. Haz clic en el botón de abajo para ir al checkout seguro y completar el pago.',
                'confirm_checkout_button': 'Ir al Checkout',
                'confirm_payment_secure': 'Pago seguro con Redsys/Getnet a través de WooCommerce',
                'confirm_data_protected': 'Tus datos están protegidos',
                'confirm_another_booking': 'Hacer otra reserva',
                
                // Modify step
                'modify_title': '¿Qué deseas modificar?',
                'modify_locations': 'Origen/Destino',
                'modify_datetime': 'Fecha/Hora',
                'modify_passengers': 'Pasajeros',
                'modify_start_over': 'Empezar de nuevo',
                'modify_locations_title': 'Modificar Ubicaciones',
                'modify_locations_question': '¿Desde dónde te recogemos?',
                'modify_datetime_title': 'Modificar Fecha y Hora',
                'modify_datetime_question': '¿Qué día necesitas el traslado?',
                'modify_passengers_title': 'Modificar Pasajeros',
                'modify_passengers_question': '¿Cuántas personas viajan?',
                
                // Vehicle types
                'vehicle_standard': 'Vehículo Estándar (1-4 pax)',
                'vehicle_van': 'Van (5-8 pax)',
                'vehicle_minibus': 'Minibus (9-16 pax)',
                'vehicle_bus': 'Bus (17-20 pax)',
                
                // Footer
                'footer_privacy': 'Al continuar aceptas la',
                'footer_privacy_link': 'Política de Privacidad',
                
                // Location options
                'location_airport': 'Aeropuerto',
                'location_hotel': 'Hotel / Alojamiento',
                
                // No results
                'no_results': 'No se encontraron resultados',
                'loading': 'Cargando...'
            },
            
            // ========== ENGLISH (British) ==========
            en: {
                // Header
                'assistant_title': 'MET Mallorca Assistant',
                'status_online': 'Online',
                
                // Invitación
                'invitation_text': 'Can I help you with your booking?',
                
                // Input placeholders
                'placeholder_message': 'Type your message...',
                'placeholder_number': 'Type a number...',
                'placeholder_email': 'Type your email...',
                'placeholder_location_origin': 'Search origin location...',
                'placeholder_location_destination': 'Search destination...',
                'placeholder_time': 'Search time (e.g.: 14:30)',
                'placeholder_date': 'DD/MM/YYYY',
                
                // Botones comunes
                'btn_back': 'Back',
                'btn_restart': 'Restart',
                'btn_confirm': 'Confirm',
                'btn_continue': 'Continue',
                'btn_modify': 'Modify',
                
                // Mensajes de error
                'error_connection': 'Sorry, a connection error occurred. Please try again.',
                'error_general': 'Sorry, an error occurred. Please try again.',
                
                // Welcome step
                'welcome_title': 'Welcome to MET Mallorca!',
                'welcome_message': 'I\'m your booking assistant. I\'ll help you calculate the price of your transfer and generate your booking in less than 2 minutes.',
                'welcome_question': 'What type of transfer do you need?',
                'option_airport': 'Airport ↔ Destination',
                'option_point_to_airport': 'Where I Am → Airport (PMI)',
                'option_verify': 'Verify my booking',
                
                // Route type step
                'route_airport_title': 'Transfer from the Airport',
                'route_airport_question': 'Perfect, where shall we take you?',
                'route_point_title': 'Transfer to the Airport',
                'route_point_question': 'Perfect. Search and select your origin location:',
                
                // Verify booking
                'verify_title': 'Verify Booking',
                'verify_message': 'Please enter your <strong>order number</strong> and your <strong>email</strong> separated by a comma.',
                'verify_example': 'Example: 1234, email@example.com',
                'verify_error_missing_info': '❌ Please provide the order number and email separated by a comma. Example: 1234, email@example.com',
                'verify_error_invalid_format': '❌ The code must be a valid number.',
                'verify_checking': '🔍 Checking your booking...',
                'verify_error_not_met': '❌ We couldn\'t find that booking at MET Mallorca. Could it belong to another company? You can send us a photo of the voucher or tell us the company on your receipt.',
                'verify_error_system': '❌ System error. Please contact support.',
                'verify_error_not_found': '❌ Your booking is not registered. Please check it again or make a new one.',
                'verify_error_email': '❌ The email does not match the booking. Please double-check.',
                'verify_success_prefix': '✅ Your booking was made with MET Mallorca.',
                'verify_details_title': 'Your booking details:',
                'verify_details_ref': 'Reference',
                'verify_details_client': 'Customer',
                'verify_details_email': 'Email',
                'verify_details_phone': 'Phone',
                'verify_details_date': 'Date',
                'verify_details_total': 'Total',
                'verify_details_status': 'Status',
                'verify_details_services': 'Services',
                'verify_details_transfer': 'Transfer details',
                'verify_details_origin': 'Origin',
                'verify_details_destination': 'Destination',
                'verify_details_datetime': 'Date/Time',
                'verify_details_passengers': 'Passengers',
                'verify_option_view_details': '📋 View full details',
                'verify_option_modify': '✏️ Modify booking',
                'verify_option_restart': '🏠 Go back to start',
                'verify_option_new_booking': '🆕 Make a new booking',
                'verify_option_support': '📞 Contact support',
                'verify_option_retry': '🔄 Try again',
                'verify_retry_question': 'Would you like to try again?',
                'verify_error_generic': 'There was an error verifying the booking. Please try again.',
                
                // Location step
                'location_origin_title': 'Origin Location',
                'location_origin_question': 'Search and select your pick-up location:',
                'location_destination_title': 'What is your destination?',
                'location_destination_question': 'Search and select your destination location:',
                
                // Date step
                'date_title': 'What day do you need the transfer?',
                'date_format': 'Enter the date in <strong>DD/MM/YYYY</strong> format',
                'date_example': 'Example: 25/12/2025',
                'date_error_retry': 'Please try again:',
                
                // Time step
                'time_title': 'What time do you need the transfer?',
                'time_message': 'Select an available time (30-minute intervals).',
                'time_example': 'Example: 2:00 PM, 2:30 PM, 3:00 PM…',
                'time_error_retry': 'Please try again:',
                
                // Passengers step
                'passengers_title': 'How many people are travelling?',
                'passengers_question': 'Enter the number of passengers:',
                'passengers_example': 'E.g.: 4',
                'passengers_error_retry': 'Please try again:',
                'passengers_large_group': 'Large Group',
                'passengers_large_message': 'For groups of more than 20 people, we recommend requesting a personalised quote.',
                'passengers_contact': 'Please contact us at:',
                
                // Extras step
                'extras_title': 'Extra Options',
                'extras_message': 'Select the additional options you need for your journey:',
                'extras_hand_luggage': 'Hand luggage',
                'extras_suitcases': 'Suitcases',
                'extras_booster_seats': 'Booster seats',
                'extras_baby_seats': 'Baby seats',
                'extras_golf_bag': 'Golf bag',
                'extras_bicycle': 'Bicycle',
                'extras_free': 'Free',
                'extras_each': 'each',
                'extras_confirm': 'Confirm options',
                'extras_selected_summary': 'Selected extra options',
                'extras_confirmed': 'Extra options confirmed',
                'extras_none': 'No extra options',
                'extras_none_message': 'We\'ll continue without additional services.',
                'extras_continue': 'Let\'s continue with your booking summary...',
                'extras_total': 'Extras total',
                
                // Price breakdown
                'price_breakdown_title': 'Price breakdown',
                'price_location': 'Location',
                'price_distance': 'Distance',
                'price_distance_unit': 'km',
                'price_base': 'Base price',
                'price_vehicle': 'Vehicle',
                'price_vehicle_supplement': 'Vehicle supplement',

                'price_passenger_supplement': 'Extra passengers',
                'price_total': 'TOTAL',
                'price_extra_pet': '🐾 Pet',
                'price_extra_child_seat': '👶 Child seat',
                'price_extra_booster_seat': '🪑 Booster seat',
                'price_extra_luggage_extra': '🧳 Extra luggage',
                'price_extra_meet_greet': '👋 Meet & Greet',
                'price_extra_bolsa_golf': '⛳ Golf bag',
                'price_extra_bicicleta': '🚴 Bicycle',
                
                // Summary step
                'summary_title': 'Your Booking Summary',
                'summary_route': 'Route',
                'summary_datetime': 'Date and Time',
                'summary_passengers': 'Passengers',
                'summary_vehicle': 'Vehicle',
                'summary_extras': 'Extra Options',
                'summary_question': 'Is everything correct?',
                'summary_continue_checkout': 'Yes, continue to checkout',
                'summary_modify_data': 'Modify details',
                'summary_error_missing': 'Error: Missing data required to calculate the price.',
                'summary_error_fields': 'Missing fields',
                'summary_error_calculate': 'Error calculating price',
                
                // Confirm step
                'confirm_perfect': 'Perfect!',
                'confirm_message': 'Your booking is ready. Click the button below to go to secure checkout and complete payment.',
                'confirm_checkout_button': 'Go to Checkout',
                'confirm_payment_secure': 'Secure payment with Redsys/Getnet through WooCommerce',
                'confirm_data_protected': 'Your data is protected',
                'confirm_another_booking': 'Make another booking',
                
                // Modify step
                'modify_title': 'What would you like to modify?',
                'modify_locations': 'Origin/Destination',
                'modify_datetime': 'Date/Time',
                'modify_passengers': 'Passengers',
                'modify_start_over': 'Start over',
                'modify_locations_title': 'Modify Locations',
                'modify_locations_question': 'Where shall we pick you up from?',
                'modify_datetime_title': 'Modify Date and Time',
                'modify_datetime_question': 'What day do you need the transfer?',
                'modify_passengers_title': 'Modify Passengers',
                'modify_passengers_question': 'How many people are travelling?',
                
                // Vehicle types
                'vehicle_standard': 'Standard Vehicle (1-4 pax)',
                'vehicle_van': 'Van (5-8 pax)',
                'vehicle_minibus': 'Minibus (9-16 pax)',
                'vehicle_bus': 'Bus (17-20 pax)',
                
                // Footer
                'footer_privacy': 'By continuing you accept the',
                'footer_privacy_link': 'Privacy Policy',
                
                // Location options
                'location_airport': 'Airport',
                'location_hotel': 'Hotel / Accommodation',
                
                // No results
                'no_results': 'No results found',
                'loading': 'Loading...'
            },
            
            // ========== DEUTSCH ==========
            de: {
                // Header
                'assistant_title': 'MET Mallorca Assistent',
                'status_online': 'Online',
                
                // Invitación
                'invitation_text': 'Kann ich Ihnen bei Ihrer Buchung helfen?',
                
                // Input placeholders
                'placeholder_message': 'Schreiben Sie Ihre Nachricht...',
                'placeholder_number': 'Geben Sie eine Nummer ein...',
                'placeholder_email': 'Geben Sie Ihre E-Mail ein...',
                'placeholder_location_origin': 'Startort suchen...',
                'placeholder_location_destination': 'Zielort suchen...',
                'placeholder_time': 'Uhrzeit suchen (z.B.: 14:30)',
                'placeholder_date': 'TT/MM/JJJJ',
                
                // Botones comunes
                'btn_back': 'Zurück',
                'btn_restart': 'Neustart',
                'btn_confirm': 'Bestätigen',
                'btn_continue': 'Weiter',
                'btn_modify': 'Ändern',
                
                // Mensajes de error
                'error_connection': 'Entschuldigung, es ist ein Verbindungsfehler aufgetreten. Bitte versuchen Sie es erneut.',
                'error_general': 'Entschuldigung, es ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.',
                
                // Welcome step
                'welcome_title': 'Willkommen bei MET Mallorca!',
                'welcome_message': 'Ich bin Ihr Buchungsassistent. Ich helfe Ihnen, den Preis Ihres Transfers zu berechnen und Ihre Buchung in weniger als 2 Minuten zu erstellen.',
                'welcome_question': 'Welche Art von Transfer benötigen Sie?',
                'option_airport': 'Flughafen ↔ Zielort',
                'option_point_to_airport': 'Wo Ich Bin → Flughafen (PMI)',
                'option_verify': 'Meine Buchung überprüfen',
                
                // Route type step
                'route_airport_title': 'Transfer vom Flughafen',
                'route_airport_question': 'Perfekt, wohin sollen wir Sie bringen?',
                'route_point_title': 'Transfer zum Flughafen',
                'route_point_question': 'Perfekt. Suchen und wählen Sie Ihren Startort:',
                
                // Verify booking
                'verify_title': 'Buchung überprüfen',
                'verify_message': 'Bitte geben Sie Ihre <strong>Bestellnummer</strong> und Ihre <strong>E-Mail</strong> durch Komma getrennt ein.',
                'verify_example': 'Beispiel: 1234, email@beispiel.com',
                'verify_error_missing_info': '❌ Bitte gib Bestellnummer und E-Mail getrennt durch ein Komma ein. Beispiel: 1234, email@beispiel.com',
                'verify_error_invalid_format': '❌ Der Code muss eine gültige Nummer sein.',
                'verify_checking': '🔍 Wir prüfen deine Buchung...',
                'verify_error_not_met': '❌ Wir konnten diese Buchung nicht bei MET Mallorca finden. Könnte sie zu einem anderen Unternehmen gehören? Bitte sende uns ein Foto des Vouchers oder nenne die Firma auf deinem Beleg.',
                'verify_error_system': '❌ Systemfehler. Bitte kontaktiere den Support.',
                'verify_error_not_found': '❌ Ihre Buchung ist nicht registriert. Bitte überprüfen Sie sie erneut oder erstellen Sie eine neue.',
                'verify_error_email': '❌ Die E-Mail stimmt nicht mit der Buchung überein. Bitte überprüfe die Angaben.',
                'verify_success_prefix': '✅ Deine Buchung wurde bei MET Mallorca erstellt.',
                'verify_details_title': 'Details deiner Buchung:',
                'verify_details_ref': 'Referenz',
                'verify_details_client': 'Kunde',
                'verify_details_email': 'E-Mail',
                'verify_details_phone': 'Telefon',
                'verify_details_date': 'Datum',
                'verify_details_total': 'Gesamt',
                'verify_details_status': 'Status',
                'verify_details_services': 'Leistungen',
                'verify_details_transfer': 'Transferdetails',
                'verify_details_origin': 'Abfahrtsort',
                'verify_details_destination': 'Zielort',
                'verify_details_datetime': 'Datum/Uhrzeit',
                'verify_details_passengers': 'Passagiere',
                'verify_option_view_details': '📋 Alle Details anzeigen',
                'verify_option_modify': '✏️ Buchung bearbeiten',
                'verify_option_restart': '🏠 Zurück zum Start',
                'verify_option_new_booking': '🆕 Neue Buchung erstellen',
                'verify_option_support': '📞 Support kontaktieren',
                'verify_option_retry': '🔄 Erneut versuchen',
                'verify_retry_question': 'Möchtest du es erneut versuchen?',
                'verify_error_generic': 'Beim Überprüfen der Buchung ist ein Fehler aufgetreten. Bitte versuche es erneut.',
                
                // Location step
                'location_origin_title': 'Startort',
                'location_origin_question': 'Suchen und wählen Sie Ihren Abholort:',
                'location_destination_title': 'Was ist Ihr Zielort?',
                'location_destination_question': 'Suchen und wählen Sie Ihren Zielort:',
                
                // Date step
                'date_title': 'An welchem Tag benötigen Sie den Transfer?',
                'date_format': 'Geben Sie das Datum im Format <strong>TT/MM/JJJJ</strong> ein',
                'date_example': 'Beispiel: 25/12/2025',
                'date_error_retry': 'Bitte versuchen Sie es erneut:',
                
                // Time step
                'time_title': 'Um welche Uhrzeit benötigen Sie den Transfer?',
                'time_message': 'Wählen Sie eine verfügbare Uhrzeit (30-Minuten-Intervalle).',
                'time_example': 'Beispiel: 14:00, 14:30, 15:00 Uhr…',
                'time_error_retry': 'Bitte versuchen Sie es erneut:',
                
                // Passengers step
                'passengers_title': 'Wie viele Personen reisen?',
                'passengers_question': 'Geben Sie die Anzahl der Passagiere ein:',
                'passengers_example': 'Z.B.: 4',
                'passengers_error_retry': 'Bitte versuchen Sie es erneut:',
                'passengers_large_group': 'Große Gruppe',
                'passengers_large_message': 'Für Gruppen von mehr als 20 Personen empfehlen wir, ein individuelles Angebot anzufordern.',
                'passengers_contact': 'Bitte kontaktieren Sie uns unter:',
                
                // Extras step
                'extras_title': 'Zusatzoptionen',
                'extras_message': 'Wählen Sie die zusätzlichen Optionen, die Sie für Ihre Reise benötigen:',
                'extras_hand_luggage': 'Handgepäck',
                'extras_suitcases': 'Koffer',
                'extras_booster_seats': 'Sitzerhöhungen',
                'extras_baby_seats': 'Kindersitze',
                'extras_golf_bag': 'Golftasche',
                'extras_bicycle': 'Fahrrad',
                'extras_free': 'Kostenlos',
                'extras_each': 'pro Stück',
                'extras_confirm': 'Optionen bestätigen',
                'extras_selected_summary': 'Ausgewählte Zusatzoptionen',
                'extras_confirmed': 'Zusatzoptionen bestätigt',
                'extras_none': 'Keine Zusatzoptionen',
                'extras_none_message': 'Wir fahren ohne zusätzliche Services fort.',
                'extras_continue': 'Fahren wir mit Ihrer Buchungszusammenfassung fort...',
                'extras_total': 'Extras gesamt',
                
                // Price breakdown
                'price_breakdown_title': 'Preisaufschlüsselung',
                'price_location': 'Standort',
                'price_distance': 'Entfernung',
                'price_distance_unit': 'km',
                'price_base': 'Grundpreis',
                'price_vehicle': 'Fahrzeug',
                'price_vehicle_supplement': 'Fahrzeugsupplement',

                'price_passenger_supplement': 'Zusatzpassagiere',
                'price_total': 'GESAMT',
                'price_extra_pet': '🐾 Haustier',
                'price_extra_child_seat': '👶 Kindersitz',
                'price_extra_booster_seat': '🪑 Sitzerhöhung',
                'price_extra_luggage_extra': '🧳 Zusätzliches Gepäck',
                'price_extra_meet_greet': '👋 Meet & Greet',
                'price_extra_bolsa_golf': '⛳ Golftasche',
                'price_extra_bicicleta': '🚴 Fahrrad',
                
                // Summary step
                'summary_title': 'Ihre Buchungszusammenfassung',
                'summary_route': 'Route',
                'summary_datetime': 'Datum und Uhrzeit',
                'summary_passengers': 'Passagiere',
                'summary_vehicle': 'Fahrzeug',
                'summary_extras': 'Zusatzoptionen',
                'summary_question': 'Ist alles korrekt?',
                'summary_continue_checkout': 'Ja, zur Kasse gehen',
                'summary_modify_data': 'Details ändern',
                'summary_error_missing': 'Fehler: Fehlende Daten zur Preisberechnung.',
                'summary_error_fields': 'Fehlende Felder',
                'summary_error_calculate': 'Fehler bei der Preisberechnung',
                
                // Confirm step
                'confirm_perfect': 'Perfekt!',
                'confirm_message': 'Ihre Buchung ist fertig. Klicken Sie auf die Schaltfläche unten, um zur sicheren Kasse zu gehen und die Zahlung abzuschließen.',
                'confirm_checkout_button': 'Zur Kasse gehen',
                'confirm_payment_secure': 'Sichere Zahlung mit Redsys/Getnet über WooCommerce',
                'confirm_data_protected': 'Ihre Daten sind geschützt',
                'confirm_another_booking': 'Weitere Buchung vornehmen',
                
                // Modify step
                'modify_title': 'Was möchten Sie ändern?',
                'modify_locations': 'Start/Ziel',
                'modify_datetime': 'Datum/Uhrzeit',
                'modify_passengers': 'Passagiere',
                'modify_start_over': 'Von vorne beginnen',
                'modify_locations_title': 'Orte ändern',
                'modify_locations_question': 'Wo sollen wir Sie abholen?',
                'modify_datetime_title': 'Datum und Uhrzeit ändern',
                'modify_datetime_question': 'An welchem Tag benötigen Sie den Transfer?',
                'modify_passengers_title': 'Passagiere ändern',
                'modify_passengers_question': 'Wie viele Personen reisen?',
                
                // Vehicle types
                'vehicle_standard': 'Standardfahrzeug (1-4 Pax)',
                'vehicle_van': 'Van (5-8 Pax)',
                'vehicle_minibus': 'Minibus (9-16 Pax)',
                'vehicle_bus': 'Bus (17-20 Pax)',
                
                // Footer
                'footer_privacy': 'Durch Fortfahren akzeptieren Sie die',
                'footer_privacy_link': 'Datenschutzerklärung',
                
                // Location options
                'location_airport': 'Flughafen',
                'location_hotel': 'Hotel / Unterkunft',
                
                // No results
                'no_results': 'Keine Ergebnisse gefunden',
                'loading': 'Wird geladen...'
            }
        },
        
        /**
         * Obtener traducción
         */
        t: function(key) {
            const lang = this.currentLang;
            if (this.translations[lang] && this.translations[lang][key]) {
                return this.translations[lang][key];
            }
            // Fallback a español
            return this.translations.es[key] || key;
        },
        
        /**
         * Cambiar idioma
         */
        setLanguage: function(lang) {
            if (this.translations[lang]) {
                this.currentLang = lang;
                // Guardar en localStorage
                localStorage.setItem('met_chatbot_lang', lang);
                // Disparar evento para actualizar UI
                $(document).trigger('met-language-changed', [lang]);
                return true;
            }
            return false;
        },
        
        /**
         * Obtener idioma actual
         */
        getLanguage: function() {
            return this.currentLang;
        },
        
        /**
         * Inicializar idioma desde localStorage
         */
        init: function() {
            const savedLang = localStorage.getItem('met_chatbot_lang');
            if (savedLang && this.translations[savedLang]) {
                this.currentLang = savedLang;
                // Actualizar botones activos
                $(document).ready(() => {
                    $('.met-lang-btn').removeClass('active');
                    $('.met-lang-btn[data-lang="' + savedLang + '"]').addClass('active');
                });
            }
        }
    };
    
    // Inicializar al cargar
    $(document).ready(function() {
        window.MetTranslations.init();
    });
    
})(jQuery);
