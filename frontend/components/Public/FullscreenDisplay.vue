<template>
    <div class="fullscreen-display">
        <!-- QR Widget siempre visible -->
        <qr-scanner-widget :request-url="requestUrl" />

        <!-- Video Player (si hay video_url) -->
        <div
            v-if="currentVideoUrl && displayMode === 'videoclips'"
            class="video-container"
        >
            <iframe
                :key="currentVideoUrl"
                :src="embedUrl"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                class="video-player"
            />
        </div>

        <!-- Audio Visualization Fallback -->
        <div
            v-else
            class="visualization-container"
        >
            <canvas
                ref="visualizationCanvas"
                class="visualization-canvas"
            />
        </div>

        <!-- Song Info Overlay (sutil) -->
        <div class="song-info-overlay">
            <transition name="fade">
                <div
                    v-if="currentSong"
                    :key="currentSong.id"
                    class="song-info"
                >
                    <h1 class="song-title">
                        {{ currentSong.title }}
                    </h1>
                    <h2 class="song-artist">
                        {{ currentSong.artist }}
                    </h2>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup lang="ts">
import {computed, ref, watch, onMounted, onUnmounted} from 'vue';
import QRScannerWidget from './QRScannerWidget.vue';
import {useAudioAnalyzer} from '~/functions/useAudioAnalyzer';

interface Song {
    id: string;
    title: string;
    artist: string;
    video_url?: string | null;
}

interface FullscreenDisplayProps {
    currentSong: Song | null;
    stationShortName: string;
    displayMode: 'videoclips' | 'waveform';
    audioElement?: HTMLAudioElement;
}

const props = defineProps<FullscreenDisplayProps>();

const visualizationCanvas = ref<HTMLCanvasElement | null>(null);
let animationId: number | null = null;
let audioContext: AudioContext | null = null;
let analyser: AnalyserNode | null = null;
let dataArray: Uint8Array | null = null;

// Request URL for QR code
const requestUrl = computed(() => {
    return `${window.location.origin}/public/${props.stationShortName}`;
});

// Current video URL
const currentVideoUrl = computed(() => {
    return props.currentSong?.video_url || null;
});

// Convert YouTube/Vimeo URL to embed format
const embedUrl = computed(() => {
    if (!currentVideoUrl.value) return '';

    const url = currentVideoUrl.value;

    // YouTube
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        const videoId = extractYouTubeId(url);
        return `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=0&controls=0&showinfo=0&rel=0&modestbranding=1`;
    }

    // Vimeo
    if (url.includes('vimeo.com')) {
        const videoId = extractVimeoId(url);
        return `https://player.vimeo.com/video/${videoId}?autoplay=1&muted=0&controls=0&title=0&byline=0&portrait=0`;
    }

    return url;
});

function extractYouTubeId(url: string): string {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : '';
}

function extractVimeoId(url: string): string {
    const regExp = /vimeo.*\/(\d+)/i;
    const match = url.match(regExp);
    return match ? match[1] : '';
}

// Audio Visualization Setup
function setupVisualization() {
    if (!props.audioElement || !visualizationCanvas.value) return;

    try {
        audioContext = new (window.AudioContext || (window as any).webkitAudioContext)();
        const source = audioContext.createMediaElementSource(props.audioElement);
        analyser = audioContext.createAnalyser();
        
        analyser.fftSize = 256;
        const bufferLength = analyser.frequencyBinCount;
        dataArray = new Uint8Array(bufferLength);

        source.connect(analyser);
        analyser.connect(audioContext.destination);

        drawVisualization();
    } catch (error) {
        console.error('Error setting up audio visualization:', error);
    }
}

function drawVisualization() {
    if (!visualizationCanvas.value || !analyser || !dataArray) return;

    const canvas = visualizationCanvas.value;
    const canvasCtx = canvas.getContext('2d');
    if (!canvasCtx) return;

    const WIDTH = canvas.width = window.innerWidth;
    const HEIGHT = canvas.height = window.innerHeight;

    animationId = requestAnimationFrame(drawVisualization);

    analyser.getByteFrequencyData(dataArray);

    // Gradient background
    const gradient = canvasCtx.createLinearGradient(0, 0, 0, HEIGHT);
    gradient.addColorStop(0, '#0f172a');
    gradient.addColorStop(1, '#1e293b');
    canvasCtx.fillStyle = gradient;
    canvasCtx.fillRect(0, 0, WIDTH, HEIGHT);

    const barWidth = (WIDTH / dataArray.length) * 2.5;
    let barHeight;
    let x = 0;

    for (let i = 0; i < dataArray.length; i++) {
        barHeight = (dataArray[i] / 255) * HEIGHT * 0.8;

        // Bar gradient
        const barGradient = canvasCtx.createLinearGradient(0, HEIGHT - barHeight, 0, HEIGHT);
        barGradient.addColorStop(0, `hsl(${i * 1.5 + 200}, 100%, 60%)`);
        barGradient.addColorStop(1, `hsl(${i * 1.5 + 200}, 100%, 40%)`);
        
        canvasCtx.fillStyle = barGradient;
        canvasCtx.fillRect(x, HEIGHT - barHeight, barWidth, barHeight);

        x += barWidth + 1;
    }
}

function cleanupVisualization() {
    if (animationId) {
        cancelAnimationFrame(animationId);
    }
    if (audioContext) {
        audioContext.close();
    }
}

// Watch for changes
watch(() => props.currentSong, (newSong) => {
    // Song changed, video will auto-reload due to :key binding
}, { deep: true });

watch(() => props.displayMode, (mode) => {
    if (mode === 'waveform' && !currentVideoUrl.value) {
        setupVisualization();
    } else {
        cleanupVisualization();
    }
});

onMounted(() => {
    if (props.displayMode === 'waveform' && !currentVideoUrl.value) {
        setupVisualization();
    }
});

onUnmounted(() => {
    cleanupVisualization();
});
</script>

<style scoped>
.fullscreen-display {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: #000;
    overflow: hidden;
}

.video-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.video-player {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.visualization-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.visualization-canvas {
    width: 100%;
    height: 100%;
    display: block;
}

.song-info-overlay {
    position: absolute;
    bottom: 80px;
    left: 0;
    right: 0;
    z-index: 10;
    pointer-events: none;
}

.song-info {
    text-align: center;
    padding: 24px;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.song-title {
    font-size: 3rem;
    font-weight: bold;
    color: white;
    margin: 0 0 12px 0;
    text-shadow: 0 4px 12px rgba(0, 0, 0, 0.8);
    line-height: 1.2;
}

.song-artist {
    font-size: 2rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.8);
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

@media (max-width: 768px) {
    .song-title {
        font-size: 2rem;
    }
    
    .song-artist {
        font-size: 1.5rem;
    }
}
</style>
