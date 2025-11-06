# MET Mallorca Chatbot Plugin

## 📋 Descripción

Plugin de chatbot inteligente para MET Mallorca que automatiza el proceso de reservas, presupuestos y verificación de reservas existentes.

## ✨ Características

### 1. **Flujo de Reservas Aeropuerto ↔ Punto**
- Hasta 20 personas: Reserva online inmediata
- Más de 20 personas: Derivación a presupuesto personalizado
- Captura de datos completos antes de derivar al sistema

### 2. **Flujo Punto ↔ Punto**
- Traslados dentro de Mallorca sin aeropuerto
- Siempre deriva a formulario de presupuesto personalizado

### 3. **Verificación de Reservas**
- Detecta si la reserva es de MET o de otra empresa
- Formato de código: MET-XXXXXX
- Validación por email

### 4. **Cumplimiento RGPD**
- Aviso de política de privacidad
- Datos usados solo para gestión de reservas
- Pago en entorno seguro

## 📁 Estructura del Plugin

```
met-chatbot/
├── met-chatbot.php              # Archivo principal del plugin
├── README.md                    # Este archivo
├── includes/
│   ├── class-conversation-flow.php   # Lógica del flujo de conversación
│   └── class-booking-handler.php     # Manejo de reservas y verificación
├── assets/
│   ├── css/
│   │   └── chatbot.css          # Estilos del chatbot
│   └── js/
│       └── chatbot.js           # JavaScript del chatbot
└── templates/
    └── chatbot-widget.php       # Template HTML del widget
```

## 🚀 Instalación

1. Copia la carpeta `met-chatbot` a `/wp-content/plugins/`
2. Ve a WordPress Admin → Plugins
3. Activa "MET Mallorca Chatbot"
4. El chatbot aparecerá automáticamente en la esquina inferior derecha

## 🔧 Configuración

### Requisitos
- WordPress 5.0+
- WooCommerce (para gestión de reservas)
- PHP 7.4+

### Personalización

#### Cambiar colores
Edita las variables CSS en `assets/css/chatbot.css`:

```css
:root {
    --met-primary: #0066cc;        /* Color principal */
    --met-primary-dark: #004d99;   /* Color principal oscuro */
    --met-secondary: #00cc66;      /* Color secundario */
}
```

#### Modificar URL de reservas
En `includes/class-conversation-flow.php`, método `generate_booking_url()`:

```php
$base_url = home_url('/tu-pagina-de-reservas/');
```

## 📊 Flujos de Conversación

### Flujo 1: Aeropuerto ↔ Punto (≤20 personas)
```
1. Bienvenida
2. Tipo de ruta → Aeropuerto
3. Origen (aeropuerto)
4. Destino
5. Número de pasajeros
6. Mascota (sí/no)
7. Fecha y hora
8. Número de vuelo (opcional)
9. Resumen y botón "Reservar ahora"
```

### Flujo 2: Aeropuerto ↔ Punto (>20 personas)
```
1-5. Igual que Flujo 1
6. Detecta >20 personas
7. Mensaje de grupo
8. Botón "Solicitar presupuesto de grupo"
```

### Flujo 3: Punto ↔ Punto
```
1. Bienvenida
2. Tipo de ruta → Punto a Punto
3. Origen
4. Destino
5. Número de pasajeros
6. Mensaje de presupuesto
7. Botón "Solicitar presupuesto"
```

### Flujo 4: Verificación de Reserva
```
1. Bienvenida
2. Opción "Verificar mi reserva"
3. Solicitar código y email
4. Verificación en base de datos
5. Resultado:
   - Si es MET: Mostrar detalles completos
   - Si no es MET: Mensaje informativo
```

## 🔌 Integración con WooCommerce

El plugin se integra con WooCommerce para:

- **Verificar reservas**: Busca órdenes por ID (formato MET-XXXXXX)
- **Crear reservas**: Puede crear órdenes con metadata personalizada
- **Almacenar datos**: Guarda información del traslado como metadata

### Metadata guardada en órdenes:
- `_origin`: Punto de origen
- `_destination`: Punto de destino
- `_datetime`: Fecha y hora del traslado
- `_passengers`: Número de pasajeros
- `_pet`: Tipo de mascota (si aplica)
- `_flight_number`: Número de vuelo (si aplica)

## 🎨 Personalización Avanzada

### Agregar nuevos pasos al flujo

En `includes/class-conversation-flow.php`:

```php
case 'nuevo_paso':
    $response = $this->step_nuevo_paso($message, $data);
    break;

private function step_nuevo_paso($message, $data) {
    $data['nuevo_campo'] = $message;
    
    return array(
        'message' => 'Tu mensaje aquí',
        'nextStep' => 'siguiente_paso',
        'options' => array(
            array('text' => 'Opción 1', 'value' => 'opcion1')
        ),
        'data' => $data
    );
}
```

### Modificar mensajes

Todos los mensajes están en `includes/class-conversation-flow.php`. Busca el método correspondiente y modifica el texto.

## 🐛 Debugging

Para activar el modo debug, agrega en `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Los logs se guardarán en `/wp-content/debug.log`

## 📱 Responsive

El chatbot es completamente responsive:
- Desktop: 380px de ancho
- Mobile: Ocupa casi toda la pantalla
- Adaptable a diferentes tamaños

## 🔒 Seguridad

- Verificación de nonce en todas las peticiones AJAX
- Sanitización de todos los inputs
- Validación de emails
- Protección contra acceso directo a archivos

## 🆘 Soporte

Para soporte o preguntas:
- Email: soporte@metmallorca.com
- Web: https://metmallorca.com/contacto

## 📝 Changelog

### Versión 1.0.0 (2025-11-06)
- Lanzamiento inicial
- Flujo completo de reservas aeropuerto
- Flujo punto a punto
- Verificación de reservas
- Integración con WooCommerce
- Diseño responsive
- Cumplimiento RGPD

## 📄 Licencia

GPL v2 or later

## 👨‍💻 Autor

MET Mallorca
https://metmallorca.com
