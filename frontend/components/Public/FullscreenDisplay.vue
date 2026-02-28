<template>
    <div class="fullscreen-wrapper">
        <!-- QR Widget en posición fija -->
        <QRScannerWidget :request-url="requestUrl" />
        
        <!-- Ad Overlay (cubre la pantalla cuando un anuncio está reproduciéndose) -->
        <AdOverlay :station-short-name="stationShortName" />
        
        <div class="fullscreen-display">
        <!-- Loading State -->
        <div v-if="isLoading" class="loading-container">
            <div class="pulse-circles">
                <div
                    v-for="i in 3"
                    :key="i"
                    class="pulse-circle"
                    :style="{ animationDelay: `${i * 0.3}s` }"
                />
            </div>
            <p class="loading-text">Cargando...</p>
        </div>

        <!-- Content (when loaded) -->
        <template v-else>

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
                <div class="waveform-placeholder">
                    <div class="pulse-circles">
                        <div
                            v-for="i in 3"
                            :key="i"
                            class="pulse-circle"
                            :style="{ animationDelay: `${i * 0.3}s` }"
                        />
                    </div>
                </div>
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
        </template>

        <!-- Hidden audio player for streaming -->
        <div style="display: none;">
            <radio-player
                v-bind="radioPlayerProps"
                @np_updated="onNowPlayingUpdate"
            />
        </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import {computed, ref, watch} from 'vue';
import QRScannerWidget from './QRScannerWidget.vue';
import AdOverlay from './AdOverlay.vue';
import RadioPlayer from './Player.vue';
import {ApiNowPlaying} from '~/entities/ApiInterfaces';
import {usePlayerStore} from '~/functions/usePlayerStore';

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
    radioPlayerProps: any; // PlayerProps from Player.vue
    isLoading?: boolean;
}

const props = defineProps<FullscreenDisplayProps>();

const emit = defineEmits<{
    np_updated: [np: ApiNowPlaying]
}>();

const localNp = ref<ApiNowPlaying | null>(null);
const playerStore = usePlayerStore();

// Track whether we muted the Icecast stream for video playback
let mutedForVideo = false;

// Request URL for QR code
const requestUrl = computed(() => {
    console.error('🎯 FullscreenDisplay: stationShortName =', props.stationShortName);
    const baseUrl = window.location.origin;
    const url = `${baseUrl}/public/${props.stationShortName}?request`;
    console.error('🎯 FullscreenDisplay: requestUrl =', url);
    return url;
});

// Current video URL - Solo considerar válido si hay song Y displayMode está en videoclips
const currentVideoUrl = computed(() => {
    // Verificar que tenemos una canción válida
    if (!props.currentSong) {
        console.log('FullscreenDisplay: No currentSong available');
        return null;
    }

    // Verificar que displayMode está configurado para mostrar videos
    if (props.displayMode !== 'videoclips') {
        console.log('FullscreenDisplay: displayMode is not videoclips, it is:', props.displayMode);
        return null;
    }

    // Verificar que hay video_url disponible
    const videoUrl = props.currentSong.video_url;
    if (!videoUrl || videoUrl.trim() === '') {
        console.log('FullscreenDisplay: No video_url for current song');
        return null;
    }

    console.log('FullscreenDisplay: Valid video URL found:', videoUrl);
    return videoUrl.trim();
});

// Embed URL — stored as ref, only updates when song changes (NOT on every NP update)
// This prevents the iframe from reloading every 15 seconds
const embedUrl = ref('');

// When video URL changes (new song), build the embed URL and handle stream muting
watch(currentVideoUrl, (newUrl) => {
    if (!newUrl) {
        embedUrl.value = '';
        // No video — unmute the Icecast stream
        if (mutedForVideo && playerStore.isMuted) {
            playerStore.toggleMute();
            mutedForVideo = false;
        }
        return;
    }

    // Capture elapsed time ONCE for sync (prevents iframe reload on NP updates)
    const elapsed = Math.max(0, Math.floor(localNp.value?.now_playing?.elapsed ?? 0));

    // Build embed URL based on provider
    if (newUrl.includes('youtube.com') || newUrl.includes('youtu.be')) {
        const videoId = extractYouTubeId(newUrl);
        embedUrl.value = videoId
            ? `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=0&controls=0&showinfo=0&rel=0&modestbranding=1&loop=1&playlist=${videoId}&start=${elapsed}`
            : '';
    } else if (newUrl.includes('vimeo.com')) {
        const videoId = extractVimeoId(newUrl);
        embedUrl.value = videoId
            ? `https://player.vimeo.com/video/${videoId}?autoplay=1&muted=0&controls=0&title=0&byline=0&portrait=0&loop=1`
            : '';
    } else {
        embedUrl.value = newUrl;
    }

    // Mute the Icecast stream — audio comes from YouTube/Vimeo
    if (!playerStore.isMuted) {
        playerStore.toggleMute();
        mutedForVideo = true;
    }
}, { immediate: true });

function extractYouTubeId(url: string): string {
    // Mejorada para soportar más formatos de URLs de YouTube
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/,
    ];
    
    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match && match[1]) {
            console.log('FullscreenDisplay: Extracted YouTube ID:', match[1]);
            return match[1];
        }
    }
    
    console.error('FullscreenDisplay: Failed to extract YouTube ID from:', url);
    return '';
}

function extractVimeoId(url: string): string {
    const regExp = /vimeo\.com\/(?:video\/)?(\d+)/i;
    const match = url.match(regExp);
    if (match && match[1]) {
        console.log('FullscreenDisplay: Extracted Vimeo ID:', match[1]);
        return match[1];
    }
    
    console.error('FullscreenDisplay: Failed to extract Vimeo ID from:', url);
    return '';
}

function onNowPlayingUpdate(np: ApiNowPlaying) {
    localNp.value = np;
    console.log('Now Playing Updated:', np);
    
    // Propagar evento hacia FullPlayer
    emit('np_updated', np);
}
</script>

<style scoped>
.fullscreen-wrapper {
    position: relative;
    width: 100vw;
    height: 100vh;
    overflow: visible;
    pointer-events: none;
}

.fullscreen-display {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: #000;
    overflow: hidden;
    pointer-events: auto;
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
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
}

.waveform-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pulse-circles {
    position: relative;
    width: 300px;
    height: 300px;
}

.pulse-circle {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100%;
    height: 100%;
    border: 3px solid rgba(59, 130, 246, 0.6);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    animation: pulse 3s ease-out infinite;
}

@keyframes pulse {
    0% {
        transform: translate(-50%, -50%) scale(0.3);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.5);
        opacity: 0;
    }
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

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    gap: 24px;
}

.loading-text {
    color: white;
    font-size: 1.5rem;
    margin: 0;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.8);
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
