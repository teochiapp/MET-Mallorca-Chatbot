# 🎬 Demo Visual - MET Mallorca Chatbot

## 🖼️ Capturas de Pantalla (Descripción)

### 1. Botón Flotante
```
┌─────────────────────────────────────┐
│                                     │
│                                     │
│                                     │
│                                     │
│                              ┌───┐  │
│                              │ 💬 │  │ ← Botón flotante
│                              └───┘  │    (esquina inferior derecha)
└─────────────────────────────────────┘
```

### 2. Ventana del Chat Abierta
```
┌──────────────────────────────────────┐
│ 🤖 Asistente MET Mallorca    [─]    │ ← Header azul
├──────────────────────────────────────┤
│                                      │
│  🤖 ¡Hola! Soy el asistente de      │
│     MET Mallorca. Te ayudo a         │
│     reservar en 1 minuto.            │
│                                      │
│     ¿De dónde a dónde viajas?        │
│                                      │
│  ┌────────────────────────────────┐ │
│  │ ✈️ Aeropuerto ↔ Punto         │ │ ← Botones
│  └────────────────────────────────┘ │   de opciones
│  ┌────────────────────────────────┐ │
│  │ 🚗 Punto ↔ Punto              │ │
│  └────────────────────────────────┘ │
│  ┌────────────────────────────────┐ │
│  │ 🔍 Verificar mi reserva       │ │
│  └────────────────────────────────┘ │
│                                      │
├──────────────────────────────────────┤
│ Al continuar aceptas la Política... │ ← Footer RGPD
└──────────────────────────────────────┘
```

### 3. Conversación en Progreso
```
┌──────────────────────────────────────┐
│ 🤖 Asistente MET Mallorca    [─]    │
├──────────────────────────────────────┤
│                                      │
│  🤖 ¡Hola! Soy el asistente...      │
│                                      │
│              Aeropuerto ↔ Punto 👤  │
│                                      │
│  🤖 Perfecto. ¿Desde qué            │
│     aeropuerto sales o llegas?       │
│                                      │
│              Aeropuerto de Palma 👤 │
│                                      │
│  🤖 ¿Cuál es tu destino?            │
│                                      │
│  ┌────────────────────────────────┐ │
│  │ Hotel Nixe, Palma          [→] │ │ ← Input de texto
│  └────────────────────────────────┘ │
│                                      │
└──────────────────────────────────────┘
```

### 4. Resumen Final
```
┌──────────────────────────────────────┐
│ 🤖 Asistente MET Mallorca    [─]    │
├──────────────────────────────────────┤
│                                      │
│  🤖 ✅ Resumen de tu reserva:       │
│                                      │
│     ✈️ Aeropuerto de Palma →        │
│        Hotel Nixe                    │
│     👥 6 persona(s)                  │
│     🐾 Mascota: Perro pequeño        │
│     📅 15/11/2025 - 09:00           │
│     ✈️ Vuelo: IB1234                │
│                                      │
│     Todo listo. Puedes reservar      │
│     y pagar ahora:                   │
│                                      │
│  ┌────────────────────────────────┐ │
│  │ 🎯 Reservar ahora             │ │
│  └────────────────────────────────┘ │
│                                      │
└──────────────────────────────────────┘
```

---

## 🎭 Ejemplos de Conversaciones

### Ejemplo 1: Reserva Estándar (Aeropuerto → Hotel)

```
🤖: ¡Hola! Soy el asistente de MET Mallorca. 
    Te ayudo a reservar en 1 minuto.
    ¿De dónde a dónde viajas?

👤: [Clic en] Aeropuerto ↔ Punto

🤖: Perfecto. ¿Desde qué aeropuerto sales o llegas?

👤: [Clic en] Aeropuerto de Palma (PMI)

🤖: ¿Cuál es tu destino?
    Escribe el nombre del hotel, dirección o zona:

👤: [Escribe] Hotel Nixe, Palma

🤖: ¿Cuántas personas viajan?

👤: [Escribe] 6

🤖: ¿Viajas con mascota?

👤: [Clic en] Sí, perro pequeño

🤖: ¿Fecha y hora del traslado?
    Formato: DD/MM/YYYY - HH:MM

👤: [Escribe] 15/11/2025 - 09:00

🤖: ¿Número de vuelo? (opcional)

👤: [Escribe] IB1234

🤖: ✅ Resumen de tu reserva:
    ✈️ Aeropuerto de Palma → Hotel Nixe
    👥 6 persona(s)
    🐾 Mascota: Perro pequeño
    📅 15/11/2025 - 09:00
    ✈️ Vuelo: IB1234
    
    Todo listo. Puedes reservar y pagar ahora:
    [🎯 Reservar ahora]
```

---

### Ejemplo 2: Grupo Grande (>20 personas)

```
🤖: ¡Hola! Soy el asistente de MET Mallorca.
    ¿De dónde a dónde viajas?

👤: [Clic en] Aeropuerto ↔ Punto

🤖: ¿Desde qué aeropuerto sales o llegas?

👤: [Clic en] Aeropuerto de Palma (PMI)

🤖: ¿Cuál es tu destino?

👤: [Escribe] Cala Millor

🤖: ¿Cuántas personas viajan?

👤: [Escribe] 35

🤖: 👥 Son 35 pasajeros, perfecto.
    
    Para grupos de más de 20 personas, gestionamos
    el traslado mediante un presupuesto personalizado.
    
    [📋 Solicitar presupuesto de grupo]
```

---

### Ejemplo 3: Punto a Punto

```
🤖: ¡Hola! ¿De dónde a dónde viajas?

👤: [Clic en] Punto ↔ Punto dentro de Mallorca

🤖: Perfecto. ¿Desde qué punto de Mallorca sales?
    Escribe la ciudad o dirección de origen:

👤: [Escribe] Sóller

🤖: ¿Cuál es tu destino?
    Escribe la ciudad o dirección de destino:

👤: [Escribe] Port d'Andratx

🤖: ¿Cuántas personas viajan?

👤: [Escribe] 4

🤖: 🚗 Trayecto: Sóller → Port d'Andratx
    👥 4 personas
    
    Este tipo de traslados se gestiona mediante
    nuestro formulario de presupuesto personalizado.
    
    [📋 Solicitar presupuesto]
```

---

### Ejemplo 4: Verificación de Reserva (Exitosa)

```
🤖: ¡Hola! ¿De dónde a dónde viajas?

👤: [Clic en] Verificar mi reserva

🤖: 🔍 Por favor, escribe tu número de reserva
    (ej. MET-123456) y tu email separados por coma.
    
    Ejemplo: MET-123456, email@ejemplo.com

👤: [Escribe] MET-123, juan@ejemplo.com

🤖: 🔍 Verificando tu reserva...

🤖: ✅ Tu reserva fue realizada con MET Mallorca.
    
    Detalles de tu reserva:
    📋 Ref: MET-123
    👤 Cliente: Juan Pérez
    📧 Email: juan@ejemplo.com
    📞 Teléfono: 612345678
    📅 Fecha: 15/11/2025 09:00
    💰 Total: 50,00€
    📊 Estado: ✅ Confirmada
    
    Servicios:
    • Traslado Aeropuerto - Hotel (x1)
    
    Detalles del traslado:
    📍 Origen: Aeropuerto de Palma
    📍 Destino: Hotel Nixe
    📅 Fecha/Hora: 15/11/2025 - 09:00
    👥 Pasajeros: 6
    
    [📋 Ver detalles completos]
    [✏️ Modificar reserva]
    [🏠 Volver al inicio]
```

---

### Ejemplo 5: Verificación Fallida (No es MET)

```
🤖: ¡Hola! ¿De dónde a dónde viajas?

👤: [Clic en] Verificar mi reserva

🤖: 🔍 Por favor, escribe tu número de reserva
    y tu email separados por coma.

👤: [Escribe] ABC-789, maria@ejemplo.com

🤖: 🔍 Verificando tu reserva...

🤖: ❌ No encontramos esa reserva en MET Mallorca.
    
    ¿Podría ser de otra empresa?
    
    Puedes adjuntar foto del voucher o indicarnos
    la empresa que figura en tu comprobante.
    
    [🆕 Hacer nueva reserva]
    [📞 Contactar soporte]
```

---

## 🎨 Elementos Visuales

### Colores del Chatbot

```
┌─────────────────────────────────────┐
│ Primario (#0066cc)                  │ ← Header, botones
├─────────────────────────────────────┤
│ Primario Oscuro (#004d99)           │ ← Hover effects
├─────────────────────────────────────┤
│ Secundario (#00cc66)                │ ← Avatar usuario
├─────────────────────────────────────┤
│ Blanco (#ffffff)                    │ ← Mensajes bot
├─────────────────────────────────────┤
│ Gris Claro (#f5f5f5)                │ ← Fondo mensajes
└─────────────────────────────────────┘
```

### Iconos Utilizados

```
🤖 - Avatar del bot
👤 - Avatar del usuario
✈️ - Aeropuerto / Vuelo
🚗 - Traslado punto a punto
🔍 - Verificar reserva
📍 - Ubicación
👥 - Pasajeros
🐾 - Mascota
📅 - Fecha
💰 - Precio
📋 - Detalles / Presupuesto
✅ - Confirmado
❌ - Error / No encontrado
📧 - Email
📞 - Teléfono
🎯 - Acción principal
✏️ - Modificar
🏠 - Inicio
```

### Animaciones

1. **Apertura del chat**: Slide up + fade in (0.3s)
2. **Nuevos mensajes**: Fade in + translate Y (0.3s)
3. **Typing indicator**: Dots bouncing (1.4s loop)
4. **Hover en botones**: Scale + translate X (0.2s)
5. **Botón flotante hover**: Scale 1.1 (0.3s)

---

## 📱 Responsive Design

### Desktop (>768px)
```
┌────────────────────────────────────────────┐
│                                            │
│                                            │
│                                            │
│                                     ┌────┐ │
│                                     │Chat│ │
│                                     │    │ │
│                                     │    │ │
│                                     │    │ │
│                                     └────┘ │
│                                      [💬]  │
└────────────────────────────────────────────┘
Ancho: 380px | Alto: 600px
```

### Mobile (<768px)
```
┌──────────────────────┐
│                      │
│                      │
│                      │
│                      │
│                      │
│  ┌────────────────┐ │
│  │                │ │
│  │     Chat       │ │
│  │   (Fullscreen) │ │
│  │                │ │
│  │                │ │
│  │                │ │
│  └────────────────┘ │
│        [💬]         │
└──────────────────────┘
Ancho: 100vw - 20px
Alto: 100vh - 100px
```

---

## 🎯 Estados del Chatbot

### Estado 1: Cerrado
- Botón flotante visible
- Icono de chat (💬)
- Hover: Scale 1.1

### Estado 2: Abierto
- Ventana visible
- Botón muestra X
- Mensajes cargados

### Estado 3: Escribiendo
- Dots animados
- Usuario no puede enviar
- Scroll automático

### Estado 4: Esperando input
- Input de texto visible
- Placeholder dinámico
- Focus automático

### Estado 5: Mostrando opciones
- Botones visibles
- Input oculto
- Hover effects activos

---

## 🔄 Flujo de Interacción

```
Usuario abre sitio web
         ↓
Ve botón flotante en esquina
         ↓
Hace clic en botón
         ↓
Ventana se abre con animación
         ↓
Mensaje de bienvenida aparece
         ↓
Opciones se muestran
         ↓
Usuario selecciona opción
         ↓
Mensaje del usuario aparece
         ↓
Typing indicator se muestra
         ↓
Respuesta del bot aparece
         ↓
Nuevas opciones o input
         ↓
[Ciclo se repite]
         ↓
Resumen final
         ↓
Botón de acción (Reservar/Presupuesto)
         ↓
Usuario hace clic
         ↓
Se abre nueva página con datos
```

---

## 💡 Tips de Uso

### Para el Usuario

1. **Inicio rápido**: Solo haz clic en el botón azul
2. **Respuestas rápidas**: Usa los botones cuando aparezcan
3. **Escribir**: Cuando veas el campo de texto, escribe tu respuesta
4. **Correcciones**: Si te equivocas, el bot te guiará
5. **Verificación**: Siempre revisa el resumen antes de confirmar

### Para el Administrador

1. **Monitoreo**: Revisa las órdenes en WooCommerce
2. **Personalización**: Edita mensajes en el código PHP
3. **Colores**: Cambia variables CSS para tu marca
4. **Testing**: Prueba todos los flujos antes de producción
5. **Backup**: Guarda copia antes de modificar

---

## 🎬 Video Tutorial (Guión)

### Parte 1: Instalación (2 min)
1. Mostrar carpeta del plugin
2. Ir a WordPress Admin
3. Activar plugin
4. Verificar que aparece el botón

### Parte 2: Reserva Estándar (3 min)
1. Abrir chatbot
2. Seleccionar Aeropuerto ↔ Punto
3. Completar todos los datos
4. Ver resumen
5. Hacer clic en Reservar

### Parte 3: Grupo Grande (2 min)
1. Abrir chatbot
2. Ingresar más de 20 personas
3. Ver mensaje de presupuesto
4. Hacer clic en solicitar presupuesto

### Parte 4: Verificación (2 min)
1. Abrir chatbot
2. Seleccionar Verificar reserva
3. Ingresar código y email
4. Ver detalles de la reserva

### Parte 5: Personalización (3 min)
1. Mostrar archivo CSS
2. Cambiar colores
3. Mostrar archivo PHP
4. Cambiar mensajes
5. Guardar y probar

---

## 📊 Métricas de Éxito

### KPIs a Monitorear

1. **Tasa de apertura**: % de visitantes que abren el chat
2. **Tasa de completación**: % que completan el flujo
3. **Tiempo promedio**: Minutos para completar reserva
4. **Conversión**: % que hacen clic en "Reservar ahora"
5. **Abandono**: En qué paso abandonan más usuarios

### Objetivos Esperados

- ⏱️ Tiempo de reserva: < 2 minutos
- 📈 Tasa de completación: > 70%
- 💰 Conversión: > 40%
- 📞 Reducción de llamadas: > 50%
- ⭐ Satisfacción: > 4.5/5

---

*Demo creado para MET Mallorca Chatbot v1.0.0*
