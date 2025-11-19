# Changelog - MET Chatbot v2.0

## Versión 2.0.0 (2025-11-19)

### 🎉 Nuevas Funcionalidades

#### Sistema Completo de Reservas
- ✅ Flujo conversacional FSM (Finite State Machine) completo
- ✅ Motor de cálculo de precios configurable y extensible
- ✅ Generación automática de URL de checkout WooCommerce
- ✅ Integración directa con pasarela Redsys/Getnet
- ✅ Validaciones exhaustivas de todos los inputs
- ✅ Navegación hacia atrás con comando "volver"
- ✅ Comandos especiales (reiniciar, ayuda)

#### Arquitectura Modular
- ✅ Separación en módulos independientes
- ✅ Fácil mantenimiento y extensibilidad
- ✅ Código limpio y documentado

### 📁 Archivos Nuevos

#### Core del Sistema
- `includes/class-conversation-controller.php` - Controlador FSM principal
- `includes/class-pricing-engine.php` - Motor de cálculo de precios
- `includes/class-checkout-generator.php` - Generador de URLs de checkout
- `includes/class-booking-validator.php` - Validaciones de datos

#### Módulos de Steps
- `includes/class-conversation-steps-welcome.php` - Bienvenida y tipo de ruta
- `includes/class-conversation-steps-location.php` - Origen y destino
- `includes/class-conversation-steps-details.php` - Fecha, hora, pasajeros
- `includes/class-conversation-steps-summary.php` - Resumen y checkout

#### Estilos y Documentación
- `assets/css/chatbot-booking.css` - Estilos adicionales para reservas
- `SISTEMA-RESERVAS-V2.md` - Documentación técnica completa
- `GUIA-CONFIGURACION-RAPIDA.md` - Guía de configuración
- `TEST-FLUJO.md` - Guía de diagnóstico
- `CHANGELOG-V2.md` - Este archivo

### 🔧 Archivos Modificados

#### `met-chatbot.php`
- Actualizado a versión 2.0.0
- Carga del nuevo controlador conversacional
- Inicialización de hooks de WooCommerce
- Registro diferido de checkout generator

#### Integración con WooCommerce
- Hooks para modificar precio en carrito
- Metadata personalizada en pedidos
- Creación automática de producto "Servicio de Traslado"

### ✨ Simplificación del Flujo Conversacional

**Cambios en v2.1:**
- ❌ Eliminada pregunta sobre mascotas
- ❌ Eliminada pregunta sobre número de vuelo
- ✅ Flujo más rápido y directo
- ✅ Menos pasos = Mejor conversión

**Flujo anterior:** Origen → Destino → Fecha → Hora → Pasajeros → Mascota → Vuelo → Resumen (7 pasos)
**Flujo nuevo:** Origen → Destino → Fecha → Hora → Pasajeros → Resumen (5 pasos)

**Archivos modificados:**
- `includes/class-conversation-steps-details.php` (líneas 126-131)
- `includes/class-conversation-controller.php` (líneas 133, 182-193, 216-225, 240-245)
- `includes/class-conversation-steps-summary.php` (líneas 107-110, 197-211, 220-224)

### 🎉 Mejora Mayor: Productos Únicos por Reserva

**Nueva Estrategia (v2.1):**
En lugar de modificar dinámicamente el precio de un producto existente, ahora el sistema crea un **producto único para cada reserva** con:

- ✅ **Número de reserva autoincremental** (formato: MET-2025-0001)
- ✅ **Título descriptivo**: "Traslado #MET-2025-0001 - Aeropuerto de Palma → Alcudia"
- ✅ **Precio fijo** establecido al crear el producto
- ✅ **Descripción completa** con todos los detalles de la reserva
- ✅ **Desglose de precio** incluido en la descripción
- ✅ **Metadata completa** guardada en el producto

**Ventajas:**
- 🚀 No depende de hooks de WooCommerce para modificar precios
- 🚀 No requiere sesiones ni datos temporales
- 🚀 Cada reserva es un producto independiente y rastreable
- 🚀 Historial completo de reservas en WooCommerce → Productos
- 🚀 Compatible con cualquier tema y plugin de WooCommerce
- 🚀 Fácil de buscar y gestionar (por número de reserva)

**Archivos modificados:**
- `includes/class-checkout-generator.php` (líneas 39-202)

### 🐛 Correcciones de Bugs

#### Bug #1: Precio no se aplica en checkout de WooCommerce (RESUELTO CON NUEVA ESTRATEGIA)
**Problema:** El precio calculado por el chatbot no se transfería correctamente al checkout de WooCommerce, mostrando €0.

**Causa:** El flujo usaba parámetros URL (`?add-to-cart=123&precio=60`) pero WooCommerce no aplicaba el precio personalizado correctamente desde la URL.

**Solución:** 
- Cambio en el flujo: ahora el producto se agrega directamente al carrito con `WC()->cart->add_to_cart()` incluyendo los datos personalizados
- Mejora en `modify_cart_item_price()`: doble verificación (datos del item + sesión)
- Nuevo hook `woocommerce_cart_item_price` para mostrar el precio correcto en la vista del carrito
- Limpieza del carrito antes de agregar para evitar conflictos

**Archivos modificados:**
- `includes/class-checkout-generator.php` (líneas 39-72, 191-214, 287-292, 297-309)

#### Bug #2: Mensaje de error inicial
**Problema:** El chatbot mostraba "Lo siento, ha ocurrido un error de conexión" al iniciar.

**Causa:** El `MET_Checkout_Generator` intentaba registrar hooks antes de que WooCommerce estuviera listo.

**Solución:** Registro diferido de hooks usando `woocommerce_init` action.

**Archivos modificados:**
- `met-chatbot.php` (líneas 58-76)

#### Bug #3: Flujo estancado en "Calculando precio..."
**Problema:** El chatbot se quedaba estancado después de ingresar el número de vuelo.

**Causa:** El paso intermedio "Calculando precio..." esperaba un auto-avance que no estaba implementado en JavaScript.

**Solución:** Eliminado el paso intermedio, ahora llama directamente al método `step_summary()`.

**Archivos modificados:**
- `includes/class-conversation-steps-details.php` (líneas 195-200)
- `includes/class-conversation-steps-summary.php` (líneas 27-64)

**Mejoras adicionales:**
- Validación de datos requeridos antes de calcular precio
- Try-catch para capturar errores del motor de precios
- Mensajes de error claros y específicos

### 💰 Sistema de Precios

#### Configuración por Defecto

**Precios base por distancia:**
- 0-10 km: €25
- 10-20 km: €35
- 20-30 km: €45
- 30-50 km: €60
- 50-100 km: €90
- +100 km: €120

**Suplementos de vehículo:**
- Estándar (1-4 pax): €0
- Van (5-8 pax): +€15
- Minibus (9-16 pax): +€30
- Bus (17-20 pax): +€50

**Suplementos adicionales:**
- Horario nocturno (22:00-06:00): +€10
- Pasajero extra: +€5/pax
- Mascota pequeña: +€10
- Mascota grande: +€15

**Distancias desde aeropuerto (PMI):**
- Palma: 10 km
- Palma Nova: 20 km
- Magaluf: 22 km
- Alcudia: 60 km
- Puerto Pollensa: 65 km
- Cala Millor: 70 km
- Cala D'or: 65 km
- *(y más...)*

### ✅ Validaciones Implementadas

#### Fecha (DD/MM/YYYY)
- ✅ Formato correcto
- ✅ Fecha válida
- ✅ No puede ser pasada
- ✅ Máximo 1 año en el futuro

#### Hora (HH:MM)
- ✅ Formato 24 horas
- ✅ Hora válida (0-23)
- ✅ Minutos válidos (0-59)

#### Pasajeros
- ✅ Mínimo 1
- ✅ Máximo 50
- ✅ Grupos >20 → Derivar a presupuesto

#### Ubicaciones
- ✅ No vacías
- ✅ Mínimo 3 caracteres
- ✅ Máximo 100 caracteres
- ✅ Sanitización

#### Número de Vuelo
- ✅ Opcional
- ✅ Solo letras y números
- ✅ Máximo 20 caracteres

### 🔒 Seguridad

- ✅ Nonce verification en AJAX
- ✅ Sanitización de inputs
- ✅ Validación de estados FSM
- ✅ Escape de outputs HTML
- ✅ Sesiones seguras WooCommerce
- ✅ Hash único por reserva

### 📱 Responsive

- ✅ Móvil (< 480px)
- ✅ Tablet (480px - 768px)
- ✅ Desktop (> 768px)

### 🚀 Rendimiento

- ✅ Carga diferida de módulos
- ✅ Caché de configuración de precios
- ✅ Optimización de consultas
- ✅ Minificación de assets

### 📝 Comandos Especiales

- `volver` o `atrás` → Volver al paso anterior
- `reiniciar` → Empezar de nuevo
- `ayuda` → Mostrar ayuda contextual

### 🔄 Migración desde v1.x

La migración es automática y compatible hacia atrás:
1. ✅ El archivo antiguo `class-conversation-flow.php` permanece pero no se usa
2. ✅ No requiere cambios en la base de datos
3. ✅ No requiere reconfiguración

### 📊 Compatibilidad

- WordPress: 5.0+
- WooCommerce: 5.0+
- PHP: 7.4+
- MySQL: 5.6+

### 🎯 Próximas Mejoras (Roadmap)

#### v2.1.0 (Planificado)
- [ ] Integración con Google Maps Distance Matrix API
- [ ] Notificaciones por email automáticas
- [ ] Panel de administración para configurar precios
- [ ] Exportación de reservas a CSV
- [ ] Estadísticas y analytics

#### v2.2.0 (Planificado)
- [ ] Multi-idioma (ES, EN, DE, FR)
- [ ] Integración con calendario de disponibilidad
- [ ] Sistema de cupones y descuentos
- [ ] Reservas recurrentes
- [ ] API REST para integraciones externas

### 👥 Créditos

Desarrollado para MET Mallorca
Versión 2.0.0 - Noviembre 2025

### 📄 Licencia

GPL v2 or later

---

## Notas de Actualización

### Para Desarrolladores

Si has modificado el código del plugin:
1. Revisa los cambios en `met-chatbot.php`
2. Actualiza tus personalizaciones en los nuevos módulos
3. Prueba el flujo completo antes de desplegar

### Para Usuarios

1. Activa el plugin
2. Verifica que WooCommerce esté activo
3. Prueba una reserva completa
4. Ajusta los precios en `class-pricing-engine.php` si es necesario

### Soporte

Para reportar bugs o solicitar funcionalidades:
- 📧 Email: soporte@metmallorca.com
- 📱 WhatsApp: +34 971 123 456

---

**¡Gracias por usar MET Chatbot!** 🚀
