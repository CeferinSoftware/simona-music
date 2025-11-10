# Plan de Integración: Mesa de DJ Virtual

**Fecha:** 10 de Noviembre 2025  
**Proyecto:** Simona Music (AzuraCast Fork)  
**Estado:** ✅ SISTEMA WEBDJ YA IMPLEMENTADO  
**Autor:** Análisis Técnico - Integración DJ

---

## 📋 Resumen Ejecutivo

**DESCUBRIMIENTO CRÍTICO:** AzuraCast (y por tanto Simona Music) **YA TIENE UN SISTEMA COMPLETO DE DJ VIRTUAL** llamado **WebDJ** que permite transmitir en vivo desde el navegador sin necesidad de software externo.

### ✅ Lo que ya existe:
- **WebDJ completo**: Interfaz de mesa de mezclas en el navegador
- **2 Playlists virtuales**: Con controles play/pause/stop/next/previous
- **Mixer de 2 canales**: Crossfade entre playlists
- **Entrada de micrófono**: Con controles de volumen y "cue"
- **Streaming en vivo**: Transmisión directa vía WebSocket a Liquidsoap
- **Autenticación**: Login con username/password de streamer
- **Metadata en vivo**: Actualización de título/artista durante transmisión
- **Bitrate configurable**: 128-320 kbps
- **Sistema de colas**: Request queue e interrupting queue

### 🎯 Objetivo del Documento

Este documento explica:
1. **Cómo funciona** el sistema WebDJ existente
2. **Cómo activarlo** y configurarlo
3. **Cómo integrarlo** con la interfaz de Terraza/DJ
4. **Opciones de expansión** si se necesita funcionalidad adicional

---

## 🏗️ Arquitectura Actual del Sistema WebDJ

### 1. Stack Tecnológico

```
┌─────────────────────────────────────────────────────────────┐
│                    NAVEGADOR (Cliente)                      │
├─────────────────────────────────────────────────────────────┤
│  • Vue 3 Components (WebDJ.vue)                            │
│  • Web Audio API (AudioContext, ScriptProcessor)          │
│  • MediaRecorder API (audio/webm codec opus)              │
│  • getUserMedia API (micrófono)                           │
│  • WebSocket (protocolo "webcast")                        │
└────────────────────┬────────────────────────────────────────┘
                     │ WSS (WebSocket Secure)
                     │ Protocol: "webcast"
                     │ Format: audio/webm;codecs=opus
                     ▼
┌─────────────────────────────────────────────────────────────┐
│              SERVIDOR (Backend PHP + Liquidsoap)            │
├─────────────────────────────────────────────────────────────┤
│  • PHP Backend:                                            │
│    - WebDjAction.php (controller)                         │
│    - Genera WSS URL para conexión                         │
│    - Autenticación de streamers                           │
│                                                            │
│  • Liquidsoap (AutoDJ):                                   │
│    - input.harbor() - Recibe stream WebSocket            │
│    - Buffer configurable (5-10 segundos)                 │
│    - Autenticación via azuracast.dj_auth                 │
│    - On_connect / On_disconnect handlers                 │
│    - Fallback a AutoDJ cuando DJ desconecta              │
│                                                            │
│  • Nginx:                                                  │
│    - Proxy WebSocket WSS                                  │
│    - Terminación SSL/TLS                                  │
└─────────────────────────────────────────────────────────────┘
```

### 2. Componentes Frontend

```
frontend/components/Public/WebDJ/
├── WebDJ.vue                    # Componente principal
├── SettingsPanel.vue            # Conexión, credenciales, bitrate
├── MicrophonePanel.vue          # Control de micrófono
├── MixerPanel.vue               # Mixer 2 canales
├── PlaylistPanel.vue            # Playlist con play/pause/stop/next
├── VolumeSlider.vue             # Control de volumen
├── useWebcaster.ts              # WebSocket + MediaRecorder
├── useWebDjNode.ts              # AudioContext + audio processing
├── useWebDjSource.ts            # getUserMedia + audio files
├── useWebDjTrack.ts             # Track control + gain nodes
├── useMixerValue.ts             # Mixer state
└── usePassthroughSync.ts        # Cue (preview) sync
```

### 3. Componentes Backend

```
backend/
├── src/
│   ├── Controller/Frontend/PublicPages/
│   │   └── WebDjAction.php              # Controller HTTP
│   ├── Radio/Backend/
│   │   ├── Liquidsoap.php               # Adaptador Liquidsoap
│   │   └── Liquidsoap/
│   │       ├── ConfigWriter.php         # Genera config Liquidsoap
│   │       ├── Command/
│   │       │   ├── DjAuthCommand.php    # Autenticación DJ
│   │       │   ├── DjOnCommand.php      # Handler conexión
│   │       │   └── DjOffCommand.php     # Handler desconexión
│   └── Entity/
│       ├── StationStreamer.php          # Entidad streamer/DJ
│       └── StationStreamerBroadcast.php # Historial transmisiones
└── templates/
    └── frontend/public/
        └── webdj.latte                  # Template página WebDJ
```

---

## 🔧 Configuración Técnica

### Requisitos del Sistema

#### Backend (Servidor):
- **Liquidsoap** 2.x con soporte `input.harbor()`
- **Puerto DJ**: Por defecto `frontend_port + 5` (ej: 8005)
- **Puerto Telnet**: Por defecto `dj_port - 1` (ej: 8004)
- **WebSocket**: WSS (obligatorio HTTPS para `getUserMedia`)
- **Codecs**: Opus en container WebM

#### Frontend (Cliente):
- **Navegador**: Firefox (recomendado), Chrome/Edge
- **Conexión**: HTTPS obligatorio (getUserMedia requirement)
- **Permisos**: Acceso a micrófono
- **Compatibilidad**: 
  - MediaRecorder API ✅
  - Web Audio API ✅
  - WebSocket ✅
  - getUserMedia ✅

### Configuración por Estación

En la base de datos `station` table:

```sql
-- Habilitar streamers/DJs
enable_streamers = 1

-- Habilitar página pública (requerido para WebDJ)
enable_public_page = 1
```

En `station_backend_configuration`:

```sql
-- Puerto para DJs (opcional, usa default si NULL)
dj_port = 8005

-- Mount point para DJ (default: '/')
dj_mount_point = '/'

-- Buffer en segundos (5-10 recomendado)
dj_buffer = 5

-- Grabar transmisiones en vivo
record_streams = 1
record_streams_format = 'mp3'
record_streams_bitrate = 128
```

### Creación de Streamer/DJ

```sql
INSERT INTO station_streamer (
    station_id,
    streamer_username,
    streamer_password,
    display_name,
    comments,
    is_active,
    enforce_schedule
) VALUES (
    1,                                  -- ID de la estación
    'dj_carlos',                        -- Username
    '$2y$10$...',                       -- Password hasheado (bcrypt)
    'DJ Carlos',                        -- Nombre visible
    'DJ principal turnos tarde',       -- Comentarios
    1,                                  -- Activo
    0                                   -- No forzar horario (0=siempre puede conectar)
);
```

---

## 🚀 Flujo de Conexión WebDJ

### 1. Inicialización

```javascript
// frontend/components/Public/WebDJ.vue

// Proveedor del webcaster (WebSocket + MediaRecorder)
const webcaster = useProvideWebcaster({
    baseUri: 'wss://simonamusic.net/public/dj/estacion1/'  // Props desde backend
});

// Proveedor del nodo de audio Web Audio API
useProvideWebDjNode(webcaster);

// Proveedor del mixer (state compartido)
useProvideMixer(1.0);  // Valor inicial centrado
```

### 2. Autenticación y Conexión

```typescript
// frontend/components/Public/WebDJ/useWebDjNode.ts

const startStream = (username: string | null = null, password: string | null = null) => {
    // 1. Resumir AudioContext (requerido por navegadores)
    await context.value.resume();

    // 2. Crear MediaRecorder con stream desde AudioContext
    mediaRecorder = new MediaRecorder(
        streamNode.value.stream,
        {
            mimeType: "audio/webm;codecs=opus",
            audioBitsPerSecond: bitrate.value * 1000  // 128000-320000
        }
    );

    // 3. Conectar WebSocket con autenticación
    connectSocket(mediaRecorder, username, password);

    // 4. Iniciar grabación en chunks de 1 segundo
    mediaRecorder.start(1000);
}
```

### 3. Protocolo WebSocket

```typescript
// frontend/components/Public/WebDJ/useWebcaster.ts

// Mensaje HELLO (autenticación)
socket.send(JSON.stringify({
    type: "hello",
    data: {
        mime: "audio/webm;codecs=opus",
        user: "dj_carlos",           // Username streamer
        password: "password123"       // Password streamer
    }
}));

// Mensaje METADATA (actualización canción)
socket.send(JSON.stringify({
    type: "metadata",
    data: {
        title: "Despacito",
        artist: "Luis Fonsi"
    }
}));

// Mensaje DATA (audio chunks)
mediaRecorder.ondataavailable = async (e: BlobEvent) => {
    const data = await e.data.arrayBuffer();
    if (isConnected.value) {
        socket.send(data);  // Binary data, no JSON
    }
};
```

### 4. Procesamiento en Liquidsoap

```liquidsoap
# backend/src/Radio/Backend/Liquidsoap/ConfigWriter.php genera:

# Configuración harbor (receptor WebSocket)
live = input.harbor(
    "/",                           # Mount point
    id = "input_streamer",
    port = 8005,                   # Puerto DJ
    auth = azuracast.dj_auth,      # Función autenticación
    icy = true,                    # Soporte ICY metadata
    icy_metadata_charset = "UTF-8",
    metadata_charset = "UTF-8",
    buffer = 5.0,                  # Buffer 5 segundos
    max = 10.0                     # Máximo 10 segundos
)

# Handlers de eventos
live.on_connect(synchronous=false, azuracast.live_connected)
live.on_disconnect(synchronous=false, azuracast.live_disconnected)

# Insertar metadata si falta
def insert_missing(m) =
    if m == [] then
        [("title", "#{settings.azuracast.live_broadcast_text()}"), ("is_live", "true")]
    else
        [("is_live", "true")]
    end
end
live = metadata.map(insert_missing, live)

# Fallback: Live DJ -> AutoDJ
radio = fallback(
    id="live_fallback",
    track_sensitive=true,
    replay_metadata=true,
    [live, radio]  # Si live no está ready, usa radio (AutoDJ)
)
```

### 5. Autenticación Backend

```php
// backend/src/Radio/Backend/Liquidsoap/Command/DjAuthCommand.php

protected function doRun(Station $station, bool $asAutoDj = false, array $payload = []): array
{
    if (!$station->enable_streamers) {
        throw new RuntimeException('Streamers are disabled on this station.');
    }

    [$user, $pass] = $this->getCredentials($payload);

    // Opción 1: Password source general
    if ('source' === $user) {
        $sourcePw = $station->frontend_config->source_pw;
        if ($pass === $sourcePw) {
            return ['auth' => true, 'user' => 'source'];
        }
    }

    // Opción 2: Streamer específico
    $streamer = $this->streamerRepo->findByUsername($station, $user);
    if ($streamer && $streamer->authenticate($pass)) {
        return [
            'auth' => true,
            'user' => $user,
            'display_name' => $streamer->display_name
        ];
    }

    return ['auth' => false];
}
```

---

## 📡 Web Audio API - Arquitectura Interna

### Grafo de Audio Completo

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│ MICRÓFONO    │       │ PLAYLIST 1   │       │ PLAYLIST 2   │
│ getUserMedia │       │ File Input   │       │ File Input   │
└──────┬───────┘       └──────┬───────┘       └──────┬───────┘
       │                      │                      │
       │                      │                      │
   ┌───▼──────┐          ┌───▼──────┐          ┌───▼──────┐
   │ Media    │          │ Media    │          │ Media    │
   │ Stream   │          │ Element  │          │ Element  │
   │ Source   │          │ Source   │          │ Source   │
   └───┬──────┘          └───┬──────┘          └───┬──────┘
       │                      │                      │
   ┌───▼──────┐          ┌───▼──────┐          ┌───▼──────┐
   │ Gain     │          │ Gain     │          │ Gain     │
   │ Node     │          │ Node     │          │ Node     │
   │ (Volume) │          │ (Volume) │          │ (Volume) │
   └───┬──────┘          └─────┬────┘          └─────┬────┘
       │                       │                      │
       │                       └──────┬───────────────┘
       │                              │
       │                         ┌────▼────┐
       │                         │ MIXER   │
       │                         │ (Gain)  │
       │                         └────┬────┘
       │                              │
       └──────────────┬───────────────┘
                      │
                 ┌────▼────────┐
                 │ Script      │
                 │ Processor   │ ◄─── VU Meters, Effects
                 │ (Controls)  │
                 └────┬────────┘
                      │
            ┌─────────┴─────────┐
            │                   │
       ┌────▼────┐         ┌────▼────────┐
       │ Media   │         │ Script      │
       │ Stream  │         │ Processor   │
       │ Dest.   │         │ (Passthru)  │ ◄─── Cue/Preview
       │ (Sink)  │         └─────┬───────┘
       └────┬────┘               │
            │              ┌─────▼──────┐
            │              │ Audio      │
            │              │ Context    │
            │              │ .dest      │ ◄─── Monitors locales
            │              └────────────┘
            │
     ┌──────▼──────┐
     │ Media       │
     │ Recorder    │  ◄─── Codec: audio/webm;codecs=opus
     │ API         │       Bitrate: 128-320 kbps
     └──────┬──────┘
            │
     ┌──────▼──────┐
     │ WebSocket   │  ◄─── Protocol: "webcast"
     │ WSS         │       Chunks cada 1000ms
     └──────┬──────┘
            │
            ▼
     [LIQUIDSOAP]
```

### Código de Procesamiento de Audio

```typescript
// frontend/components/Public/WebDJ/useWebDjNode.ts

// Crear sink (destino final para streaming)
const sink = computed(() => {
    const currentContext = context.value;
    
    // ScriptProcessor para procesar audio en tiempo real
    const sink = currentContext.createScriptProcessor(
        bufferSize.value,       // 256 samples
        channelCount.value,     // 2 canales (stereo)
        channelCount.value
    );

    // Procesar cada frame de audio
    sink.onaudioprocess = (buf) => {
        for (let channel = 0; channel < buf.inputBuffer.numberOfChannels; channel++) {
            const channelData = buf.inputBuffer.getChannelData(channel);
            
            // Aplicar mixer de volumen
            const mixer = mixerValue.value;  // 0.0 - 2.0
            
            // Output = Input con ganancia aplicada
            buf.outputBuffer.getChannelData(channel).set(channelData);
        }
    };

    return sink;
});

// Crear nodo de streaming (para MediaRecorder)
const streamNode = computed(() => {
    const streamNode = context.value.createMediaStreamDestination();
    streamNode.channelCount = channelCount.value;
    sink.value.connect(streamNode);
    return streamNode;
});
```

### Control de Ganancia por Track

```typescript
// frontend/components/Public/WebDJ/useWebDjTrack.ts

const prepare = () => {
    // Nodo de controles (VU meter, processing)
    controlsNode = createControlsNode();
    controlsNode.connect(sink.value);

    // Nodo de ganancia (volumen del track)
    trackGainNode = context.value.createGain();
    trackGainNode.gain.value = Number(trackGain.value) / 100.0;  // 0-100% -> 0.0-1.0
    trackGainNode.connect(controlsNode);

    // Nodo de passthrough (cue/preview)
    passThroughNode = createPassThrough();
    passThroughNode.connect(context.value.destination);  // Audio local
    trackGainNode.connect(passThroughNode);

    await context.value.resume();

    return trackGainNode;  // Return para conectar source
}

// Función para crear source desde archivo
const createAudioSource = (pointer: WebDjFilePointer, cb) => {
    const el = new Audio(URL.createObjectURL(pointer.file));
    el.controls = false;
    el.autoplay = false;

    el.addEventListener("canplay", () => {
        source = context.value.createMediaElementSource(el);
        
        // Wrapper con métodos de control
        source.play = () => el.play()
        source.position = () => el.currentTime;
        source.duration = () => el.duration;
        source.paused = () => el.paused;
        source.stop = () => {
            el.pause();
            return el.remove();
        };
        source.pause = () => el.pause();
        source.seek = (percent) => {
            const time = percent * parseFloat(pointer.audio.length);
            el.currentTime = time;
            return time;
        };

        cb(source);  // Callback con source listo
    });
};
```

---

## 🎛️ Funcionalidades Implementadas

### 1. Mixer de 2 Canales

```vue
<!-- frontend/components/Public/WebDJ/MixerPanel.vue -->
<template>
  <div class="mixer card">
    <div class="card-header">
      <h5>Mixer</h5>
      <div class="d-flex">
        <div>Playlist 1</div>
        <input
          v-model.number="mixer"
          type="range"
          min="0"
          max="2"
          step="0.05"
          @click.right.prevent="mixer = 1.0"  <!-- Reset al centro -->
        >
        <div>Playlist 2</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {useInjectMixer} from "~/components/Public/WebDJ/useMixerValue";

const {mixer} = useInjectMixer();  // State compartido reactivo
</script>
```

**Funcionalidad:**
- Valor 0.0 = 100% Playlist 1, 0% Playlist 2
- Valor 1.0 = 50% Playlist 1, 50% Playlist 2 (centro)
- Valor 2.0 = 0% Playlist 1, 100% Playlist 2
- Click derecho = Reset al centro

### 2. Control de Playlist

```vue
<!-- frontend/components/Public/WebDJ/PlaylistPanel.vue -->

Funcionalidades:
✅ Play / Pause / Stop
✅ Next track / Previous track
✅ Seek bar (scrubbing)
✅ VU meter (visualización nivel audio)
✅ Volume slider (0-100%)
✅ Cue button (preview en auriculares)
✅ File input (cargar archivos locales)
✅ Continuous play (loop automático)
✅ Loop single track
✅ Display: current time / total duration
✅ Display: nombre archivo actual
```

### 3. Control de Micrófono

```typescript
// frontend/components/Public/WebDJ/MicrophonePanel.vue

Funcionalidades:
✅ Enable/Disable micrófono (botón on/off)
✅ Selector de dispositivo de entrada
✅ Volume slider (0-100%)
✅ Cue button (preview en auriculares)
✅ VU meter en tiempo real
✅ getUserMedia con permisos del navegador
✅ Detección automática de dispositivos disponibles
```

### 4. Panel de Configuración

```vue
<!-- frontend/components/Public/WebDJ/SettingsPanel.vue -->

Funcionalidades cuando DESCONECTADO:
✅ Input username DJ
✅ Input password DJ
✅ Selector sample rate (22050/44100/48000 Hz)
✅ Selector bitrate (64/96/128/192/256/320 kbps)
✅ Botón "Start Streaming"
✅ Botón "Cue" global (preview todo)

Funcionalidades cuando CONECTADO:
✅ Input "Title" (metadata)
✅ Input "Artist" (metadata)
✅ Botón "Update Metadata"
✅ Botón "Stop Streaming"
✅ Indicador conexión exitosa (toast notification)
```

---

## 🔐 Seguridad y Autenticación

### 1. Flujo de Autenticación

```
1. Usuario ingresa username/password en WebDJ
2. Frontend inicia WebSocket WSS con credenciales
3. WebSocket envía mensaje "hello" con user/pass
4. Liquidsoap llama función `azuracast.dj_auth`
5. Backend PHP (DjAuthCommand) valida credenciales:
   a. Busca StationStreamer por username
   b. Verifica password con bcrypt
   c. Verifica is_active = 1
   d. Verifica enforce_schedule (si aplica)
6. Liquidsoap recibe respuesta auth=true/false
7. Si true: acepta conexión, dispara on_connect
8. Si false: rechaza conexión, cierra WebSocket
```

### 2. Validaciones de Seguridad

```php
// backend/src/Entity/StationStreamer.php

public function authenticate(string $password): bool
{
    // Solo streamers activos pueden autenticar
    if (!$this->is_active) {
        return false;
    }

    // Verificar schedule si está activado
    if ($this->enforce_schedule) {
        $now = CarbonImmutable::now($this->station->timezone);
        $currentTimestamp = $now->getTimestamp();
        
        foreach ($this->schedule_items as $scheduleItem) {
            if ($scheduleItem->isScheduledToPlayNow($station, $now)) {
                // Dentro de horario permitido
                goto check_password;
            }
        }
        
        // Fuera de horario
        return false;
    }

    check_password:
    // Verificar password con bcrypt
    return password_verify($password, $this->streamer_password);
}
```

### 3. Requisitos HTTPS

**CRÍTICO:** `getUserMedia()` API **SOLO funciona en contextos seguros:**

```javascript
// Navegadores modernos bloquean getUserMedia en HTTP
// REQUIERE: https://simonamusic.net (no http://)

navigator.mediaDevices.getUserMedia({audio: true})
    .then(stream => {
        // ✅ Funciona en HTTPS
    })
    .catch(error => {
        // ❌ Error en HTTP: "NotAllowedError"
    });
```

**Solución:**
- Nginx con SSL/TLS (Let's Encrypt)
- WebSocket WSS (no WS)
- Dominio válido con certificado

---

## 🎨 Integración con Dashboard DJ/Terraza

### Opción 1: Enlace Directo (Implementado)

**URL actual:** `https://simonamusic.net/public/{station_short_name}/dj`

```php
// backend/src/Controller/Frontend/PublicPages/WebDjAction.php

public function __invoke(ServerRequest $request, Response $response, array $params): ResponseInterface
{
    $station = $request->getStation();

    // Verificar que página pública está habilitada
    if (!$station->enable_public_page) {
        throw NotFoundException::station();
    }

    // Verificar que streamers están habilitados
    StationFeatures::Streamers->assertSupportedForStation($station);

    // Obtener URL WebSocket
    $backend = $this->adapters->requireBackendAdapter($station);
    $wssUrl = (string)$backend->getWebStreamingUrl($station, $request->getRouter()->getBaseUrl());

    // Renderizar Vue component
    return $view->renderVuePage(
        response: $response,
        component: 'Public/WebDJ',
        id: 'webdj',
        layout: 'minimal',
        title: __('Web DJ') . ' - ' . $station->name,
        props: [
            'baseUri' => $wssUrl,              // WSS URL
            'stationName' => $station->name,
        ],
    );
}
```

**Implementación simple:**

```vue
<!-- En componente del dashboard DJ -->
<template>
  <div>
    <h2>Mesa de DJ Virtual</h2>
    <a
      :href="`/public/${stationShortName}/dj`"
      target="_blank"
      class="btn btn-primary"
    >
      Abrir WebDJ
    </a>
  </div>
</template>
```

### Opción 2: Iframe Embebido

```vue
<!-- frontend/components/Stations/DJ/WebDjPanel.vue -->
<template>
  <div class="card">
    <div class="card-header">
      <h3>Mesa de DJ Virtual</h3>
    </div>
    <div class="card-body" style="padding: 0;">
      <iframe
        :src="`/public/${stationShortName}/dj`"
        style="width: 100%; height: 800px; border: none;"
        allow="microphone"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  stationShortName: string
}>();
</script>
```

**Ventajas:**
- Integración visual en dashboard
- No requiere nueva ventana
- Mantiene contexto de navegación

**Desventajas:**
- Requiere `allow="microphone"` en iframe
- Mayor consumo de memoria
- Posibles problemas de focus para getUserMedia

### Opción 3: Componente Vue Reutilizable

```vue
<!-- frontend/components/Stations/DJ/DJConsole.vue -->
<template>
  <div class="dj-console">
    <!-- Importar componentes WebDJ existentes -->
    <settings-panel :station-name="stationName" />
    <microphone-panel />
    <mixer-panel />
    <div class="row">
      <playlist-panel id="playlist_1" class="col-6" />
      <playlist-panel id="playlist_2" class="col-6" />
    </div>
  </div>
</template>

<script setup lang="ts">
import SettingsPanel from "~/components/Public/WebDJ/SettingsPanel.vue";
import MicrophonePanel from "~/components/Public/WebDJ/MicrophonePanel.vue";
import MixerPanel from "~/components/Public/WebDJ/MixerPanel.vue";
import PlaylistPanel from "~/components/Public/WebDJ/PlaylistPanel.vue";
import {useProvideWebDjNode} from "~/components/Public/WebDJ/useWebDjNode";
import {useProvideWebcaster} from "~/components/Public/WebDJ/useWebcaster";
import {useProvideMixer} from "~/components/Public/WebDJ/useMixerValue";
import {useProvidePassthroughSync} from "~/components/Public/WebDJ/usePassthroughSync";

const props = defineProps<{
  baseUri: string,
  stationName: string
}>();

// Inicializar providers
const webcaster = useProvideWebcaster(props);
useProvideWebDjNode(webcaster);
useProvideMixer(1.0);
useProvidePassthroughSync('');
</script>
```

**Uso en dashboard:**

```vue
<!-- frontend/js/pages/Stations/DJ.vue -->
<template>
  <div>
    <h1>Dashboard DJ</h1>
    
    <!-- Sección WebDJ -->
    <dj-console
      :base-uri="djWebSocketUrl"
      :station-name="station.name"
    />
  </div>
</template>

<script setup lang="ts">
import DJConsole from "~/components/Stations/DJ/DJConsole.vue";

const props = defineProps<{
  station: Station,
  djWebSocketUrl: string
}>();
</script>
```

---

## 📊 Expansión y Mejoras Opcionales

### 1. Efectos de Audio (DSP)

**Implementación con Web Audio API:**

```typescript
// Crear nodos de efectos
const createEffectsChain = (context: AudioContext) => {
    // Compressor (limitar picos)
    const compressor = context.createDynamicsCompressor();
    compressor.threshold.value = -24;
    compressor.knee.value = 30;
    compressor.ratio.value = 12;
    compressor.attack.value = 0.003;
    compressor.release.value = 0.25;

    // EQ de 3 bandas
    const lowShelf = context.createBiquadFilter();
    lowShelf.type = "lowshelf";
    lowShelf.frequency.value = 320;
    lowShelf.gain.value = 0;

    const midPeak = context.createBiquadFilter();
    midPeak.type = "peaking";
    midPeak.frequency.value = 1000;
    midPeak.Q.value = 0.5;
    midPeak.gain.value = 0;

    const highShelf = context.createBiquadFilter();
    highShelf.type = "highshelf";
    highShelf.frequency.value = 3200;
    highShelf.gain.value = 0;

    // Reverb (usando ConvolverNode)
    const reverb = context.createConvolver();
    // Cargar impulse response...

    // Conectar en cadena
    return {
        input: lowShelf,
        output: reverb,
        chain: () => {
            lowShelf.connect(midPeak);
            midPeak.connect(highShelf);
            highShelf.connect(compressor);
            compressor.connect(reverb);
        },
        controls: {
            lowGain: lowShelf.gain,
            midGain: midPeak.gain,
            highGain: highShelf.gain,
            threshold: compressor.threshold
        }
    };
};
```

**UI de efectos:**

```vue
<template>
  <div class="effects-panel">
    <h4>Efectos de Audio</h4>
    
    <!-- EQ -->
    <div class="eq">
      <label>Bajos (320Hz)</label>
      <input v-model.number="lowGain" type="range" min="-12" max="12" step="0.5">
      
      <label>Medios (1kHz)</label>
      <input v-model.number="midGain" type="range" min="-12" max="12" step="0.5">
      
      <label>Agudos (3.2kHz)</label>
      <input v-model.number="highGain" type="range" min="-12" max="12" step="0.5">
    </div>

    <!-- Compressor -->
    <div class="compressor">
      <label>Threshold</label>
      <input v-model.number="threshold" type="range" min="-60" max="0" step="1">
    </div>

    <!-- Reverb -->
    <div class="reverb">
      <label>Reverb Mix</label>
      <input v-model.number="reverbMix" type="range" min="0" max="100" step="5">
    </div>
  </div>
</template>
```

### 2. Integración con Hardware DJ

**Opción A: Web MIDI API**

```typescript
// Conectar controlador MIDI USB
navigator.requestMIDIAccess()
    .then(midiAccess => {
        const inputs = midiAccess.inputs.values();
        
        for (let input of inputs) {
            input.onmidimessage = (event) => {
                const [status, note, velocity] = event.data;
                
                // Control Change (CC)
                if (status === 176) {
                    switch (note) {
                        case 1:  // Fader del mixer
                            mixer.value = velocity / 127 * 2;
                            break;
                        case 7:  // Volumen track 1
                            playlist1Volume.value = velocity / 127 * 100;
                            break;
                        // ... más controles
                    }
                }
                
                // Note On (botones)
                if (status === 144) {
                    switch (note) {
                        case 60:  // Play track 1
                            playlist1.play();
                            break;
                        case 61:  // Stop track 1
                            playlist1.stop();
                            break;
                        // ... más botones
                    }
                }
            };
        }
    });
```

**Controladores compatibles:**
- Numark DJ2GO2
- Pioneer DDJ-200
- Hercules DJControl Starlight
- Akai LPD8
- Novation Launchpad Mini

**Opción B: Protocolo OSC (Open Sound Control)**

```typescript
// Usando librería osc.js
import OSC from 'osc-js';

const osc = new OSC();
osc.open(); // WebSocket server en localhost:8080

osc.on('/mixer/crossfader', (message) => {
    mixer.value = message.args[0] * 2;
});

osc.on('/track/1/volume', (message) => {
    playlist1Volume.value = message.args[0] * 100;
});
```

**Software compatible:**
- TouchOSC (iOS/Android)
- Lemur (iOS)
- Open Stage Control (Desktop)

### 3. Visualización Avanzada

```typescript
// Analizador de espectro
const createSpectrumAnalyzer = (context: AudioContext) => {
    const analyser = context.createAnalyser();
    analyser.fftSize = 2048;
    
    const bufferLength = analyser.frequencyBinCount;
    const dataArray = new Uint8Array(bufferLength);
    
    const draw = (canvas: HTMLCanvasElement) => {
        const ctx = canvas.getContext('2d');
        const width = canvas.width;
        const height = canvas.height;
        
        const render = () => {
            requestAnimationFrame(render);
            
            analyser.getByteFrequencyData(dataArray);
            
            ctx.fillStyle = 'rgb(0, 0, 0)';
            ctx.fillRect(0, 0, width, height);
            
            const barWidth = (width / bufferLength) * 2.5;
            let x = 0;
            
            for (let i = 0; i < bufferLength; i++) {
                const barHeight = dataArray[i] / 255 * height;
                
                const r = barHeight + (25 * (i / bufferLength));
                const g = 250 * (i / bufferLength);
                const b = 50;
                
                ctx.fillStyle = `rgb(${r}, ${g}, ${b})`;
                ctx.fillRect(x, height - barHeight, barWidth, barHeight);
                
                x += barWidth + 1;
            }
        };
        
        render();
    };
    
    return { analyser, draw };
};
```

### 4. Grabación Local

```typescript
// Grabar sesión completa localmente
const recorder = new MediaRecorder(streamNode.stream, {
    mimeType: 'audio/webm;codecs=opus',
    audioBitsPerSecond: 320000
});

const chunks: Blob[] = [];

recorder.ondataavailable = (e) => {
    chunks.push(e.data);
};

recorder.onstop = () => {
    const blob = new Blob(chunks, { type: 'audio/webm' });
    const url = URL.createObjectURL(blob);
    
    // Descargar automáticamente
    const a = document.createElement('a');
    a.href = url;
    a.download = `session_${Date.now()}.webm`;
    a.click();
};

// Iniciar grabación
recorder.start();

// Detener después de X tiempo
setTimeout(() => recorder.stop(), 3600000); // 1 hora
```

---

## 🔌 APIs y Servicios Externos

### 1. ¿Se necesita SDK externo?

**RESPUESTA: NO ❌**

El sistema WebDJ de AzuraCast es **completamente autónomo** y **no requiere**:
- ❌ SDK de terceros (Mixxx, Serato, Traktor, VirtualDJ)
- ❌ Plugins externos
- ❌ Servicios de streaming de terceros
- ❌ APIs de pago
- ❌ Software instalado en el cliente

**Razón:** Usa APIs nativas del navegador:
- ✅ Web Audio API (W3C Standard)
- ✅ MediaRecorder API (W3C Standard)
- ✅ WebSocket API (W3C Standard)
- ✅ getUserMedia API (W3C Standard)

### 2. Integración con Software DJ Profesional (Opcional)

Si se desea permitir que DJs conecten con software profesional:

#### A. Icecast/SHOUTcast Compatible

**Ya está implementado:** Liquidsoap `input.harbor()` es compatible con protocolo ICY.

**Software compatible:**
- Mixxx (Open Source)
- Traktor DJ
- Serato DJ
- VirtualDJ
- BUTT (Broadcast Using This Tool)
- RadioDJ
- SAM Broadcaster

**Configuración en software DJ:**

```
Host: simonamusic.net
Port: 8005
Mount: /
Username: dj_carlos
Password: ********
Protocol: Icecast 2
Format: MP3 o OGG
Bitrate: 128 kbps
```

**Código Liquidsoap (ya existe):**

```liquidsoap
live = input.harbor(
    "/",
    port = 8005,
    auth = azuracast.dj_auth,
    icy = true,  # ← Soporte protocolo ICY/SHOUTcast
    icy_metadata_charset = "UTF-8"
)
```

#### B. RTMP (Real-Time Messaging Protocol)

**No implementado** - Requeriría añadir:

```liquidsoap
# Alternativa: input.rtmp
live_rtmp = input.rtmp(
    "rtmp://0.0.0.0:1935/live",
    auth = azuracast.dj_auth
)

radio = fallback([live_rtmp, live_harbor, radio])
```

**Software compatible con RTMP:**
- OBS Studio
- Streamlabs Desktop
- XSplit Broadcaster

#### C. SRT (Secure Reliable Transport)

**No implementado** - Requeriría Liquidsoap 2.1+:

```liquidsoap
live_srt = input.srt(
    "srt://0.0.0.0:9000",
    auth = azuracast.dj_auth
)
```

**Ventajas:**
- Menor latencia que Icecast
- Mejor recuperación de pérdida de paquetes
- Encryption incorporado

### 3. Servicios Cloud de Mezclado (No recomendado)

Existen APIs de mezclado en la nube, pero **no son necesarias**:

- **Dolby.io Media API**: Mezclado y efectos en la nube
- **Agora RTC SDK**: Real-time audio processing
- **Twilio Live**: Live streaming infrastructure

**Por qué NO usarlas:**
- 💰 Coste adicional (pay-per-use)
- 🔒 Dependencia de terceros
- 📡 Latencia adicional (round-trip a cloud)
- 🛡️ Preocupaciones de privacidad
- ⚡ Sistema actual funciona perfectamente sin ellas

---

## 📈 Estimación de Tiempos y Costos

### Escenario 1: Activar WebDJ Existente (RECOMENDADO)

**Tiempo estimado: 2 horas**

| Tarea | Tiempo | Costo ($50/hora) |
|-------|--------|------------------|
| Habilitar `enable_streamers` en estación | 15 min | $12 |
| Crear usuarios streamer en DB | 30 min | $25 |
| Configurar nginx proxy WebSocket | 30 min | $25 |
| Verificar SSL/HTTPS funcionando | 15 min | $12 |
| Pruebas de conexión y streaming | 30 min | $25 |
| **TOTAL** | **2h** | **$100** |

**Pasos concretos:**

```sql
-- 1. Habilitar streamers
UPDATE station SET enable_streamers = 1, enable_public_page = 1 WHERE id = 1;

-- 2. Crear DJ
INSERT INTO station_streamer (station_id, streamer_username, streamer_password, display_name, is_active)
VALUES (1, 'dj_carlos', '$2y$10$...', 'DJ Carlos', 1);

-- 3. Configurar backend
UPDATE station_backend_configuration 
SET dj_port = 8005, dj_buffer = 5, dj_mount_point = '/'
WHERE station_id = 1;
```

### Escenario 2: Integrar WebDJ en Dashboard DJ

**Tiempo estimado: 4-6 horas**

| Tarea | Tiempo | Costo ($50/hora) |
|-------|--------|------------------|
| Crear componente DJConsole.vue | 1.5h | $75 |
| Añadir tab "Mesa Virtual" en dashboard | 0.5h | $25 |
| Pasar props (baseUri, station) desde backend | 1h | $50 |
| Ajustar estilos para integración visual | 1h | $50 |
| Testing navegadores (Firefox, Chrome) | 1h | $50 |
| Testing permisos micrófono | 0.5h | $25 |
| **TOTAL** | **5.5h** | **$275** |

### Escenario 3: Añadir Efectos y Visualización

**Tiempo estimado: 12-15 horas**

| Tarea | Tiempo | Costo ($50/hora) |
|-------|--------|------------------|
| Implementar EQ de 3 bandas | 2h | $100 |
| Implementar compressor/limiter | 1.5h | $75 |
| Implementar reverb con IR | 2h | $100 |
| UI panel de efectos | 2h | $100 |
| Analizador de espectro (canvas) | 2h | $100 |
| Waveform display | 2h | $100 |
| VU meters mejorados | 1.5h | $75 |
| Testing y optimización | 2h | $100 |
| **TOTAL** | **15h** | **$750** |

### Escenario 4: Soporte Hardware MIDI

**Tiempo estimado: 8-10 horas**

| Tarea | Tiempo | Costo ($50/hora) |
|-------|--------|------------------|
| Implementar Web MIDI API | 2h | $100 |
| Mapeo de controles (faders, botones) | 2h | $100 |
| UI configuración MIDI mapping | 2h | $100 |
| Guardar/cargar presets de mapping | 1.5h | $75 |
| Testing con controladores físicos | 2h | $100 |
| Documentación de uso | 0.5h | $25 |
| **TOTAL** | **10h** | **$500** |

### Escenario 5: Soporte Software DJ Externo (Mixxx, etc.)

**Tiempo estimado: 0-1 hora** ✅ **YA IMPLEMENTADO**

El sistema `input.harbor()` con `icy = true` **ya soporta** conexión desde software DJ profesional.

Solo requiere:
- Documentar configuración (30 min)
- Crear guía de conexión con capturas (30 min)

**Costo total: $50**

---

## 🎯 Recomendación Final

### ⚠️ ACLARACIÓN IMPORTANTE

El sistema WebDJ **YA EXISTE Y FUNCIONA**. La confusión vino del presupuesto original que hablaba de "integración con SDK externo", pero:

1. **NO SE NECESITA SDK EXTERNO** - El WebDJ usa APIs nativas del navegador
2. **YA SOPORTA SOFTWARE PROFESIONAL** - DJs pueden conectar con Traktor/Serato/Mixxx
3. **Solo falta integrarlo visualmente** en el dashboard

### Plan Real para las 15 horas presupuestadas

**Opción A: Implementación Mínima (7h / $350)** ✅ RECOMENDADO

**Prioridad 1: Activar y Documentar (3h / $150)** ✅ CRÍTICO
- Habilitar `enable_streamers` en estación (30 min)
- Crear usuarios DJ en base de datos (30 min)
- Verificar WebDJ funciona en `/public/{station}/dj` (1h)
- **Documentar conexión para software profesional** (1h)
  - Crear PDF/guía con configuración Traktor/Serato/Mixxx
  - Incluir credenciales y troubleshooting

**Prioridad 2: Integración Dashboard (4h / $200)** ✅ ESENCIAL
- Crear tab "Mesa Virtual" en dashboard DJ (2h)
- Añadir opción "Conectar software externo" con credenciales (1h)
- Testing ambos modos (WebDJ browser + software externo) (1h)

**Total Mínimo: 7h / $350**

---

**Opción B: Implementación Completa (15h / $750)** ⭐ ÓPTIMO

Todo lo anterior (7h) +

**Prioridad 3: Mejoras WebDJ para uso básico (4h / $200)**
- Mejorar VU meters con colores (verde/amarillo/rojo)
- Añadir waveform simple (visualización de audio)
- Botón "Modo Terraza" (auto-crossfade cada X minutos)
- Presets de volumen (mañana/tarde/noche)

**Prioridad 4: Guías y capacitación (4h / $200)**
- Manual de usuario WebDJ con capturas de pantalla (2h)
- Video tutorial de 5 min para terraza (1h)
- Guía de conexión software profesional con screenshots (1h)

**Total Completo: 15h / $750**

---

### � Casos de Uso Reales

#### Caso 1: Terraza (uso diario)
**Solución:** WebDJ con 2 playlists
- Playlist 1: Música ambiente automática
- Playlist 2: Jingles y promociones
- Micrófono: Para anuncios ocasionales
- **Horas: 7h** (activar + integrar)

#### Caso 2: DJ Profesional (eventos especiales)
**Solución:** Software profesional + mesa física
- Le das credenciales de conexión Icecast
- Conecta su Traktor/Serato con su controladora
- Pincha profesionalmente con 4 decks + efectos
- **Horas: 3h** (documentar configuración)

#### Caso 3: Streamer desde casa
**Solución:** Mixxx (gratis) + conexión remota
- Descarga Mixxx (open source, gratis)
- Configura conexión Icecast
- Transmite desde su PC personal
- **Horas: 3h** (documentar configuración)

---

### 💰 Inversión Real Necesaria

```
MÍNIMO FUNCIONAL (7h):
- Activar sistema existente
- Integrar en dashboard  
- Documentar software profesional
Costo: $350

ÓPTIMO RECOMENDADO (15h):
- Todo lo anterior
- Mejoras UX para terraza
- Manuales y tutoriales completos
Costo: $750
```

**No se necesitan las 15h originales para "desarrollo"** porque el sistema ya existe. Las 15h serían para:
- Configuración y activación (20%)
- Integración visual dashboard (30%)
- Documentación y guías (30%)
- Mejoras opcionales UX (20%)

---

## 📚 Documentación y Referencias

### Documentación Oficial

1. **Web Audio API**
   - MDN: https://developer.mozilla.org/en-US/docs/Web/API/Web_Audio_API
   - Spec: https://www.w3.org/TR/webaudio/

2. **MediaRecorder API**
   - MDN: https://developer.mozilla.org/en-US/docs/Web/API/MediaRecorder
   - Spec: https://www.w3.org/TR/mediastream-recording/

3. **getUserMedia API**
   - MDN: https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia
   - Spec: https://www.w3.org/TR/mediacapture-streams/

4. **WebSocket API**
   - MDN: https://developer.mozilla.org/en-US/docs/Web/API/WebSocket
   - Spec: https://websockets.spec.whatwg.org/

5. **Liquidsoap**
   - Docs: https://www.liquidsoap.info/
   - Harbor: https://www.liquidsoap.info/doc-dev/harbor.html

### Tutoriales y Ejemplos

1. **Web Audio API Examples**
   - https://github.com/mdn/webaudio-examples
   - https://webaudioapi.com/samples/

2. **MediaRecorder Samples**
   - https://webrtc.github.io/samples/src/content/capture/audio-output/

3. **Liquidsoap Cookbook**
   - https://www.liquidsoap.info/doc-dev/cookbook.html

### Librerías Útiles (Opcionales)

```json
{
  "tone": "^14.7.77",           // Framework audio de alto nivel
  "pizzicato": "^0.6.4",        // Efectos de audio simples
  "wavesurfer.js": "^7.0.0",    // Waveform display
  "osc-js": "^2.3.1",           // Protocolo OSC
  "webmidi": "^3.1.0"           // Web MIDI API wrapper
}
```

### Controladores MIDI Recomendados

| Modelo | Precio | Características |
|--------|--------|-----------------|
| Numark DJ2GO2 | $79 | 2 canales, crossfader, jog wheels |
| Pioneer DDJ-200 | $149 | Bluetooth, 2 canales, FX |
| Hercules DJControl Starlight | $89 | Portátil, RGB, 2 canales |
| Akai LPD8 | $99 | 8 pads, 8 knobs, MIDI |

---

## ✅ Checklist de Implementación

### Fase 1: Activación (2h)

- [ ] Verificar Liquidsoap 2.x instalado
- [ ] Habilitar `enable_streamers` en estación
- [ ] Habilitar `enable_public_page` en estación
- [ ] Configurar `dj_port`, `dj_buffer`, `dj_mount_point`
- [ ] Crear al menos 1 usuario `station_streamer`
- [ ] Verificar SSL/HTTPS activo en dominio
- [ ] Probar acceso a `/public/{station}/dj`
- [ ] Probar conexión WebSocket WSS
- [ ] Probar streaming con micrófono
- [ ] Probar streaming con archivos de audio
- [ ] Verificar metadata se actualiza

### Fase 2: Integración Dashboard (5h)

- [ ] Crear `DJConsole.vue` componente
- [ ] Importar componentes WebDJ existentes
- [ ] Añadir tab "Mesa Virtual" en dashboard DJ
- [ ] Pasar props desde backend PHP
- [ ] Ajustar layout para dashboard
- [ ] Testing en Firefox
- [ ] Testing en Chrome
- [ ] Testing permisos micrófono
- [ ] Verificar funcionamiento de mixer
- [ ] Verificar funcionamiento de playlists

### Fase 3: Mejoras UX (4h)

- [ ] Mejorar diseño de VU meters
- [ ] Añadir waveform display básico
- [ ] Mejorar feedback de conexión
- [ ] Añadir indicadores de estado (streaming/paused)
- [ ] Mejorar diseño responsivo mobile
- [ ] Testing UX con usuarios reales

### Fase 4: Efectos (4h) - OPCIONAL

- [ ] Implementar EQ 3 bandas
- [ ] Implementar compressor
- [ ] Crear UI panel efectos
- [ ] Testing efectos en vivo

---

## 🚨 Problemas Comunes y Soluciones

### 1. getUserMedia falla con "NotAllowedError"

**Causa:** HTTP en lugar de HTTPS

**Solución:**
```bash
# Verificar certificado SSL
certbot certificates

# Renovar si expiró
certbot renew

# Verificar nginx proxy HTTPS
nginx -t
```

### 2. WebSocket falla con "Connection refused"

**Causa:** Puerto DJ bloqueado o Liquidsoap no iniciado

**Solución:**
```bash
# Verificar Liquidsoap corriendo
docker compose ps

# Ver logs Liquidsoap
docker compose logs liquidsoap

# Verificar puerto abierto
netstat -tuln | grep 8005

# Verificar firewall
ufw status
ufw allow 8005/tcp
```

### 3. Audio con latencia alta

**Causa:** Buffer demasiado grande

**Solución:**
```sql
-- Reducir buffer a 2-3 segundos
UPDATE station_backend_configuration 
SET dj_buffer = 2 
WHERE station_id = 1;

-- Reiniciar Liquidsoap
docker compose restart liquidsoap
```

### 4. Autenticación falla

**Causa:** Password hasheado incorrectamente

**Solución:**
```php
// Hashear password correctamente con bcrypt
$hashedPassword = password_hash('password123', PASSWORD_BCRYPT);

// Insertar en DB
UPDATE station_streamer 
SET streamer_password = '$hashedPassword' 
WHERE streamer_username = 'dj_carlos';
```

### 5. Micrófono no detectado

**Causa:** Permisos del navegador

**Solución:**
1. Click en icono de candado en barra de direcciones
2. Permissions > Microphone > Allow
3. Recargar página
4. Si persiste, probar en navegador Incógnito

---

## 🎬 Conclusión y Respuestas Finales

### ❓ ¿Qué falta realmente?

**NADA de desarrollo.** El sistema está completo. Solo falta:

1. **Activarlo** (cambiar configuración DB) - 1h
2. **Integrarlo visualmente** en dashboard - 4h
3. **Documentarlo** para DJs profesionales - 2h

**Total real: 7 horas** (no 15h de desarrollo)

### ❓ ¿Es mejor VirtualDJ que nuestro WebDJ?

**AMBOS COEXISTEN:**

**WebDJ (nuestro):**
- ✅ Para **terraza** (2 playlists automáticas)
- ✅ Para **usuarios básicos** (sin instalación)
- ✅ Para **emergencias** (backup rápido)
- ❌ Limitado para DJ profesional (solo 2 decks, sin efectos avanzados)

**VirtualDJ/Traktor/Serato (profesional):**
- ✅ Para **DJ profesional** con su equipo
- ✅ Para **eventos especiales** (4+ decks, efectos, loops)
- ✅ **YA PUEDE CONECTARSE** al sistema (Icecast compatible)
- ❌ Requiere instalación y conocimiento técnico

### ❓ ¿Un DJ profesional estará satisfecho con 2 pistas?

**NO.** Por eso:

1. **DJ profesional trae su software** (Traktor/Serato/Mixxx)
2. **Le das credenciales de conexión** Icecast
3. **Conecta su mesa física** (Pioneer/Numark/etc.)
4. **Pincha con 4 decks + efectos profesionales**

**Esto YA funciona** sin desarrollo adicional.

### ❓ ¿Puede conectar su mesa física?

**SÍ, de 2 formas:**

**Forma 1: Software profesional + Icecast** ✅ RECOMENDADO
```
1. DJ instala Traktor/Serato en su laptop
2. Conecta su controladora USB (Pioneer DDJ, Numark, etc.)
3. Configura salida Icecast:
   - Host: simonamusic.net
   - Puerto: 8005
   - User: dj_profesional
   - Pass: ******
4. Dale al "Live" y empieza a pinchar
```

**Forma 2: Web MIDI (futuro, opcional)** 🎨 NO IMPLEMENTADO
- Conectar controladora MIDI directo al navegador
- Requiere desarrollo adicional (10h)
- Solo para controladores USB-MIDI básicos
- No recomendado para DJs profesionales

### 🎯 Estrategia Recomendada

**Para el presupuesto:**

```
OPCIÓN A (7h / $350): ACTIVAR + DOCUMENTAR
✅ Activa WebDJ para terraza (uso diario)
✅ Documenta conexión Icecast para DJs profesionales
✅ Crea PDF con credenciales y configuración software
✅ Testing ambos modos

OPCIÓN B (15h / $750): COMPLETO + MEJORAS
Todo lo anterior +
✅ Mejoras UX WebDJ (waveforms, presets)
✅ Videos tutoriales
✅ Manual de usuario completo
```

### 📋 Decisión Final

**¿Qué necesitas decidir?**

1. **¿WebDJ solo para terraza o también mejorarlo?**
   - Solo terraza: 7h / $350
   - Con mejoras: 15h / $750

2. **¿Documentación solo escrita o con videos?**
   - Solo PDF: incluido en 7h
   - PDF + videos: 15h

3. **¿Prioridad de implementación?**
   - Alta: 1 semana
   - Media: 2-3 semanas
   - Baja: cuando haya tiempo

**El sistema WebDJ ya existe y funciona.** Las 7-15 horas son solo para:
- Configuración (20%)
- Integración visual (40%)
- Documentación (40%)

**NO hay desarrollo de mesa DJ desde cero** porque ya está hecha y es robusta (usada por miles de radios en producción).

---

## 📞 Soporte y Contacto

**Documentación AzuraCast WebDJ:**
- GitHub: https://github.com/AzuraCast/AzuraCast
- Docs: https://docs.azuracast.com/en/user-guide/streaming-software
- Discord: https://discord.gg/azuracast

**Tecnologías clave:**
- Liquidsoap: https://www.liquidsoap.info/
- Web Audio API: https://developer.mozilla.org/en-US/docs/Web/API/Web_Audio_API
- MediaRecorder: https://developer.mozilla.org/en-US/docs/Web/API/MediaRecorder

---

**Documento generado el:** 10 de Noviembre 2025  
**Versión:** 1.0  
**Estado:** ✅ COMPLETO Y LISTO PARA IMPLEMENTACIÓN
