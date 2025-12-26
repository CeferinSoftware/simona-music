# 🏢 Gestión de Terrazas (Estaciones) - Simona Music

## 📋 Índice
1. [¿Qué es una Terraza en Simona Music?](#qué-es-una-terraza-en-simona-music)
2. [Crear una Nueva Terraza](#crear-una-nueva-terraza)
3. [Configurar tu Terraza](#configurar-tu-terraza)
4. [Panel de Control de Terraza](#panel-de-control-de-terraza)
5. [Gestionar Múltiples Terrazas](#gestionar-múltiples-terrazas)
6. [Eliminar o Desactivar Terrazas](#eliminar-o-desactivar-terrazas)

---

## ¿Qué es una Terraza en Simona Music?

En Simona Music, llamamos **"Terraza"** a cada ubicación o local donde quieres reproducir música. Técnicamente se llaman "Estaciones" (Stations), pero para que sea más fácil de entender, piensa en cada una como un espacio físico.

**Ejemplos de "Terrazas"**:
- 🏖️ Una terraza de playa
- 🍹 Un bar
- ☕ Una cafetería
- 🏨 El lobby de un hotel
- 🏊 El área de piscina de un resort
- 🏢 Cada piso de un edificio comercial

**Cada terraza tiene**:
- ✅ Su propia biblioteca de música (o compartida)
- ✅ Sus propias listas de reproducción
- ✅ Su propia programación horaria
- ✅ Su propia URL para escuchar
- ✅ Sus propios colores y logo
- ✅ Sus propios DJs asignados

---

## Crear una Nueva Terraza

### Requisitos Previos
- ✅ Tener rol de **Administrador General**
- ✅ Saber el nombre de tu terraza
- ✅ (Opcional) Tener idea de los horarios de operación

---

### Paso 1: Acceder a la Creación

1. Inicia sesión como **Administrador General**
2. En el menú lateral, ve a **Administración**
3. Haz clic en **Stations** (Estaciones)
4. Verás una lista de las terrazas existentes (si ya hay algunas)
5. Haz clic en el botón azul **"+ Add Station"**

---

### Paso 2: Configuración Básica (Tab: Profile)

Se abrirá un formulario con varias pestañas. Empezamos con **"Profile"** (Perfil).

#### 📝 **Station Name** (Nombre de la Estación) - OBLIGATORIO

Este es el nombre que verán todos los usuarios.

**Ejemplos buenos**:
- `Terraza Vista Mar`
- `Bar La Esquina`
- `Hotel Paradise - Lobby`
- `Café Central`

**❌ Evita nombres genéricos**: `Estacion 1`, `Test`, `Mi Radio`

---

#### 🔤 **Station Short Name** (Nombre Corto) - OBLIGATORIO

Un identificador único para URLs. Solo letras minúsculas, números y guiones.

**El sistema lo genera automáticamente**, pero puedes cambiarlo.

**Ejemplos**:
- Nombre: `Terraza Vista Mar` → Short Name: `terraza-vista-mar`
- Nombre: `Bar La Esquina` → Short Name: `bar-la-esquina`

**⚠️ IMPORTANTE**: 
- Una vez creado, NO se puede cambiar
- Será parte de la URL pública: `https://simonamusic.net/public/bar-la-esquina`

---

#### 📝 **Description** (Descripción) - Opcional

Una descripción de tu terraza. Aparecerá en la página pública.

**Ejemplo**:
```
Disfruta de la mejor música chill-out mientras tomas tu copa 
con vista al mar. Abierto de 10:00 a 02:00.
```

---

#### 🌍 **Time Zone** (Zona Horaria) - IMPORTANTE

La zona horaria donde está ubicada físicamente tu terraza.

**¿Por qué es importante?**
- Define cuándo se ejecutan las programaciones horarias
- Si programas música a las 20:00, será las 20:00 de ESTA zona horaria

**Para España**: Selecciona `Europe/Madrid`

**Para Latinoamérica**, busca tu ciudad o país:
- `America/Mexico_City` → Ciudad de México
- `America/Buenos_Aires` → Argentina
- `America/Bogota` → Colombia
- `America/Santiago` → Chile

---

#### 📻 **Enable Public Pages** (Habilitar Páginas Públicas)

**¿Qué hace esto?**
- ✅ Marcado: La gente puede ver la página pública de tu terraza
- ❌ Desmarcado: La terraza existe pero no tiene página pública

**Recomendación**: ✅ Siempre MARCADO (a menos que sea una terraza interna de prueba)

---

#### ✅ **Enable On-Demand Streaming** (Habilitar Descarga Bajo Demanda)

Permite a los oyentes descargar o acceder a música bajo demanda.

**Recomendación**: ❌ Normalmente DESMARCADO (por temas de derechos de autor)

---

### Paso 3: Configuración de Transmisión (Tab: Broadcasting)

Esta pestaña configura cómo sale la música de Simona Music.

#### 🎛️ **Backend Type** (Tipo de Backend)

**Recomendación**: Deja `Liquidsoap` (es el predeterminado y el mejor)

---

#### 🔊 **Broadcasting Service** (Servicio de Transmisión)

**Opciones**:
- `Icecast 2.4` → Recomendado para la mayoría
- `Shoutcast 2` → Si necesitas compatibilidad con Shoutcast

**Recomendación**: `Icecast 2.4`

---

#### 🎵 **Audio Streaming Bitrate** (Calidad de Audio)

Define la calidad del audio transmitido.

**Opciones y cuándo usarlas**:
- `128 kbps` → Calidad estándar, ahorra ancho de banda ✅ **Recomendado**
- `192 kbps` → Buena calidad
- `320 kbps` → Máxima calidad, consume más datos

**Consejo**: Empieza con 128 kbps. Si tienes buen internet y quieres mejor calidad, sube a 192 kbps.

---

#### 🎚️ **AutoDJ Crossfade Duration** (Duración de Fundido)

Cuántos segundos de cruce entre canciones.

**Valores típicos**:
- `2.0 segundos` → Cambio casi inmediato
- `3.0 segundos` → Transición suave ✅ **Recomendado**
- `5.0 segundos` → Transición muy gradual (estilo chill-out)

---

#### 👥 **Allow Streamers / DJs** (Permitir DJs en Vivo)

**¿Qué hace esto?**
- ✅ Marcado: Los DJs pueden transmitir en vivo
- ❌ Desmarcado: Solo música automática (AutoDJ)

**Recomendación**: 
- ✅ Márcalo si vas a tener DJs en vivo
- ❌ Desmárcalo si solo quieres música automática 24/7

---

### Paso 4: Frontend Configuration (Configuración de Reproducción)

Estas son configuraciones técnicas. **Usa los valores predeterminados** la primera vez.

**Lo importante aquí**:
- **Source Password**: Contraseña para que los DJs se conecten
  - Por defecto es generada automáticamente
  - Si la cambias, apúntala bien

**⚠️ Guarda esta contraseña** - Los DJs la necesitarán para transmitir

---

### Paso 5: Guardar y Finalizar

1. Revisa que todo esté correcto
2. Haz clic en **"Save Changes"** (Guardar Cambios)
3. Espera unos segundos mientras Simona Music configura todo
4. ¡Listo! Tu terraza está creada

**⏰ Nota**: Puede tardar 30-60 segundos en estar completamente activa.

---

## Configurar tu Terraza

Una vez creada tu terraza, debes configurar varios aspectos.

### Acceder a la Configuración

1. En el menú , busca tu terraza en la lista
2. Haz clic en ella
3. Verás el **Dashboard** de tu terraza

**Desde aquí puedes acceder a**:
- 📊 **Dashboard** → Resumen general
- ⚙️ **Profile** → Editar configuración básica
- 🎨 **Branding** → Personalizar colores y logo
- 📁 **Media** → Subir y organizar música
- 📋 **Playlists** → Crear listas de reproducción
- 📅 **Queue** → Ver cola de reproducción
- 🎙️ **Streamers** → Gestionar DJs
- 🎯 **Requests** → Peticiones de música
- 📈 **Reports** → Estadísticas

---

### Configuración Recomendada Post-Creación

#### 1. Personalizar Branding (Colores y Logo)

Ve a **Branding** (en el menú de tu terraza):

**Configura**:
- 🎨 **Color Primario**: El color principal de los botones
- 🎨 **Color Secundario**: Color de elementos secundarios
- 🎨 **Color de Fondo**: Fondo de la página pública
- 🖼️ **Logo**: URL de tu logo

**Ver Guía 08** para más detalles sobre personalización.

---

#### 2. Configurar Página Pública

Ve a **Profile → Edit Profile**:

**Marca estas opciones**:
- ✅ **Enable Public Pages** (Página pública)
- ✅ **Enable Song Requests** (Peticiones de música) - si quieres que tus clientes pidan canciones

---

#### 3. Configurar Horarios de Programación

En **Profile → Edit Profile**:

**Schedule Settings** (Configuración de Programación):
- Aquí defines los horarios generales de operación
- Las listas de reproducción programadas respetarán esta zona horaria

---

## Panel de Control de Terraza

Cuando entras a tu terraza, verás el **Dashboard**. Vamos a explicar cada sección:

### 🎵 Reproduciendo Ahora (Now Playing)

Muestra:
- 🎵 Canción actual
- 🎤 Artista
- ⏱️ Tiempo transcurrido / Total

---

### 📊 Estadísticas Rápidas

- **Song History**: Historial de canciones reproducidas hoy
- **Song Requests**: Peticiones pendientes

---

### 🎛️ Servicios

Muestra el estado de los servicios:
- ✅ **Backend (Liquidsoap)**: Debería estar en verde
- ✅ **Frontend (Icecast)**: Debería estar en verde

**Si ves rojo**, hay un problema técnico.

---

### 🔗 Enlaces Rápidos

- **Listen URL**: Enlace directo para escuchar tu radio
- **Public Page**: Página pública de tu terraza

---

## Gestionar Múltiples Terrazas

### Vista General de Terrazas

Si eres **Administrador General**, puedes ver todas las terrazas desde:

**Menú**: Ve a la sección principal que dice **"Terrazas"** o **"Dashboard"**

Verás:
- Una lista de todas las terrazas
- Estado de cada una (Online/Offline)
- Oyentes actuales
- Qué está sonando

---

### Cambiar entre Terrazas

**Método 1 - Menú **:
1. En el menú , haz clic en el nombre de la terraza
2. Se desplegará un submenú con todas sus opciones

**Método 2 - Vista de Terrazas**:
1. Ve a **Dashboard** o **Terrazas Control**
2. Haz clic en la terraza que quieres gestionar

---

### Copiar Configuración entre Terrazas

¿Creaste una terraza perfecta y quieres clonarla?

**Simona Music tiene función de clonado**:

1. Ve a **Administración → Stations**
2. Encuentra la terraza que quieres copiar
3. Haz clic en **"Clone"** (Clonar)
4. Dale un nuevo nombre
5. ¡Listo! Tendrás una copia exacta

**Se copia**:
- ✅ Configuración de transmisión
- ✅ Configuración de AutoDJ
- ✅ Listas de reproducción (referencias, no archivos)

**NO se copia**:
- ❌ Archivos de música (necesitas asignar storage)
- ❌ Historial de reproducción

---

## Eliminar o Desactivar Terrazas

### ⚠️ Desactivar Temporalmente

Si quieres que una terraza deje de funcionar temporalmente:

1. Ve a la terraza
2. **Profile → Edit Profile**
3. Desmarca **"Enable Public Pages"**
4. **Save Changes**

**Resultado**: 
- La terraza sigue existiendo
- Los usuarios no pueden acceder a la página pública
- No se transmite música

---

### 🗑️ Eliminar Permanentemente

**⚠️ CUIDADO**: Esto es IRREVERSIBLE

**¿Qué se borra?**
- ❌ La configuración de la terraza
- ❌ Las listas de reproducción
- ❌ El historial de reproducción
- ❌ Las estadísticas

**¿Qué NO se borra?**
- ✅ Los archivos de música (se quedan en el storage)
- ✅ Los usuarios

**Pasos para eliminar**:
1. Ve a **Administración → Stations**
2. Encuentra la terraza
3. Haz clic en **"Delete"** (Borrar) - ícono de basura roja
4. Confirma escribiendo el nombre de la terraza
5. Haz clic en **"Delete"**

**Recomendación**: En lugar de borrar, mejor desactívala. Siempre puedes reactivarla después.

---

## Ejemplos Prácticos

### Ejemplo 1: Cadena de 3 Terrazas

**Situación**: Tienes 3 bares en diferentes barrios

**Setup recomendado**:

```
Terraza 1: Bar Centro
- Short Name: bar-centro
- URL: simonamusic.net/public/bar-centro
- Horarios: 12:00 - 02:00

Terraza 2: Bar Playa
- Short Name: bar-playa
- URL: simonamusic.net/public/bar-playa
- Horarios: 10:00 - 01:00

Terraza 3: Bar Montaña
- Short Name: bar-montana
- URL: simonamusic.net/public/bar-montana
- Horarios: 09:00 - 23:00
```

**Cada una con**:
- Su propio logo y colores
- Sus propias listas de reproducción
- Sus propios DJs asignados

---

### Ejemplo 2: Hotel con Múltiples Áreas

**Situación**: Un hotel con 4 zonas de música

**Setup recomendado**:

```
Terraza 1: Hotel Paradise - Lobby
- Short Name: hotel-paradise-lobby
- Estilo: Música relajante
- Volumen: Ambiente suave

Terraza 2: Hotel Paradise - Pool
- Short Name: hotel-paradise-pool
- Estilo: Música tropical / chill-out
- Volumen: Medio

Terraza 3: Hotel Paradise - Bar
- Short Name: hotel-paradise-bar
- Estilo: Música comercial / hits
- Volumen: Alto

Terraza 4: Hotel Paradise - Restaurant
- Short Name: hotel-paradise-restaurant
- Estilo: Jazz / clásica
- Volumen: Ambiente suave
```

---

## Troubleshooting (Solución de Problemas)

### Problema: "Mi terraza no aparece en el menú"

**Soluciones**:
1. Verifica que tu usuario tenga permisos para esa terraza
2. Recarga la página (Ctrl + F5)
3. Cierra sesión y vuelve a entrar

---

### Problema: "Los servicios aparecen en rojo"

**Soluciones**:
1. Ve a **Profile → Actions**
2. Haz clic en **"Restart Broadcasting"**
3. Espera 30 segundos
4. Recarga la página

Si sigue en rojo:
1. Ve a **Logs** (en el menú de la terraza)
2. Busca mensajes de error
3. Contacta soporte técnico

---

### Problema: "No puedo cambiar el Short Name"

**Respuesta**: Los short names no se pueden cambiar una vez creados. Son identificadores permanentes.

**Solución**: Si realmente necesitas cambiarlo:
1. Crea una nueva terraza con el short name correcto
2. Clónala de la terraza original
3. Borra la terraza original

---

### Problema: "La música no suena"

**Checklist**:
- [ ] ¿Los servicios están en verde?
- [ ] ¿Hay al menos una lista de reproducción activa?
- [ ] ¿La lista tiene canciones?
- [ ] ¿La terraza está configurada para transmitir?
- [ ] ¿Hay algún DJ en vivo conectado? (si está habilitado, tiene prioridad)

---

## Buenas Prácticas

### ✅ Nomenclatura

**Nombres claros y descriptivos**:
- ✅ `Terraza Vista Mar`
- ✅ `Bar La Esquina - Zona VIP`
- ❌ `Radio 1`
- ❌ `Test`

---

### ✅ Organización

**Para muchas terrazas**, usa prefijos:
```
Hotel Paradise - Lobby
Hotel Paradise - Pool
Hotel Paradise - Bar
Hotel Paradise - Restaurant
```

Así quedan todas juntas en orden alfabético.

---

### ✅ Configuración Inicial

**Antes de hacer pública una terraza**:
1. [ ] Configura branding (colores y logo)
2. [ ] Sube al menos 50 canciones
3. [ ] Crea al menos 2 listas de reproducción
4. [ ] Prueba que se escuche bien
5. [ ] Configura peticiones de música
6. [ ] Genera el código QR

---

### ✅ Mantenimiento

**Cada semana**:
- Revisa las estadísticas
- Añade música nueva
- Revisa peticiones pendientes
- Verifica que los servicios estén activos

**Cada mes**:
- Revisa usuarios y permisos
- Actualiza las listas de reproducción
- Borra canciones poco populares

---

## Preguntas Frecuentes

### ❓ ¿Cuántas terrazas puedo crear?
**Respuesta**: Técnicamente ilimitadas, pero depende de los recursos del servidor. Una instalación típica puede manejar 10-50 terrazas sin problemas.

### ❓ ¿Pueden varias terrazas compartir la misma música?
**Respuesta**: Sí, todas las terrazas comparten el mismo storage de música por defecto.

### ❓ ¿Puede una canción sonar en varias terrazas a la vez?
**Respuesta**: Sí, sin problema. Cada terraza es independiente.

### ❓ ¿Puedo cambiar el nombre de una terraza después?
**Respuesta**: Sí, el "Station Name" se puede cambiar. El "Short Name" NO.

---

## 🎯 Checklist: Crear una Terraza Profesional

Usa esto cuando crees una nueva terraza:

### Configuración Básica
- [ ] Nombre descriptivo y profesional
- [ ] Short name único y claro
- [ ] Descripción atractiva
- [ ] Zona horaria correcta
- [ ] Páginas públicas habilitadas

### Transmisión
- [ ] Bitrate apropiado (128 o 192 kbps)
- [ ] Crossfade configurado (2-5 segundos)
- [ ] AutoDJ activo
- [ ] Decisión sobre DJs en vivo

### Contenido
- [ ] Mínimo 50-100 canciones subidas
- [ ] Al menos 2 listas de reproducción creadas
- [ ] Programación horaria configurada

### Personalización
- [ ] Logo configurado
- [ ] Colores personalizados
- [ ] CSS custom (si aplica)

### Funcionalidades
- [ ] Peticiones de música configuradas
- [ ] Código QR generado e impreso
- [ ] Permisos de usuarios asignados

### Testing
- [ ] Reproducción probada
- [ ] Página pública accesible
- [ ] Peticiones funcionando
- [ ] DJs pueden conectarse (si aplica)

---

¡Ya sabes todo sobre crear y gestionar terrazas en Simona Music! 🎉

**Siguiente**: Lee la **Guía 04** para aprender a subir música y crear listas de reproducción.
