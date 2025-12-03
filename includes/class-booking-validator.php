<?php
/**
 * Validador de datos de reserva
 * Valida todos los inputs del usuario antes de procesarlos
 */

if (!defined('ABSPATH')) {
    exit;
}

class MET_Booking_Validator {
    
    /**
     * Validar fecha en formato DD/MM/YYYY
     */
    public function validate_date($date_string) {
        $date_string = trim($date_string);
        
        // Formato esperado: DD/MM/YYYY
        if (!preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date_string, $matches)) {
            return array(
                'valid' => false,
                'error' => '❌ Formato de fecha incorrecto. Usa DD/MM/YYYY (ej: 25/12/2025)'
            );
        }
        
        $day = intval($matches[1]);
        $month = intval($matches[2]);
        $year = intval($matches[3]);
        
        // Validar que sea una fecha válida
        if (!checkdate($month, $day, $year)) {
            return array(
                'valid' => false,
                'error' => '❌ Fecha inválida. Verifica el día y mes.'
            );
        }
        
        // Crear objeto DateTime
        $date = DateTime::createFromFormat('d/m/Y', $date_string);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        // Verificar que no sea una fecha pasada
        if ($date < $today) {
            return array(
                'valid' => false,
                'error' => '❌ La fecha no puede ser anterior a hoy.'
            );
        }
        
        // Verificar que no sea más de 1 año en el futuro
        $max_date = clone $today;
        $max_date->modify('+1 year');
        
        if ($date > $max_date) {
            return array(
                'valid' => false,
                'error' => '❌ La fecha no puede ser más de 1 año en el futuro.'
            );
        }
        
        return array(
            'valid' => true,
            'date' => $date_string,
            'formatted' => $date->format('d/m/Y')
        );
    }
    
    /**
     * Validar hora en formato HH:MM
     */
    public function validate_time($time_string) {
        $time_string = trim($time_string);
        
        // Formato esperado: HH:MM
        if (!preg_match('/^(\d{1,2}):(\d{2})$/', $time_string, $matches)) {
            return array(
                'valid' => false,
                'error' => '❌ Formato de hora incorrecto. Usa HH:MM (ej: 14:30)'
            );
        }
        
        $hour = intval($matches[1]);
        $minute = intval($matches[2]);
        
        // Validar rangos
        if ($hour < 0 || $hour > 23) {
            return array(
                'valid' => false,
                'error' => '❌ La hora debe estar entre 00 y 23.'
            );
        }
        
        if ($minute < 0 || $minute > 59) {
            return array(
                'valid' => false,
                'error' => '❌ Los minutos deben estar entre 00 y 59.'
            );
        }
        
        if ($minute % 30 !== 0) {
            return array(
                'valid' => false,
                'error' => '❌ Solo aceptamos horarios cada 30 minutos (ej: 14:00, 14:30).'
            );
        }

        // Formatear con ceros a la izquierda
        $formatted = sprintf('%02d:%02d', $hour, $minute);
        
        return array(
            'valid' => true,
            'time' => $formatted,
            'hour' => $hour,
            'minute' => $minute
        );
    }
    
    /**
     * Validar fecha y hora combinadas
     */
    public function validate_datetime($datetime_string) {
        $datetime_string = trim($datetime_string);
        
        // Formato esperado: DD/MM/YYYY - HH:MM o DD/MM/YYYY HH:MM
        $parts = preg_split('/\s*[-–]\s*|\s+/', $datetime_string);
        
        if (count($parts) < 2) {
            return array(
                'valid' => false,
                'error' => '❌ Formato incorrecto. Usa: DD/MM/YYYY - HH:MM<br>Ejemplo: 25/12/2025 - 14:30'
            );
        }
        
        $date_part = trim($parts[0]);
        $time_part = trim($parts[1]);
        
        // Validar fecha
        $date_validation = $this->validate_date($date_part);
        if (!$date_validation['valid']) {
            return $date_validation;
        }
        
        // Validar hora
        $time_validation = $this->validate_time($time_part);
        if (!$time_validation['valid']) {
            return $time_validation;
        }
        
        // Combinar
        $datetime_formatted = $date_validation['formatted'] . ' - ' . $time_validation['time'];
        
        return array(
            'valid' => true,
            'datetime' => $datetime_formatted,
            'date' => $date_validation['date'],
            'time' => $time_validation['time']
        );
    }
    
    /**
     * Validar número de pasajeros
     */
    public function validate_passengers($passengers_input) {
        $passengers = intval($passengers_input);
        
        if ($passengers < 1) {
            return array(
                'valid' => false,
                'error' => '❌ Debe haber al menos 1 pasajero.'
            );
        }
        
        return array(
            'valid' => true,
            'passengers' => $passengers
        );
    }
    
    /**
     * Validar ubicación (no vacía, longitud razonable)
     */
    public function validate_location($location_string) {
        $location = trim($location_string);
        
        if (empty($location)) {
            return array(
                'valid' => false,
                'error' => '❌ La ubicación no puede estar vacía.'
            );
        }
        
        if (strlen($location) < 3) {
            return array(
                'valid' => false,
                'error' => '❌ La ubicación debe tener al menos 3 caracteres.'
            );
        }
        
        if (strlen($location) > 100) {
            return array(
                'valid' => false,
                'error' => '❌ La ubicación es demasiado larga (máximo 100 caracteres).'
            );
        }
        
        // Sanitizar
        $location = sanitize_text_field($location);
        
        return array(
            'valid' => true,
            'location' => $location
        );
    }
    
    /**
     * Validar número de vuelo (opcional)
     */
    public function validate_flight_number($flight_string) {
        $flight = trim($flight_string);
        
        // Si está vacío o es "no" o "skip", es válido (opcional)
        if (empty($flight) || strtolower($flight) === 'no' || strtolower($flight) === 'skip') {
            return array(
                'valid' => true,
                'flight_number' => '',
                'optional' => true
            );
        }
        
        // Validar formato básico (letras y números)
        if (!preg_match('/^[A-Z0-9\s\-]+$/i', $flight)) {
            return array(
                'valid' => false,
                'error' => '❌ Formato de vuelo inválido. Usa solo letras y números (ej: IB3456)'
            );
        }
        
        if (strlen($flight) > 20) {
            return array(
                'valid' => false,
                'error' => '❌ Número de vuelo demasiado largo.'
            );
        }
        
        return array(
            'valid' => true,
            'flight_number' => strtoupper(sanitize_text_field($flight))
        );
    }
    
    /**
     * Validar datos completos de reserva
     */
    public function validate_complete_booking($data) {
        $errors = array();
        
        // Validar campos requeridos
        $required_fields = array('origin', 'destination', 'date', 'time', 'passengers');
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = 'Falta el campo: ' . $field;
            }
        }
        
        if (!empty($errors)) {
            return array(
                'valid' => false,
                'errors' => $errors
            );
        }
        
        return array(
            'valid' => true
        );
    }
}
