# 📅 Programación de Contenido - Simona Music

## 📋 Índice
1. [¿Qué es la Programación?](#qué-es-la-programación)
2. [Vista de Programación (Schedule View)](#vista-de-programación-schedule-view)
3. [Programar Listas por Horarios](#programar-listas-por-horarios)
4. [Estrategias de Programación](#estrategias-de-programación)
5. [Ejemplos Prácticos](#ejemplos-prácticos)
6. [Solución de Problemas](#solución-de-problemas)

---

## ¿Qué es la Programación?

La **programación** en Simona Music te permite decidir **QUÉ música suena** y **CUÁNDO suena** en cada terraza.

**Piensa en esto como**:
- 📺 La programación de TV (cada show en su horario)
- 📻 La programación de radio (diferentes shows en diferentes horas)
- 🎵 Tu terraza tendrá música diferente según el momento del día

---

### Beneficios de Programar

✅ **Ambiente adecuado en cada momento**
- Música tranquila por la mañana
- Música comercial al mediodía
- Música de fiesta por la noche

✅ **Automatización total**
- Configuras una vez
- Se repite automáticamente cada día
- No necesitas estar pendiente

✅ **Profesionalismo**
- Tu terraza siempre tiene la música apropiada
- Tus clientes notan la diferencia

---

## Vista de Programación (Schedule View)

### Acceder a la Programación

1. Entra a tu terraza
2. Ve a **Playlists** (en el menú lateral)
3. Haz clic en la pestaña **"Schedule View"**

---

### El Calendario de Programación

Verás un calendario visual que muestra:
- 📅 Los días de la semana (columnas)
- ⏰ Las horas del día (filas)
- 🎵 Qué lista está programada en cada momento
- 🎨 Código de colores (cada lista tiene su color)

**Ejemplo visual**:
```
        LUN     MAR     MIÉ     JUE     VIE     SÁB     DOM
08:00   [Lista Mañana - azul]
10:00   [Lista Mañana - azul]
12:00   [Lista Mediodía - verde]
14:00   [Lista Mediodía - verde]
16:00   [Lista Tarde - naranja]
18:00   [Lista Tarde - naranja]
20:00   [Lista Noche - morado]  [Lista Noche - morado]
22:00   [Lista Noche - morado]  [Lista Noche - morado]
```

---

### Entender el Calendario

**Bloques de color**:
- Cada bloque representa una lista programada
- El color identifica qué lista es
- La altura del bloque muestra cuánto tiempo dura

**Espacios en blanco**:
- Si hay espacios en blanco, significa que NO hay lista programada
- En ese caso, sonarán las listas "Standard" (si las tienes)

---

## Programar Listas por Horarios

### Método 1: Programar desde la Lista

Este es el método más común y fácil.

#### Paso 1: Crear o Editar una Lista

1. Ve a **Playlists**
2. Si la lista ya existe, haz clic en **"Edit"**
3. Si no existe, haz clic en **"+ Add Playlist"**

---

#### Paso 2: Configurar como "Scheduled"

En el formulario de la lista:

1. **Playlist Type** → Selecciona **"Scheduled"**
2. Aparecerá la sección **"Schedule"**

---

#### Paso 3: Agregar Horario

1. En la sección Schedule, haz clic en **"+ Add Schedule Entry"**
2. Configura los campos:

##### ⏰ **Start Time** (Hora de Inicio)

La hora a la que EMPIEZA a sonar esta lista.

**Formato**: 24 horas (00:00 a 23:59)

**Ejemplos**:
- `08:00` = 8 de la mañana
- `14:30` = 2:30 de la tarde
- `20:00` = 8 de la noche
- `23:45` = 11:45 de la noche

---

##### ⏰ **End Time** (Hora de Fin)

La hora a la que TERMINA de sonar esta lista.

**Importante**: 
- Si pones la misma hora que el start, sonará solo 1 vez al día en ese momento exacto
- Si el end time es MENOR que el start time, significa que cruza la medianoche

**Ejemplos**:
```
Start: 08:00, End: 12:00  → Suena de 8 AM a 12 PM (4 horas)
Start: 20:00, End: 02:00  → Suena de 8 PM a 2 AM (cruza medianoche, 6 horas)
Start: 10:00, End: 10:00  → Suena solo a las 10 AM (1 vez)
```

---

##### 📅 **Days of Week** (Días de la Semana)

Marca los días que debe sonar.

**Opciones**:
- Monday → Lunes
- Tuesday → Martes
- Wednesday → Miércoles
- Thursday → Jueves
- Friday → Viernes
- Saturday → Sábado
- Sunday → Domingo

**Tips**:
- Puedes marcar todos los días para que suene siempre
- O solo fin de semana (sábado y domingo)
- O solo entre semana (lunes a viernes)

---

##### 📆 **Start Date / End Date** (Fechas Opcionales)

Para listas temporales o de temporada.

**Ejemplos de uso**:
- **Música Navideña**: Start Date: `1 Diciembre`, End Date: `6 Enero`
- **Música Verano**: Start Date: `1 Junio`, End Date: `30 Septiembre`
- **Promoción Especial**: Start Date: `15 Marzo`, End Date: `15 Marzo` (solo ese día)

**Si lo dejas en blanco**: La programación es permanente (todo el año).

---

#### Paso 4: Agregar Múltiples Horarios (Opcional)

Una misma lista puede tener varios horarios.

**Ejemplo**: Quieres que "Música Chill" suene por la mañana Y por la noche

1. Agrega Schedule Entry 1:
   - Start: `08:00`, End: `10:00`
   - Days: Todos

2. Agrega Schedule Entry 2:
   - Start: `23:00`, End: `01:00`
   - Days: Todos

**Resultado**: La lista "Música Chill" sonará de 8-10 AM y de 11 PM a 1 AM.

---

#### Paso 5: Guardar

1. Revisa que los horarios sean correctos
2. Haz clic en **"Save Changes"**
3. Verifica en el **Schedule View** que aparezca correctamente

---

## Estrategias de Programación

### Estrategia 1: División por Momentos del Día

**Para**: Bares, cafeterías, terrazas generales

**Setup**:
```
🌅 Música Mañana (08:00 - 12:00)
- Contenido: Música suave, jazz, acústico
- Volumen: Bajo-Medio
- Objetivo: Ambiente relajado

☀️ Música Mediodía (12:00 - 16:00)
- Contenido: Pop comercial, hits
- Volumen: Medio
- Objetivo: Energía moderada

🌆 Música Tarde (16:00 - 20:00)
- Contenido: Música variada, indie, alternativa
- Volumen: Medio
- Objetivo: Ambiente agradable

🌙 Música Noche (20:00 - 02:00)
- Contenido: Electrónica, dance, house
- Volumen: Alto
- Objetivo: Ambiente de fiesta
```

---

### Estrategia 2: División por Días

**Para**: Terrazas que cambian según el día de la semana

**Setup**:
```
📚 Lunes a Jueves (Entre Semana)
08:00 - 22:00: Música relajada todo el día
Objetivo: Público tranquilo, trabajo, estudios

🎉 Viernes y Sábado (Fin de Semana)
12:00 - 16:00: Música comercial energética
16:00 - 21:00: Música pre-party (reggaeton, house)
21:00 - 03:00: Música de fiesta (electrónica, dance)

☀️ Domingo (Relax)
10:00 - 14:00: Brunch music (jazz, soul)
14:00 - 22:00: Música chill (indie, alternativa)
```

---

### Estrategia 3: Por Tipo de Público

**Para**: Espacios con clientela variada

**Setup**:
```
👔 Horario Laboral (Lun-Vie, 08:00-18:00)
- Música instrumental
- Sin letras explícitas
- Volumen bajo

👨‍👩‍👧 Familia (Sáb-Dom, 12:00-20:00)
- Música familiar
- Pop limpio
- Volumen medio

🎊 Jóvenes (Vie-Sáb, 20:00-03:00)
- Música comercial actual
- Reggaeton, trap, electrónica
- Volumen alto
```

---

### Estrategia 4: Temporadas

**Para**: Adaptarse a eventos y temporadas

**Ejemplo de Año Completo**:
```
🎄 Navidad (1 Dic - 6 Ene)
- Todo el día: Lista "Música Navideña"
- Prioridad sobre otras listas

☀️ Verano (1 Jun - 30 Sep)
- Aumentar peso de lista "Música Tropical"
- Más reggaeton y música latina

🍂 Otoño/Invierno (1 Oct - 30 Nov, 7 Ene - 31 May)
- Programación regular
- Más música indie y alternativa

💝 San Valentín (14 Feb)
- Solo ese día: Lista "Música Romántica"

🎃 Halloween (31 Oct)
- Solo ese día: Lista "Música Temática Halloween"
```

---

## Ejemplos Prácticos

### Ejemplo 1: Bar de Playa

**Contexto**: Bar en la playa, abierto de 10:00 a 02:00

**Programación**:

#### Lista 1: "Chill Beach Morning"
```
Tipo: Scheduled
Horario: 10:00 - 14:00
Días: Todos
Contenido: Reggae, bossa nova, acoustic
Peso: N/A (es scheduled)
```

#### Lista 2: "Afternoon Vibes"
```
Tipo: Scheduled
Horario: 14:00 - 18:00
Días: Todos
Contenido: Pop, indie, rock suave
```

#### Lista 3: "Sunset Session"
```
Tipo: Scheduled
Horario: 18:00 - 21:00
Días: Todos
Contenido: Deep house, chill electronic
```

#### Lista 4: "Night Party"
```
Tipo: Scheduled
Horario: 21:00 - 02:00
Días: Viernes, Sábado, Domingo
Contenido: Electrónica, dance, reggaeton
```

#### Lista 5: "Night Relax"
```
Tipo: Scheduled
Horario: 21:00 - 02:00
Días: Lunes, Martes, Miércoles, Jueves
Contenido: Lounge, jazz, chill
```

---

### Ejemplo 2: Café-Librería

**Contexto**: Café tranquilo para trabajar y leer, 08:00-22:00

**Programación**:

#### Lista 1: "Morning Coffee"
```
Tipo: Scheduled
Horario: 08:00 - 12:00
Días: Todos
Contenido: Jazz instrumental, piano, classical
Volumen: Muy bajo
```

#### Lista 2: "Afternoon Study"
```
Tipo: Scheduled
Horario: 12:00 - 18:00
Días: Lunes a Viernes
Contenido: Lo-fi, ambient, post-rock
Volumen: Bajo
```

#### Lista 3: "Evening Relax"
```
Tipo: Scheduled
Horario: 18:00 - 22:00
Días: Todos
Contenido: Indie folk, acoustic, singer-songwriter
Volumen: Medio-bajo
```

---

### Ejemplo 3: Gimnasio

**Contexto**: Gimnasio 24 horas

**Programación**:

#### Lista 1: "Early Bird Workout"
```
Tipo: Scheduled
Horario: 05:00 - 09:00
Días: Todos
Contenido: Pop energético, rock
Ritmo: Medio-alto
```

#### Lista 2: "Midday Motivation"
```
Tipo: Scheduled
Horario: 09:00 - 14:00
Días: Todos
Contenido: EDM, electrónica, hip-hop
Ritmo: Alto
```

#### Lista 3: "Afternoon Energy"
```
Tipo: Scheduled
Horario: 14:00 - 20:00
Días: Todos
Contenido: Rock, metal, trap
Ritmo: Muy alto
```

#### Lista 4: "Night Session"
```
Tipo: Scheduled
Horario: 20:00 - 05:00
Días: Todos
Contenido: Techno, house, trance
Ritmo: Constante alto
```

---

## Solución de Problemas

### Problema: "No suena la lista en su horario"

**Checklist**:
1. [ ] ¿La lista está activada? (switch en ON)
2. [ ] ¿La lista tiene canciones? (mínimo 10)
3. [ ] ¿El horario está bien configurado?
4. [ ] ¿Los días están marcados correctamente?
5. [ ] ¿La zona horaria de la terraza es correcta?
6. [ ] ¿Hay solapamiento con otra lista de mayor prioridad?

**Solución**:
1. Ve a **Playlists → Schedule View**
2. Verifica visualmente que la lista aparezca en el calendario
3. Si no aparece, revisa la configuración de horarios
4. Si aparece pero no suena, revisa los logs en **Logs**

---

### Problema: "Silencio entre listas"

**Causa**: Hay un hueco en la programación.

**Solución 1 - Crear lista de relleno**:
```
Lista: "Música General"
Tipo: Standard (NO scheduled)
Peso: 10
Contenido: Música variada
```

Esta lista sonará cuando no haya ninguna lista programada.

**Solución 2 - Extender horarios**:
- Asegúrate de que las listas cubran todo el día
- No dejes huecos entre horarios

---

### Problema: "Se solapan dos listas"

**Causa**: Dos listas programadas a la misma hora.

**¿Qué pasa?**
- Simona Music elegirá una (normalmente la primera)
- La otra no sonará

**Solución**:
1. Ve a **Schedule View**
2. Identifica el solapamiento visualmente
3. Ajusta los horarios para que no se crucen:

**Ejemplo de CORRECCIÓN**:
```
❌ ANTES (mal):
Lista A: 10:00 - 14:00
Lista B: 12:00 - 16:00  (solapan 12:00-14:00)

✅ DESPUÉS (bien):
Lista A: 10:00 - 14:00
Lista B: 14:00 - 16:00  (sin solapamiento)
```

---

### Problema: "La lista suena en días incorrectos"

**Causa**: Días mal marcados.

**Solución**:
1. Edita la lista
2. Ve a la sección Schedule
3. Revisa que los días estén correctamente marcados
4. Guarda cambios

**Recuerda**:
- Monday = Lunes
- Friday = Viernes
- Saturday = Sábado
- Sunday = Domingo

---

### Problema: "Lista temporal no se desactiva"

**Causa**: No configuraste End Date.

**Solución**:
1. Edita la lista
2. En Schedule Entry, pon:
   - **End Date**: Fecha de finalización
3. Guarda

**Ejemplo para Navidad**:
```
Start Date: 1 December
End Date: 6 January
```

Después del 6 de enero, la lista dejará de sonar automáticamente.

---

## Buenas Prácticas

### ✅ Planificación

**Antes de programar**:
1. Escribe en papel tu programación ideal
2. Define horarios claros y sin solapamientos
3. Asegúrate de cubrir todo el día
4. Piensa en diferentes días de la semana

---

### ✅ Testing

**Cómo probar tu programación**:

1. **Vista Schedule View**:
   - Revisa visualmente el calendario
   - Debe estar todo cubierto con colores
   - No debe haber solapamientos

2. **Prueba en vivo**:
   - Activa las listas
   - Escucha en diferentes horarios
   - Verifica que cambie correctamente

3. **Ajustes graduales**:
   - No cambies todo de golpe
   - Prueba una lista nueva primero
   - Si funciona, implementa el resto

---

### ✅ Mantenimiento

**Cada semana**:
- [ ] Revisa si las listas se están reproduciendo
- [ ] Verifica que no haya silencios
- [ ] Ajusta si algo no funciona

**Cada mes**:
- [ ] Actualiza el contenido de las listas
- [ ] Agrega música nueva
- [ ] Elimina canciones que no funcionan
- [ ] Ajusta horarios según feedback de clientes

**Cada temporada**:
- [ ] Crea listas temáticas (verano, navidad, etc.)
- [ ] Ajusta pesos de listas según la época
- [ ] Actualiza programación según eventos

---

## Preguntas Frecuentes

### ❓ ¿Puedo cambiar la programación en cualquier momento?
**Respuesta**: Sí, los cambios se aplican inmediatamente.

### ❓ ¿Qué pasa si hay un DJ en vivo durante una hora programada?
**Respuesta**: El DJ tiene prioridad. Cuando termina, vuelve la programación automática.

### ❓ ¿Puedo programar la misma lista en varios horarios?
**Respuesta**: Sí, agrega múltiples Schedule Entries a la misma lista.

### ❓ ¿Cómo hago para que suene música 24/7?
**Respuesta**: Crea listas que cubran todo el día o usa listas Standard de respaldo.

### ❓ ¿Puedo tener programación diferente cada día?
**Respuesta**: Sí, crea listas separadas para diferentes días de la semana.

### ❓ ¿Qué pasa con el cambio de horario (verano/invierno)?
**Respuesta**: Simona Music usa la zona horaria que configuraste. Se ajusta automáticamente.

---

## 🎯 Checklist: Programación Perfecta

Usa esto para verificar tu programación:

### Configuración
- [ ] Zona horaria de la terraza es correcta
- [ ] Tengo al menos 3 listas diferentes
- [ ] Cada lista tiene mínimo 20 canciones

### Horarios
- [ ] Todos los horarios cubren 24/7 (o el horario de apertura)
- [ ] No hay solapamientos entre listas
- [ ] Los días están correctamente marcados
- [ ] Las fechas especiales están configuradas (si aplica)

### Activación
- [ ] Todas las listas están en ON
- [ ] Verifiqué en Schedule View que se vea correcto
- [ ] Probé escuchando en diferentes horarios

### Respaldo
- [ ] Tengo al menos 1 lista Standard de respaldo
- [ ] Las listas tienen suficiente contenido
- [ ] Exporté un backup de la configuración

---

¡Ya dominas la programación de contenido en Simona Music! 📅🎵

**Siguiente**: Lee la **Guía 06** para aprender a transmitir en vivo como DJ profesional.
