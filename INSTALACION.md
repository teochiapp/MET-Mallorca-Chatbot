# 🚀 Guía de Instalación Rápida - MET Mallorca Chatbot

## ✅ Paso 1: Verificar Requisitos

Antes de instalar, asegúrate de tener:

- ✅ WordPress 5.0 o superior
- ✅ WooCommerce instalado y activado
- ✅ PHP 7.4 o superior
- ✅ Acceso al panel de administración de WordPress

## 📦 Paso 2: Instalar el Plugin

### Opción A: Instalación Manual (Recomendada)

1. **El plugin ya está en la carpeta correcta:**
   ```
   /wp-content/plugins/met-chatbot/
   ```

2. **Ve al panel de WordPress:**
   - Accede a: `http://localhost/metmallorca/wp-admin/`
   - Usuario y contraseña de administrador

3. **Activa el plugin:**
   - Ve a: **Plugins** → **Plugins instalados**
   - Busca: **MET Mallorca Chatbot**
   - Haz clic en: **Activar**

### Opción B: Verificar Instalación

Si no ves el plugin en la lista, verifica que la estructura sea correcta:

```
wp-content/
└── plugins/
    └── met-chatbot/
        ├── met-chatbot.php          ← Archivo principal
        ├── README.md
        ├── INSTALACION.md
        ├── includes/
        ├── assets/
        └── templates/
```

## 🎯 Paso 3: Verificar Funcionamiento

1. **Abre tu sitio web:**
   ```
   http://localhost/metmallorca/
   ```

2. **Busca el botón del chatbot:**
   - Debe aparecer en la **esquina inferior derecha**
   - Es un círculo azul con un icono de chat

3. **Prueba el chatbot:**
   - Haz clic en el botón
   - Debería abrirse la ventana del chat
   - Verás el mensaje de bienvenida

## ⚙️ Paso 4: Configuración Básica

### A) Configurar URL de Reservas

Edita el archivo:
```
/wp-content/plugins/met-chatbot/includes/class-conversation-flow.php
```

Busca la línea 238 (método `generate_booking_url`):
```php
$base_url = home_url('/reservar/');
```

Cámbiala por la URL real de tu página de reservas:
```php
$base_url = home_url('/tu-pagina-de-reservas/');
```

### B) Personalizar Colores (Opcional)

Edita el archivo:
```
/wp-content/plugins/met-chatbot/assets/css/chatbot.css
```

Cambia las variables CSS (líneas 6-12):
```css
:root {
    --met-primary: #0066cc;        /* Tu color principal */
    --met-primary-dark: #004d99;   /* Versión más oscura */
    --met-secondary: #00cc66;      /* Color secundario */
}
```

### C) Configurar Política de Privacidad

Asegúrate de tener una página de Política de Privacidad en:
```
http://localhost/metmallorca/politica-de-privacidad/
```

Si tu URL es diferente, edita:
```
/wp-content/plugins/met-chatbot/templates/chatbot-widget.php
```

Línea 70:
```php
<a href="<?php echo home_url('/tu-url-de-privacidad/'); ?>" target="_blank">
```

## 🧪 Paso 5: Probar Flujos

### Flujo 1: Reserva Aeropuerto (≤20 personas)

1. Abre el chatbot
2. Selecciona: **✈️ Aeropuerto ↔ Punto**
3. Elige: **Aeropuerto de Palma**
4. Escribe destino: `Hotel Nixe, Palma`
5. Número de personas: `6`
6. Mascota: **Sí, perro pequeño**
7. Fecha: `15/11/2025 - 09:00`
8. Vuelo: `IB1234`
9. Verifica el resumen
10. Clic en **Reservar ahora**

### Flujo 2: Reserva Grupo (>20 personas)

1. Abre el chatbot
2. Selecciona: **✈️ Aeropuerto ↔ Punto**
3. Elige: **Aeropuerto de Palma**
4. Escribe destino: `Cala Millor`
5. Número de personas: `35`
6. Verás mensaje de presupuesto personalizado
7. Clic en **Solicitar presupuesto de grupo**

### Flujo 3: Punto a Punto

1. Abre el chatbot
2. Selecciona: **🚗 Punto ↔ Punto**
3. Origen: `Sóller`
4. Destino: `Port d'Andratx`
5. Personas: `4`
6. Verás mensaje de presupuesto
7. Clic en **Solicitar presupuesto**

### Flujo 4: Verificar Reserva

1. Abre el chatbot
2. Selecciona: **🔍 Verificar mi reserva**
3. Escribe: `MET-123, email@ejemplo.com`
4. El sistema buscará la reserva

**Nota:** Para que funcione, necesitas tener una orden en WooCommerce con ese ID.

## 🔧 Solución de Problemas

### El chatbot no aparece

1. **Verifica que el plugin esté activado:**
   - WordPress Admin → Plugins
   - Busca "MET Mallorca Chatbot"
   - Debe decir "Desactivar" (no "Activar")

2. **Limpia la caché:**
   - Si usas un plugin de caché, límpialo
   - Ctrl + F5 en el navegador

3. **Verifica errores en consola:**
   - F12 en el navegador
   - Ve a la pestaña "Console"
   - Busca errores en rojo

### El chatbot no responde

1. **Verifica AJAX:**
   - F12 → Network
   - Intenta enviar un mensaje
   - Busca llamadas a `admin-ajax.php`
   - Verifica que respondan con código 200

2. **Verifica permisos:**
   - Los archivos deben ser legibles por el servidor web

### Los estilos no se cargan

1. **Verifica la ruta del CSS:**
   ```
   http://localhost/metmallorca/wp-content/plugins/met-chatbot/assets/css/chatbot.css
   ```

2. **Limpia caché del navegador:**
   - Ctrl + Shift + Delete
   - Borra caché e imágenes

## 📊 Integración con WooCommerce

### Crear producto de prueba

1. **Ve a:** WooCommerce → Productos → Añadir nuevo
2. **Nombre:** Traslado Aeropuerto - Hotel
3. **Precio:** 50€
4. **Publicar**

### Crear orden de prueba

1. **Ve a:** WooCommerce → Pedidos → Añadir nuevo
2. **Añade producto:** Traslado Aeropuerto - Hotel
3. **Datos del cliente:**
   - Nombre: Juan Pérez
   - Email: juan@ejemplo.com
   - Teléfono: 612345678
4. **Crear pedido**
5. **Anota el ID:** Por ejemplo, 123
6. **Código de reserva será:** MET-123

### Probar verificación

1. Abre el chatbot
2. Selecciona: **Verificar mi reserva**
3. Escribe: `MET-123, juan@ejemplo.com`
4. Debería mostrar los detalles de la reserva

## 🎨 Personalización Avanzada

### Cambiar posición del botón

En `assets/css/chatbot.css`, línea 17:

```css
.met-chatbot-widget {
    position: fixed;
    bottom: 20px;    /* Distancia desde abajo */
    right: 20px;     /* Distancia desde la derecha */
    z-index: 9999;
}
```

Para ponerlo a la izquierda:
```css
left: 20px;      /* En lugar de right */
```

### Cambiar tamaño del botón

En `assets/css/chatbot.css`, línea 26:

```css
.met-chatbot-toggle {
    width: 60px;     /* Ancho */
    height: 60px;    /* Alto */
}
```

### Cambiar tamaño de la ventana

En `assets/css/chatbot.css`, línea 64:

```css
.met-chatbot-window {
    width: 380px;    /* Ancho */
    height: 600px;   /* Alto */
}
```

## 📱 Prueba en Móvil

1. **Obtén tu IP local:**
   ```
   ipconfig
   ```
   Busca: IPv4 Address (ej: 192.168.1.100)

2. **Accede desde el móvil:**
   ```
   http://192.168.1.100/metmallorca/
   ```

3. **Verifica que sea responsive:**
   - El chatbot debe ocupar casi toda la pantalla
   - Los botones deben ser fáciles de tocar

## ✅ Checklist Final

- [ ] Plugin activado en WordPress
- [ ] Botón del chatbot visible en la web
- [ ] Chatbot abre y cierra correctamente
- [ ] Mensaje de bienvenida aparece
- [ ] Botones de opciones funcionan
- [ ] Input de texto funciona
- [ ] Flujo de aeropuerto completo
- [ ] Flujo de punto a punto completo
- [ ] Verificación de reservas funciona
- [ ] Responsive en móvil
- [ ] Colores personalizados (opcional)
- [ ] URL de reservas configurada

## 🆘 Soporte

Si tienes problemas:

1. **Revisa los logs de WordPress:**
   ```
   /wp-content/debug.log
   ```

2. **Activa el modo debug en wp-config.php:**
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

3. **Contacta soporte:**
   - Email: soporte@metmallorca.com
   - Web: https://metmallorca.com/contacto

## 🎉 ¡Listo!

Tu chatbot MET Mallorca está instalado y funcionando. 

**Próximos pasos:**
1. Personaliza los mensajes según tu marca
2. Configura los productos en WooCommerce
3. Prueba todos los flujos con datos reales
4. Entrena a tu equipo en el uso del sistema

¡Disfruta de tu nuevo asistente automatizado! 🚀
