# Guía Profesional DJ - Simona Music

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Requisitos Previos](#requisitos-previos)
3. [Credenciales de Acceso](#credenciales-de-acceso)
4. [Opción 1: WebDJ (Navegador)](#opción-1-webdj-navegador)
5. [Opción 2: Software Profesional](#opción-2-software-profesional)
   - [Traktor Pro](#configuración-traktor-pro)
   - [Serato DJ](#configuración-serato-dj)
   - [Mixxx](#configuración-mixxx)
6. [Solución de Problemas](#solución-de-problemas)
7. [Buenas Prácticas](#buenas-prácticas)
8. [Soporte Técnico](#soporte-técnico)

---

## Introducción

Bienvenido al sistema de transmisión en vivo de **Simona Music**. Este sistema te permite transmitir música en directo utilizando dos métodos:

1. **WebDJ**: Interfaz web integrada, ideal para sesiones rápidas desde cualquier dispositivo.
2. **Software Profesional**: Conexión directa desde Traktor, Serato, Mixxx u otro software DJ compatible con Icecast.

---

## Requisitos Previos

### Para WebDJ (Navegador):
- ✅ Navegador moderno: Chrome, Firefox, Edge o Safari (última versión)
- ✅ Conexión a internet estable (mínimo 2 Mbps upload)
- ✅ Micrófono (opcional, para anuncios)
- ✅ Archivos de música en formato MP3, WAV, FLAC, OGG

### Para Software Profesional:
- ✅ Software DJ instalado: Traktor Pro, Serato DJ, Mixxx, Virtual DJ, etc.
- ✅ Conexión a internet estable (mínimo 1 Mbps upload recomendado)
- ✅ Conocimiento básico del software DJ elegido

---

## Credenciales de Acceso

**🔐 Datos de Conexión Icecast:**

```
Servidor (Host):    simonamusic.net
Puerto (Port):      8000
Mount Point:        /live
Tipo de Fuente:     Icecast (Source)
Usuario (Username): source
Contraseña:         [Solicitar al administrador]
```

**🌐 WebDJ URL:**
```
https://simonamusic.net/public/simona/dj
```

> ⚠️ **Seguridad**: NUNCA compartas tu contraseña. Si sospechas que ha sido comprometida, contacta al administrador inmediatamente.

---

## Opción 1: WebDJ (Navegador)

### Acceso Rápido

1. **Abrir WebDJ:**
   - Navega a: `https://simonamusic.net/public/simona/dj`
   - O desde el Dashboard: **Live Streaming → Mesa Virtual**

2. **Interfaz Principal:**
   - **Panel de Configuración** (izquierda): Control de conexión y bitrate
   - **Micrófono**: Anuncios en vivo (opcional)
   - **Mezclador**: Control de volumen master
   - **Playlists 1 y 2**: Carga y reproduce tus tracks

### Guía Paso a Paso

#### Paso 1: Conectar
1. En el **Panel de Configuración**, verifica la URL del servidor
2. Selecciona el **Bitrate** deseado (128kbps recomendado, 320kbps para alta calidad)
3. Haz clic en **"Conectar"** o **"Start Broadcasting"**
4. Espera el mensaje de confirmación "Connected"

#### Paso 2: Cargar Música
1. En **Playlist 1**, haz clic en **"Add Track"** o arrastra archivos
2. Soporta: MP3, WAV, FLAC, OGG, AAC
3. Repite para **Playlist 2** si quieres dos decks

#### Paso 3: Reproducir
1. Haz clic en **Play** en cualquier track
2. Usa el **slider de volumen** para ajustar niveles
3. Usa el **crossfader** en el mezclador para transiciones

#### Paso 4: Micrófono (Opcional)
1. Haz clic en **"Enable Microphone"**
2. Permite el acceso al micrófono en el navegador
3. Ajusta el volumen del micrófono
4. Habla cuando sea necesario

#### Paso 5: Desconectar
1. Detén todos los tracks
2. Haz clic en **"Disconnect"** o **"Stop Broadcasting"**
3. Confirma el mensaje de desconexión

### Atajos de Teclado (WebDJ)

| Tecla | Acción |
|-------|--------|
| `Space` | Play/Pause track activo |
| `M` | Toggle micrófono |
| `←/→` | Navegar entre tracks |
| `↑/↓` | Ajustar volumen master |

---

## Opción 2: Software Profesional

### Configuración Traktor Pro

#### Requisitos:
- **Traktor Pro 3** o superior
- **Plugin Icecast**: Native Instruments (incluido en Traktor)

#### Pasos de Configuración:

1. **Abrir Traktor Pro**
   - Menú: `Preferences` → `Broadcasting`

2. **Configurar Icecast:**
   ```
   Encoder:         Icecast
   Address:         simonamusic.net
   Port:            8000
   Mount Point:     /live
   Username:        source
   Password:        [tu_contraseña]
   Bitrate:         320 kbps (o 128 kbps)
   Format:          MP3 o Ogg Vorbis
   ```

3. **Configurar Audio:**
   - `Preferences` → `Output Routing`
   - Asignar `Master` a tu interfaz de audio
   - Verificar `Broadcasting` está habilitado

4. **Iniciar Transmisión:**
   - En la interfaz principal, haz clic en el botón **"Broadcast"** (icono de ondas)
   - Espera el indicador verde de conexión
   - ¡Empieza a mezclar!

5. **Verificar Conexión:**
   - Abre `https://simonamusic.net/public/simona` en un navegador
   - Verifica que el stream esté reproduciendo tu audio

#### Solución de Problemas Traktor:

| Problema | Solución |
|----------|----------|
| **"Connection Failed"** | Verifica host, puerto y contraseña |
| **"Authentication Error"** | Revisa username y password |
| **Audio distorsionado** | Reduce bitrate a 192kbps o 128kbps |
| **Latencia alta** | Verifica conexión a internet, cierra apps de fondo |

---

### Configuración Serato DJ

#### Requisitos:
- **Serato DJ Pro** (licencia pagada)
- **Live Streaming Add-on** (puede requerir compra adicional)

#### Pasos de Configuración:

1. **Instalar Live Streaming Add-on:**
   - Descargar desde Serato.com → My Account → Expansions
   - Instalar y reiniciar Serato DJ

2. **Abrir Serato DJ Pro:**
   - Ir a `Setup` → `Live Streaming`

3. **Configurar Icecast:**
   ```
   Platform:        Custom RTMP/Icecast
   Type:            Icecast
   Server:          simonamusic.net
   Port:            8000
   Mount Point:     /live
   Username:        source
   Password:        [tu_contraseña]
   Bitrate:         320 kbps
   ```

4. **Configurar Audio:**
   - `Setup` → `Audio`
   - Asignar `Master Output` correctamente
   - Verificar niveles en VU meter

5. **Iniciar Transmisión:**
   - En la ventana `Live Streaming`, clic en **"Go Live"**
   - Espera el indicador "Live" en verde
   - Verifica en `https://simonamusic.net/public/simona`

#### Solución de Problemas Serato:

| Problema | Solución |
|----------|----------|
| **Add-on no aparece** | Verifica que tu licencia de Serato DJ Pro esté activa |
| **"Server Unreachable"** | Verifica firewall, antivirus, puerto 8000 abierto |
| **Calidad de audio baja** | Aumenta bitrate a 256kbps o 320kbps |
| **Desconexiones frecuentes** | Revisa estabilidad de internet, usa cable Ethernet |

---

### Configuración Mixxx

#### Requisitos:
- **Mixxx 2.3** o superior (gratuito y open source)
- **Icecast broadcasting** (incluido nativamente)

#### Pasos de Configuración:

1. **Descargar Mixxx:**
   - Sitio oficial: https://mixxx.org/download/
   - Instalar versión estable más reciente

2. **Abrir Mixxx:**
   - Ir a `Preferences` → `Live Broadcasting`

3. **Crear Nueva Conexión:**
   - Clic en **"Create new connection"**
   - Tipo: **Icecast 2**

4. **Configurar Icecast:**
   ```
   Type:            Icecast 2
   Host:            simonamusic.net
   Port:            8000
   Mount Point:     /live
   Username:        source
   Password:        [tu_contraseña]
   Stream Name:     Simona Music Live
   Description:     DJ Session
   Format:          MP3
   Bitrate:         320 kbps
   Channels:        Stereo
   ```

5. **Configurar Audio:**
   - `Preferences` → `Sound Hardware`
   - `Master output`: Tu interfaz de audio
   - Sample Rate: 44100 Hz o 48000 Hz

6. **Iniciar Transmisión:**
   - En la interfaz principal, botón **"Broadcast"** (icono micrófono)
   - Espera el mensaje "Connected to Icecast server"
   - ¡Mezcla tus tracks!

7. **Detener Transmisión:**
   - Clic nuevamente en el botón **"Broadcast"**
   - Confirma la desconexión

#### Ventajas de Mixxx:
- ✅ **Gratuito y Open Source**
- ✅ **Soporte nativo Icecast** (sin plugins adicionales)
- ✅ **Compatible con múltiples controladores DJ**
- ✅ **Análisis automático de BPM y key**

#### Solución de Problemas Mixxx:

| Problema | Solución |
|----------|----------|
| **"Failed to connect"** | Verifica host, puerto, mount point (incluir `/`) |
| **Audio no se escucha** | Revisa `Master output` en Sound Hardware |
| **CPU alta** | Reduce calidad visual, cierra apps de fondo |
| **Latencia en mezclas** | Ajusta `Buffer Size` en Sound Hardware (2048 samples) |

---

## Solución de Problemas Comunes

### 1. No puedo conectarme al servidor

**Síntomas:**
- Error "Connection timeout"
- "Server unreachable"
- "Authentication failed"

**Soluciones:**
1. ✅ **Verifica credenciales**: Host, puerto, username, password correctos
2. ✅ **Prueba en navegador**: Abre `https://simonamusic.net` para verificar que el servidor está online
3. ✅ **Firewall/Antivirus**: Permite conexiones salientes al puerto 8000
4. ✅ **VPN**: Si usas VPN, desactívala temporalmente
5. ✅ **Internet**: Verifica que tu conexión esté estable (ping a simonamusic.net)

### 2. Audio con cortes o distorsión

**Síntomas:**
- Audio se corta cada pocos segundos
- Calidad degradada
- Sonido "robótico"

**Soluciones:**
1. ✅ **Reduce bitrate**: Prueba 192kbps o 128kbps en lugar de 320kbps
2. ✅ **Cierra apps**: Cierra navegadores, videos, descargas
3. ✅ **Cable Ethernet**: Usa cable en lugar de WiFi
4. ✅ **Verifica upload**: Tu conexión debe tener mínimo 1 Mbps de upload para 128kbps
5. ✅ **Buffer size**: Aumenta el buffer en la configuración de audio de tu software

### 3. No se escucha mi transmisión

**Síntomas:**
- El software dice "Connected" pero el público no escucha nada
- Stream online pero sin audio

**Soluciones:**
1. ✅ **Verifica mount point**: Debe ser `/live` (con slash inicial)
2. ✅ **Prueba tú mismo**: Abre `https://simonamusic.net/public/simona` en otro dispositivo
3. ✅ **Master volume**: Verifica que el volumen master no esté en 0
4. ✅ **Audio routing**: En tu software, asegúrate de que `Master` esté asignado correctamente
5. ✅ **Reinicia transmisión**: Desconecta y vuelve a conectar

### 4. Desconexiones frecuentes

**Síntomas:**
- Se desconecta cada 5-10 minutos
- "Connection lost" aleatorio

**Soluciones:**
1. ✅ **Internet estable**: Usa cable Ethernet, no WiFi
2. ✅ **Cierra descargas**: Torrents, actualizaciones, backups en la nube
3. ✅ **Router**: Reinicia tu router antes de la sesión
4. ✅ **Puerto dedicado**: Configura port forwarding del puerto 8000 si es posible
5. ✅ **Antivirus**: Agrega excepciones para tu software DJ

---

## Buenas Prácticas

### Antes de la Sesión:

1. ✅ **Prueba 15 minutos antes**: Conecta, prueba audio, verifica niveles
2. ✅ **Playlist preparada**: Ten tus tracks organizados y analizados
3. ✅ **Backup de internet**: Si es posible, ten un hotspot móvil como respaldo
4. ✅ **Headphones**: Usa auriculares para monitorear el stream
5. ✅ **Notifica**: Avisa al administrador que vas a transmitir

### Durante la Sesión:

1. ✅ **Monitorea niveles**: No dejes que el master peak (rojo)
2. ✅ **Transiciones suaves**: Usa EQ y crossfader para mezclas profesionales
3. ✅ **Verifica stream**: Cada 15-20 min, revisa que el público está escuchando
4. ✅ **Comunicación**: Si hay problemas, contacta al administrador
5. ✅ **No desconectes abruptamente**: Baja volumen gradualmente antes de desconectar

### Después de la Sesión:

1. ✅ **Desconecta limpiamente**: Stop broadcasting correctamente
2. ✅ **Reporta problemas**: Si hubo issues, informa al admin
3. ✅ **Agradece al público**: Post en redes sociales
4. ✅ **Backup**: Guarda tu sesión si la grabaste localmente

---

## Calidad de Audio Recomendada

| Bitrate | Calidad | Uso Recomendado | Bandwidth (Upload) |
|---------|---------|-----------------|-------------------|
| **128 kbps** | Buena | Conexiones limitadas | 0.5 Mbps |
| **192 kbps** | Muy Buena | Balance calidad/ancho de banda | 0.8 Mbps |
| **256 kbps** | Excelente | Audiophiles, eventos especiales | 1 Mbps |
| **320 kbps** | Máxima | Producción profesional | 1.3 Mbps |

> 💡 **Recomendación**: Usa 192kbps para un balance óptimo entre calidad y estabilidad.

---

## Soporte Técnico

### Contacto:

- **Email**: soporte@simonamusic.net
- **WhatsApp**: +34 XXX XXX XXX
- **Horario**: Lunes a Viernes, 10:00 - 18:00 (CET)

### Información para Soporte:

Cuando contactes al soporte, incluye:

1. **Software usado**: Traktor, Serato, Mixxx, WebDJ
2. **Versión**: Ej. "Traktor Pro 3.8.0"
3. **Sistema operativo**: Windows 10/11, macOS, Linux
4. **Descripción del problema**: Lo más detallado posible
5. **Capturas de pantalla**: Errores, configuración, etc.
6. **Velocidad de internet**: Resultado de speedtest.net

---

## Changelog

- **v1.0** (2024-01-15): Guía inicial creada
- Sistema WebDJ integrado
- Soporte Icecast para software profesional
- Documentación completa Traktor, Serato, Mixxx

---

## Licencia

© 2024 Simona Music. Todos los derechos reservados.

Este documento es confidencial y está destinado únicamente a los DJs autorizados de Simona Music.

---

**🎧 ¡Disfruta tu sesión y haz bailar a la gente! 🎶**
