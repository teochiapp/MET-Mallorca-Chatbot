# 🔍 Diagnóstico: Buscador con JSON

## Problema Reportado
El buscador no encuentra ubicaciones después de migrar de CSV a JSON.

---

## ✅ Cambios Realizados

### 1. **Normalización de Ubicaciones en PHP**

Actualizado `class-pricing-engine.php` para normalizar todas las ubicaciones a **Title Case**:

```php
private function sanitize_location_label($label) {
    $label = trim((string) $label);
    $label = preg_replace('/\s+/', ' ', $label);
    $label = preg_replace('/\s*,\s*/', ', ', $label);
    $label = preg_replace('/\s*\/\s*/', ' / ', $label);
    
    // Normalizar capitalización a título
    $label = ucwords(strtolower($label));
    
    return $label;
}
```

**Antes**: `SON CARRIO`, `son servera`, `PALMA`  
**Ahora**: `Son Carrio`, `Son Servera`, `Palma`

### 2. **Mejora en Carga de Ubicaciones (JavaScript)**

Añadido logs de depuración y validación robusta:

```javascript
success: function(response) {
    console.log('MetLocationSearcher: respuesta recibida', response);
    
    if (response && response.success && response.data && Array.isArray(response.data.locations)) {
        self.locations = response.data.locations;
        console.log('MetLocationSearcher: ' + self.locations.length + ' ubicaciones cargadas');
        if (callback) callback(self.locations);
    } else {
        console.warn('MetLocationSearcher: respuesta inválida', response);
        if (callback) callback(self.locations);
    }
}
```

### 3. **Precarga Automática con Reintentos**

El buscador ahora intenta cargar ubicaciones automáticamente al iniciar:

```javascript
$(document).ready(function() {
    LocationSearcher.init([]);
    
    const preloadLocations = function(attempts) {
        if (typeof metChatbot !== 'undefined' && metChatbot.ajaxUrl) {
            LocationSearcher.loadLocations();
        } else if (attempts > 0) {
            setTimeout(function() {
                preloadLocations(attempts - 1);
            }, 100);
        }
    };
    
    preloadLocations(30); // Reintentar durante ~3s
});
```

### 4. **Respuesta AJAX Mejorada**

Actualizado el handler PHP para incluir información adicional:

```php
wp_send_json_success(array(
    'locations' => $locations,
    'total' => count($locations)
));
```

---

## 🧪 Pasos de Diagnóstico

### Paso 1: Verificar Carga del JSON

Abre en el navegador:
```
http://localhost/chatbot-mallorca/wp-content/plugins/met-chatbot/test-json-load.php
```

**Debe mostrar**:
- ✅ Archivo existe
- ✅ JSON válido
- ✅ 136 ubicaciones cargadas
- ✅ Lista de todas las ubicaciones

### Paso 2: Verificar Consola del Navegador

1. Abre el chatbot
2. Presiona `F12` para abrir DevTools
3. Ve a la pestaña **Console**
4. Busca estos mensajes:

```
✅ Esperado:
MetLocationSearcher: respuesta recibida {success: true, data: {locations: Array(136), total: 136}}
MetLocationSearcher: 136 ubicaciones cargadas

❌ Error:
MetLocationSearcher: respuesta inválida al cargar ubicaciones
MetLocationSearcher: error al cargar ubicaciones
```

### Paso 3: Verificar AJAX en Network

1. En DevTools, ve a la pestaña **Network**
2. Filtra por `XHR`
3. Busca la petición `admin-ajax.php?action=met_get_locations`
4. Verifica la respuesta:

```json
{
    "success": true,
    "data": {
        "locations": [
            "Alaro",
            "Alcudia",
            "Algaida",
            ...
        ],
        "total": 136
    }
}
```

### Paso 4: Verificar Variable Global

En la consola del navegador, ejecuta:

```javascript
// Ver si el buscador está inicializado
console.log(window.MetLocationSearcher);

// Ver cuántas ubicaciones tiene
console.log(window.MetLocationSearcher.locations.length);

// Ver las primeras 5 ubicaciones
console.log(window.MetLocationSearcher.locations.slice(0, 5));
```

**Resultado esperado**:
```
Object {locations: Array(136), config: {...}, ...}
136
["Alaro", "Alcudia", "Algaida", "Arta", "Banyalbufar"]
```

### Paso 5: Probar Búsqueda Manual

En la consola:

```javascript
// Probar filtrado
const results = window.MetLocationSearcher.filterLocations('palma');
console.log('Resultados para "palma":', results);
```

**Resultado esperado**:
```
Resultados para "palma": ["Palma", "Palma Nova", "Palmanova"]
```

---

## 🐛 Problemas Comunes y Soluciones

### Problema 1: "metChatbot is not defined"

**Causa**: Los scripts no se están cargando en el orden correcto.

**Solución**: Verificar en `met-chatbot.php` que `location-searcher.js` depende de `chatbot.js`:

```php
wp_enqueue_script(
    'met-location-searcher',
    MET_CHATBOT_PLUGIN_URL . 'assets/js/location-searcher.js',
    array('jquery', 'met-chatbot'),  // ← Dependencia de met-chatbot
    MET_CHATBOT_VERSION,
    true
);
```

### Problema 2: "0 ubicaciones cargadas"

**Causa**: El JSON no se está leyendo correctamente o está vacío.

**Solución**:
1. Ejecutar `test-json-load.php` para verificar
2. Verificar permisos del archivo JSON (debe ser legible)
3. Verificar que el archivo no esté corrupto

### Problema 3: "Respuesta inválida"

**Causa**: La estructura de la respuesta AJAX no coincide con lo esperado.

**Solución**: Verificar que el handler PHP devuelve:
```php
wp_send_json_success(array(
    'locations' => $locations  // ← Debe ser un array
));
```

### Problema 4: "No se encontraron ubicaciones"

**Causa**: El filtrado no encuentra coincidencias.

**Solución**: Verificar que:
- Las ubicaciones están normalizadas (Title Case)
- La búsqueda es case-insensitive
- No hay espacios extra en los nombres

### Problema 5: Ubicaciones con mayúsculas inconsistentes

**Causa**: El JSON tiene nombres en diferentes formatos (`PALMA`, `Palma`, `palma`).

**Solución**: Ya aplicado - `sanitize_location_label()` normaliza todo a Title Case.

---

## 🔧 Comandos de Depuración

### Verificar en PHP (WordPress)

Añade esto temporalmente en `get_locations()`:

```php
public function get_locations() {
    check_ajax_referer('met_chatbot_nonce', 'nonce');
    
    require_once MET_CHATBOT_PLUGIN_DIR . 'includes/class-pricing-engine.php';
    $pricing_engine = new MET_Pricing_Engine();
    $locations = $pricing_engine->get_all_locations();
    
    // DEBUG: Log para verificar
    error_log('MET Chatbot: Ubicaciones cargadas: ' . count($locations));
    error_log('MET Chatbot: Primeras 5: ' . json_encode(array_slice($locations, 0, 5)));
    
    wp_send_json_success(array(
        'locations' => $locations,
        'total' => count($locations)
    ));
}
```

Luego revisa el log de errores de WordPress:
```
wp-content/debug.log
```

### Verificar en JavaScript

Añade esto en `location-searcher.js` después de cargar:

```javascript
loadLocations: function(callback) {
    const self = this;
    
    // DEBUG: Antes de la petición
    console.log('🔍 Iniciando carga de ubicaciones...');
    console.log('URL AJAX:', metChatbot.ajaxUrl);
    console.log('Nonce:', metChatbot.nonce);
    
    $.ajax({
        url: metChatbot.ajaxUrl,
        method: 'POST',
        data: {
            action: 'met_get_locations',
            nonce: metChatbot.nonce
        },
        success: function(response) {
            // DEBUG: Respuesta completa
            console.log('✅ Respuesta recibida:', response);
            console.log('Tipo de data:', typeof response.data);
            console.log('Tipo de locations:', typeof response.data.locations);
            console.log('Es array?', Array.isArray(response.data.locations));
            
            if (response && response.success && response.data && Array.isArray(response.data.locations)) {
                self.locations = response.data.locations;
                console.log('✅ ' + self.locations.length + ' ubicaciones cargadas');
                console.log('Primeras 5:', self.locations.slice(0, 5));
                if (callback) callback(self.locations);
            } else {
                console.error('❌ Respuesta inválida');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error AJAX:', status, error);
            console.error('Response:', xhr.responseText);
        }
    });
}
```

---

## ✅ Checklist de Verificación

Antes de reportar un problema, verifica:

- [ ] El archivo `precios_locations_data.json` existe
- [ ] El JSON es válido (sin errores de sintaxis)
- [ ] El archivo tiene permisos de lectura
- [ ] `test-json-load.php` muestra 136 ubicaciones
- [ ] La consola muestra "X ubicaciones cargadas"
- [ ] No hay errores en la consola del navegador
- [ ] La petición AJAX a `admin-ajax.php` devuelve 200 OK
- [ ] La respuesta JSON contiene el array `locations`
- [ ] `window.MetLocationSearcher.locations.length > 0`
- [ ] El buscador aparece en el chatbot
- [ ] El input del buscador es visible y funcional

---

## 📊 Estructura Esperada

### JSON (precios_locations_data.json)
```json
{
    "Palma": {
        "1-4": 55,
        "5-8": 75,
        "9-12": 95,
        "13-16": 115
    }
}
```

### PHP (get_all_locations)
```php
array(
    0 => "Alaro",
    1 => "Alcudia",
    2 => "Algaida",
    ...
)
```

### AJAX Response
```json
{
    "success": true,
    "data": {
        "locations": ["Alaro", "Alcudia", ...],
        "total": 136
    }
}
```

### JavaScript (MetLocationSearcher.locations)
```javascript
["Alaro", "Alcudia", "Algaida", ...]
```

---

## 🚀 Próximos Pasos

Si después de todas estas verificaciones el buscador sigue sin funcionar:

1. **Limpiar caché**:
   - Caché del navegador (Ctrl + Shift + Delete)
   - Caché de WordPress (si usas plugin de caché)
   - Caché del servidor

2. **Verificar conflictos**:
   - Desactivar otros plugins temporalmente
   - Cambiar a un tema por defecto (Twenty Twenty-Four)
   - Verificar errores de JavaScript de otros plugins

3. **Revisar logs**:
   - `wp-content/debug.log`
   - Consola del navegador (F12)
   - Logs del servidor (Apache/Nginx)

4. **Probar en incógnito**:
   - Abrir el sitio en modo incógnito
   - Verificar si funciona sin extensiones del navegador

---

## 📝 Notas Finales

- El JSON debe tener **encoding UTF-8**
- Los nombres de ubicaciones se normalizan a **Title Case**
- La búsqueda es **case-insensitive**
- El buscador requiere **mínimo 1 carácter** para buscar
- Se muestran máximo **10 resultados** por búsqueda

---

**Última actualización**: Noviembre 2024  
**Versión del plugin**: 2.1.0
