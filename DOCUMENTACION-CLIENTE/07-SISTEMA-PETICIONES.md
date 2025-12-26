# 📱 Sistema de Peticiones de Música (QR Codes) - Simona Music

## 📋 Índice
1. [¿Qué son las Peticiones de Música?](#qué-son-las-peticiones-de-música)
2. [Activar Peticiones](#activar-peticiones)
3. [Generar Código QR](#generar-código-qr)
4. [Gestionar Peticiones](#gestionar-peticiones)
5. [Para los Clientes](#para-los-clientes)
6. [Configuración Avanzada](#configuración-avanzada)

---

## ¿Qué son las Peticiones de Música?

El **Sistema de Peticiones** permite que tus clientes **pidan canciones** escaneando un código QR con su celular.

**Cómo funciona** (es súper simple):
1. 📱 Cliente escanea QR code con su celular
2. 🎵 Ve lista de canciones disponibles
3. ✅ Selecciona la que quiere escuchar
4. ⏰ Su petición entra en cola
5. 🎶 La canción suena cuando le toque

---

### Beneficios para tu Negocio

✅ **Interacción con clientes**: Se sienten parte de la experiencia
✅ **Más tiempo en el local**: Esperan a que suene "su" canción
✅ **Consumo aumentado**: Clientes felices = más consumo
✅ **Sin interrupciones**: No tienen que pedirle al DJ o camarero
✅ **Profesional**: Imagen moderna y tecnológica

---

## Activar Peticiones

### Paso 1: Habilitar la Función

1. Entra a tu terraza en Simona Music
2. Ve a **Profile** → **Edit Profile**
3. Busca la opción: **"Enable Song Requests"** (Habilitar Peticiones de Canciones)
4. ✅ **Márcala** (actívala)
5. Haz clic en **"Save Changes"**

¡Listo! Las peticiones ya están activadas.

---

### Paso 2: Configurar Restricciones (Opcional)

Puedes configurar límites para evitar abuso:

En **Profile → Edit Profile**, busca **"Request Settings"**:

#### 🕒 **Request Delay** (Retraso entre Peticiones)

Tiempo mínimo que debe pasar antes de que la misma persona pueda pedir otra canción.

**Valores recomendados**:
- `5 minutos` → Muy permisivo
- `15 minutos` → Normal ✅ **Recomendado**
- `30 minutos` → Restrictivo
- `60 minutos` → Muy restrictivo

**¿Por qué?**: Evita que una sola persona monopolice las peticiones.

---

#### 🚫 **Request Threshold** (Límite de Peticiones)

Cuántas veces puede haber sonado una canción recientemente para poder pedirla de nuevo.

**Ejemplo**:
- Si pones `2`: Una canción que sonó en las últimas 2 horas NO se puede pedir
- Si pones `0`: Sin límite, se puede pedir cualquier canción en cualquier momento

**Valor recomendado**: `2-3` ✅

---

## Generar Código QR

Una vez activadas las peticiones, necesitas un **QR Code** que tus clientes puedan escanear.

### URL de Peticiones

Cada terraza tiene su propia URL de peticiones:

```
https://simonamusic.net/public/[nombre-de-tu-terraza]/qr-request
```

**Ejemplo**:
- Si tu terraza es `bar-centro`:
  ```
  https://simonamusic.net/public/bar-centro/qr-request
  ```

- Si tu terraza es `terraza-playa`:
  ```
  https://simonamusic.net/public/terraza-playa/qr-request
  ```

---

### Generar el QR Code

#### Opción 1: Herramienta Online (Más Fácil)

1. Ve a: https://www.qr-code-generator.com/
2. En **"URL"**, pega tu URL de peticiones
3. Personaliza (opcional):
   - **Logo**: Sube el logo de tu terraza
   - **Colores**: Personaliza los colores del QR
   - **Frame**: Agrega un marco con texto como "Pide tu canción"
4. Haz clic en **"Download"** (Descargar)
5. Guarda el QR como imagen PNG o PDF

---

#### Opción 2: Herramienta Rápida

1. Ve a: https://api.qrserver.com/v1/create-qr-code/
2. Agrega tu URL al final:
   ```
   https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=https://simonamusic.net/public/tu-terraza/qr-request
   ```
3. Cambia `tu-terraza` por el nombre real de tu terraza
4. Abre esa URL en tu navegador
5. Haz clic derecho en el QR → **"Guardar imagen como..."**

---

### Imprimir y Colocar el QR

Una vez que tienes tu QR Code:

**Recomendaciones de impresión**:
- **Tamaño mínimo**: 10 x 10 cm
- **Tamaño ideal**: 15 x 15 cm o 20 x 20 cm
- **Formato**: Imprime en alta resolución (300 DPI mínimo)
- **Plastificado**: Para que resista líquidos y uso constante

---

**Lugares ideales para colocar el QR**:
- 📋 **En las mesas** (atril, porta-menú, sticker)
- 🍺 **En la barra** (letrero visible)
- 🚪 **En la entrada** (cartel grande)
- 🧾 **En la carta/menú** (esquina o contraportada)
- 🖼️ **En las paredes** (cuadros decorativos)

**Texto recomendado para acompañar**:
```
🎵 PIDE TU CANCIÓN
Escanea este código con tu celular
y elige la música que quieres escuchar
```

O más corto:
```
📱 Escanea y pide tu canción
```

---

## Gestionar Peticiones

### Ver Peticiones Activas

1. Ve a tu terraza en Simona Music
2. Menú lateral → **Requests** (Peticiones)
3. Verás una lista de todas las peticiones:
   - ⏳ **Pendientes**: Aún no han sonado
   - ✅ **Completadas**: Ya sonaron
   - ❌ **Rechazadas**: Fueron rechazadas manualmente

---

### Estados de una Petición

Cada petición pasa por estos estados:

#### 1. 🟦 **Pending** (Pendiente)
- Acaba de ser solicitada
- Está esperando su turno
- El cliente puede verla en su pantalla

#### 2. 🟨 **Queued** (En Cola)
- Ya está en la cola de reproducción
- Sonará pronto (siguiente en lista)
- El cliente ve un mensaje de "¡Tu canción está en cola!"

#### 3. 🟩 **Accepted / Played** (Aceptada / Reproducida)
- La canción ya sonó
- El cliente ve "¡Tu canción fue reproducida!"
- No se puede volver a pedir (por un tiempo)

#### 4. 🟥 **Rejected** (Rechazada)
- Fue rechazada manualmente
- Puede ser por contenido inapropiado, duplicada, etc.
- El cliente ve "Solicitud rechazada"

---

### Aprobar o Rechazar Peticiones

Si tienes **modo manual** activado (ver [Configuración Avanzada](#configuración-avanzada)):

**Aprobar**:
1. En **Requests**, busca la petición
2. Haz clic en **"Approve"** (Aprobar) o ✅
3. La canción entra en la cola de reproducción

**Rechazar**:
1. En **Requests**, busca la petición
2. Haz clic en **"Reject"** (Rechazar) o ❌
3. Opcionalmente puedes agregar una razón (no es necesario)
4. El cliente verá que fue rechazada

---

### Filtrar Peticiones

Usa los filtros para ver solo lo que necesitas:

- **All** (Todas): Todas las peticiones
- **Pending** (Pendientes): Solo las que están esperando
- **Played** (Reproducidas): Solo las que ya sonaron
- **Rejected** (Rechazadas): Solo las rechazadas

---

## Para los Clientes

### Experiencia del Cliente (Paso a Paso)

Así es como tus clientes usarán el sistema:

#### 1️⃣ Escanear QR

1. Cliente abre la **cámara** de su celular
2. Apunta al **QR Code**
3. Toca la notificación que aparece
4. Se abre la página web de peticiones

**No necesita app** - funciona directo en el navegador.

---

#### 2️⃣ Buscar Canción

1. Ve un **buscador** grande en la pantalla
2. Escribe el nombre de la canción o artista:
   - Ejemplo: `Despacito`
   - Ejemplo: `Luis Fonsi`
   - Ejemplo: `Shape of You`

3. Ve resultados en tiempo real mientras escribe
4. Muestra:
   - 🎵 Título de la canción
   - 🎤 Artista
   - 💿 Álbum (si tiene)

---

#### 3️⃣ Seleccionar Canción

1. Toca la canción que quiere
2. Se resalta en **azul** (seleccionada)
3. Ve botón **"Solicitar"** o **"Pedir esta canción"**

---

#### 4️⃣ Información Opcional (Personalización)

Si quiere, puede agregar:
- 👤 **Su nombre**: Ej. "María"
- 🖼️ **Avatar URL**: URL de una foto (opcional)
- 💬 **Comentario**: Ej. "Es mi cumpleaños!" o "Dedicado a Juan"

**Todos estos campos son OPCIONALES** - puede dejarlos vacíos.

---

#### 5️⃣ Enviar Petición

1. Toca botón **"Enviar Solicitud"**
2. Ve mensaje: ✅ **"Solicitud recibida"**
3. Aparece una **tarjeta con el estado**:
   - 🟦 Estado: **Pendiente**
   - 🎵 Canción: El título que pidió
   - 🎤 Artista: Nombre del artista

---

#### 6️⃣ Seguir el Estado

La pantalla se actualiza **automáticamente cada 5 segundos** mostrando:

**Si está pendiente**:
```
⏳ Solicitud recibida
Tu solicitud de "Despacito" por Luis Fonsi ha sido recibida 
y será procesada pronto.
```

**Cuando entra en cola**:
```
🎶 ¡Tu canción está en cola!
"Despacito" por Luis Fonsi está ahora en la cola de reproducción.
```

**Cuando ya sonó**:
```
🎉 ¡Tu canción fue reproducida!
"Despacito" por Luis Fonsi ya fue reproducida. 
¡Gracias por tu solicitud!
```

---

#### 7️⃣ Pedir Otra Canción

Una vez que su canción sonó, puede:
- Hacer clic en **"Hacer otra solicitud"**
- Y repetir el proceso

**Restricción**: Debe esperar el tiempo configurado (15 min por defecto).

---

## Configuración Avanzada

### Modo Manual vs Automático

#### 🔀 **Modo Automático** (Recomendado)

- Las peticiones se **aceptan automáticamente**
- No necesitas aprobarlas manualmente
- Las canciones entran directo en la cola
- **Ideal para**: Bares, terrazas sin DJ en vivo

**Configuración**: Ya está así por defecto, no necesitas hacer nada.

---

#### ✋ **Modo Manual**

- TÚ decides qué peticiones se aceptan
- Puedes rechazar canciones inapropiadas
- Más control pero más trabajo
- **Ideal para**: Eventos especiales, DJs en vivo que quieren control total

**Configuración**:
1. Ve a **Profile → Edit Profile**
2. Busca **"Manual Request Approval"**
3. ✅ Márcala
4. **Save Changes**

Ahora verás botones "Approve" y "Reject" en cada petición.

---

### Canciones Permitidas vs Bloqueadas

#### Permitir todas las canciones (Normal)

Por defecto, los clientes pueden pedir **cualquier canción** que esté en tu biblioteca.

---

#### Limitar a listas específicas

Si quieres que solo puedan pedir canciones de ciertas listas:

1. Ve a **Playlists**
2. Edita una lista
3. Busca la opción: **"Allow Requests from this Playlist"**
4. ✅ Márcala
5. **Save**

Ahora solo las listas marcadas serán "solicitables".

---

#### Bloquear canciones individuales

Si hay canciones que NO quieres que se pidan:

1. Ve a **Media → Music Files**
2. Encuentra la canción
3. Haz clic en **"Edit"**
4. Busca: **"Allow Requests"**
5. ❌ **Desmárcala**
6. **Save**

Esa canción NO aparecerá en la búsqueda de peticiones.

---

## Ejemplos de Uso

### Ejemplo 1: Bar de Copas

**Setup recomendado**:
```
✅ Peticiones activadas
✅ Modo automático
⏰ Delay: 15 minutos
🚫 Threshold: 2 (no repetir canciones recientes)
📋 QR en todas las mesas
```

**Resultado**: Clientes piden canciones libremente, sistema acepta automáticamente, ambiente divertido e interactivo.

---

### Ejemplo 2: Restaurante Fino

**Setup recomendado**:
```
✅ Peticiones activadas
✅ Modo manual (el encargado aprueba)
⏰ Delay: 30 minutos
🚫 Threshold: 5
📋 QR en mesas + carta
🎵 Solo lista "Música Elegante" es solicitble
```

**Resultado**: Control total sobre qué suena, solo música apropiada, ambiente controlado.

---

### Ejemplo 3: Evento con DJ en Vivo

**Setup recomendado**:
```
✅ Peticiones activadas
✅ Modo manual (DJ aprueba en vivo)
⏰ Delay: 20 minutos
📋 QR en pantallas grandes + mesas
🎧 DJ ve peticiones en tiempo real
```

**Resultado**: DJ puede leer peticiones, decidir cuáles mezclar, interactuar con el público.

---

## Troubleshooting (Solución de Problemas)

### ❌ Cliente escanea QR pero no abre nada

**Soluciones**:
1. Verifica que el QR tenga la URL correcta
2. Regenera el QR code
3. Prueba tú mismo con tu celular
4. Asegúrate de que las peticiones estén activadas en la configuración

---

### ❌ No aparecen canciones en la búsqueda

**Causas y soluciones**:

1. **No hay música subida**:
   - Sube canciones a Media → Music Files

2. **Las listas no están marcadas como solicitables**:
   - Ve a Playlists → Edita cada lista
   - Marca "Allow Requests"

3. **Las canciones están bloqueadas**:
   - Ve a Media → Music Files
   - Edita las canciones y asegúrate de que "Allow Requests" esté marcado

---

### ❌ Las peticiones no suenan

**Checklist**:
- [ ] ¿Hay música automática reproduciéndose? (si no hay, las peticiones no entran)
- [ ] ¿Hay un DJ en vivo? (las peticiones pueden no aplicarse durante transmisión en vivo)
- [ ] ¿El modo manual está activado? (tienes que aprobar las peticiones manualmente)
- [ ] ¿La canción existe en la biblioteca?

---

### ❌ Clientes pueden pedir la misma canción muchas veces

**Solución**:
1. Aumenta el **Request Delay** (30-60 minutos)
2. Aumenta el **Request Threshold** (3-5)

---

## Preguntas Frecuentes

### ❓ ¿Necesito internet en mi local para que funcione?
**Respuesta**: Tu sistema de Simona Music necesita internet, sí. Los clientes usan su propio internet móvil (4G/5G) para pedir canciones.

### ❓ ¿Los clientes necesitan instalar una app?
**Respuesta**: NO. Todo funciona directo en el navegador del celular. Solo escanean y listo.

### ❓ ¿Puedo ver quién pidió qué canción?
**Respuesta**: Sí, si el cliente puso su nombre (opcional). Si no, verás "Anónimo".

### ❓ ¿Las peticiones tienen costo para el cliente?
**Respuesta**: NO. Es totalmente gratis para ellos (solo usan sus datos móviles).

### ❓ ¿Puedo tener múltiples QR codes?
**Respuesta**: Sí, puedes imprimir el mismo QR en todas las mesas. Todos van a la misma URL.

### ❓ ¿Qué pasa si hay un DJ en vivo?
**Respuesta**: El DJ puede ver las peticiones y decidir si las toca. O puede ignorarlas completamente.

### ❓ ¿Los clientes pueden ver qué canciones hay disponibles sin pedir?
**Respuesta**: Sí, pueden buscar y ver toda la biblioteca antes de pedir.

---

## 🎯 Checklist: Activar Peticiones Correctamente

Usa esto para verificar que todo esté configurado:

### Configuración Básica
- [ ] Peticiones activadas en Profile → Edit Profile
- [ ] Delay configurado (15-30 min recomendado)
- [ ] Threshold configurado (2-3 recomendado)

### Contenido
- [ ] Tengo al menos 100 canciones subidas
- [ ] Las listas están marcadas como "Allow Requests" (o todas si quiero)
- [ ] Verifiqué que las canciones aparezcan en búsqueda

### QR Code
- [ ] Generé el QR code con la URL correcta
- [ ] Imprimí en tamaño adecuado (15x15 cm mínimo)
- [ ] Plastifiqué o enmicé para proteger
- [ ] Coloqué en lugares visibles

### Testing
- [ ] Escaneé el QR con mi celular
- [ ] Busqué una canción y la encontré
- [ ] Hice una petición de prueba
- [ ] La petición apareció en mi panel de admin
- [ ] La canción sonó (en modo automático) o la aprobé (modo manual)

### Comunicación
- [ ] Informé al personal sobre el sistema
- [ ] Puse carteles o indicaciones para clientes
- [ ] Preparé respuestas a preguntas frecuentes

---

¡Ya tienes todo listo para que tus clientes pidan canciones! 📱🎵

**Siguiente**: Lee la **Guía 08** para aprender a personalizar los colores y logo de tu terraza.
