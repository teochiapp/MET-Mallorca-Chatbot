# 🎉 Nueva Estrategia: Productos Únicos por Reserva

## Cambio Implementado

### ❌ Estrategia Anterior (Problemática)
```
1. Usar un producto genérico "Servicio de Traslado" (precio €0)
2. Modificar el precio dinámicamente con hooks de WooCommerce
3. Depender de sesiones para mantener los datos
4. Aplicar el precio en el carrito con `set_price()`
```

**Problemas:**
- ❌ Dependía de múltiples hooks que podían fallar
- ❌ Conflictos con otros plugins
- ❌ Problemas con sesiones y caché
- ❌ Difícil de debuggear
- ❌ El precio podía perderse entre pasos

### ✅ Nueva Estrategia (Robusta)
```
1. Crear un producto NUEVO para cada reserva
2. Asignar número de reserva autoincremental (MET-2025-0001)
3. Establecer el precio directamente en el producto
4. Incluir toda la información en el título y descripción
5. Agregar al carrito normalmente
```

**Ventajas:**
- ✅ No depende de hooks complejos
- ✅ Compatible con cualquier tema/plugin
- ✅ El precio nunca se pierde
- ✅ Cada reserva es rastreable
- ✅ Historial completo en WooCommerce
- ✅ Fácil de buscar y gestionar

## Cómo Funciona

### 1. Usuario Completa el Chatbot
```
Origen: Aeropuerto de Palma
Destino: Alcudia
Fecha: 25/12/2025
Hora: 14:30
Pasajeros: 2
Precio calculado: €60
```

### 2. Sistema Crea Producto Único
```php
Título: "Traslado #MET-2025-0001 - Aeropuerto de Palma → Alcudia"
Precio: €60.00 (fijo)
Estado: Publicado (pero oculto del catálogo)
Tipo: Virtual
```

### 3. Descripción Completa del Producto
```html
<h3>Detalles de la Reserva</h3>
<ul>
  <li><strong>Origen:</strong> Aeropuerto de Palma</li>
  <li><strong>Destino:</strong> Alcudia</li>
  <li><strong>Fecha y Hora:</strong> 25/12/2025 - 14:30</li>
  <li><strong>Pasajeros:</strong> 2</li>
</ul>

<h3>Desglose del Precio</h3>
<ul>
  <li>Precio base: €60.00</li>
  <li>Suplemento vehículo: €0.00</li>
  <li>Suplemento nocturno: €0.00</li>
  <li><strong>TOTAL: €60.00</strong></li>
</ul>
```

### 4. Metadata Guardada
```php
_met_booking_number: "MET-2025-0001"
_met_booking_data: {
  origin: "Aeropuerto de Palma",
  destination: "Alcudia",
  date: "25/12/2025",
  time: "14:30",
  passengers: 2,
  ...
}
_met_price_breakdown: {
  base_price: 60,
  total: 60,
  ...
}
_met_created_at: "2025-11-19 01:15:00"
```

### 5. Producto se Agrega al Carrito
```
Producto: Traslado #MET-2025-0001 - Aeropuerto de Palma → Alcudia
Precio: €60.00
Cantidad: 1
Total: €60.00
```

## Gestión de Reservas

### Ver Todas las Reservas
1. Ve a **WooCommerce → Productos**
2. Busca "Traslado #MET"
3. Verás todas las reservas creadas

### Buscar una Reserva Específica
1. Ve a **WooCommerce → Productos**
2. Busca por número: "MET-2025-0001"
3. O busca por destino: "Alcudia"

### Ver Detalles de una Reserva
1. Abre el producto
2. La descripción contiene todos los detalles
3. Los metadatos contienen la información estructurada

### Eliminar Reservas Antiguas
Las reservas no pagadas pueden eliminarse manualmente o con un cron job:

```php
// Eliminar productos de reserva más antiguos de 7 días sin pedido
function cleanup_old_bookings() {
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_met_booking_number',
                'compare' => 'EXISTS'
            ),
            array(
                'key' => '_met_created_at',
                'value' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'compare' => '<',
                'type' => 'DATETIME'
            )
        )
    );
    
    $products = get_posts($args);
    
    foreach ($products as $product) {
        // Verificar si tiene pedido asociado
        $orders = wc_get_orders(array(
            'limit' => 1,
            'product' => $product->ID
        ));
        
        // Si no tiene pedido, eliminar
        if (empty($orders)) {
            wp_delete_post($product->ID, true);
        }
    }
}

// Ejecutar diariamente
add_action('wp_scheduled_delete', 'cleanup_old_bookings');
```

## Formato del Número de Reserva

```
MET-YYYY-NNNN

MET: Prefijo del negocio
YYYY: Año actual (2025)
NNNN: Número secuencial de 4 dígitos (0001, 0002, etc.)

Ejemplos:
- MET-2025-0001
- MET-2025-0002
- MET-2025-0123
- MET-2026-0001 (se reinicia cada año)
```

## Reiniciar Contador Anualmente

Si quieres reiniciar el contador cada año:

```php
// En functions.php o en el plugin
function reset_booking_counter_yearly() {
    $current_year = date('Y');
    $last_reset_year = get_option('met_chatbot_last_reset_year', 0);
    
    if ($current_year != $last_reset_year) {
        update_option('met_chatbot_last_booking_number', 0);
        update_option('met_chatbot_last_reset_year', $current_year);
    }
}
add_action('init', 'reset_booking_counter_yearly');
```

## Reportes y Estadísticas

### Contar Reservas por Mes
```sql
SELECT 
    DATE_FORMAT(meta_value, '%Y-%m') as mes,
    COUNT(*) as total_reservas
FROM wp_postmeta
WHERE meta_key = '_met_created_at'
GROUP BY mes
ORDER BY mes DESC;
```

### Ingresos por Destino
```sql
SELECT 
    pm1.meta_value as destino,
    COUNT(*) as reservas,
    SUM(pm2.meta_value) as ingresos_totales
FROM wp_posts p
JOIN wp_postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_met_booking_data'
JOIN wp_postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_price'
WHERE p.post_type = 'product'
GROUP BY destino
ORDER BY ingresos_totales DESC;
```

## Integración con Pedidos

Cuando un cliente completa el pago:
1. El pedido se crea normalmente en WooCommerce
2. El producto de la reserva queda asociado al pedido
3. Puedes ver el número de reserva en el pedido
4. El producto NO se elimina (queda como historial)

## Ventajas para el Cliente

1. **Email de confirmación** incluye todos los detalles
2. **Factura** muestra el número de reserva
3. **Historial de pedidos** permite ver reservas pasadas
4. **Búsqueda fácil** por número de reserva

## Ventajas para el Administrador

1. **Gestión centralizada** en WooCommerce
2. **Búsqueda rápida** por número o destino
3. **Reportes nativos** de WooCommerce funcionan
4. **Exportación fácil** a CSV/Excel
5. **Integración con plugins** de facturación, CRM, etc.

## Migración desde la Versión Anterior

No es necesaria ninguna migración. El sistema simplemente:
1. Deja de usar el producto genérico "Servicio de Traslado"
2. Empieza a crear productos únicos
3. Las reservas antiguas siguen funcionando normalmente

## Preguntas Frecuentes

### ¿Se crean muchos productos?
Sí, pero están ocultos del catálogo. Solo aparecen en el admin.

### ¿Afecta al rendimiento?
No significativamente. Los productos están indexados y son rápidos de buscar.

### ¿Puedo cambiar el formato del número?
Sí, modifica la función `get_next_booking_number()` en `class-checkout-generator.php`.

### ¿Puedo personalizar la descripción?
Sí, modifica la función `generate_product_description()`.

### ¿Los productos se eliminan automáticamente?
No, pero puedes implementar un cron job para limpiar reservas no pagadas.

---

**Implementado:** 2025-11-19
**Versión:** 2.1.0
