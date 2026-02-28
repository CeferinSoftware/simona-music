<template>
    <transition name="ad-fade">
        <div
            v-if="isAdPlaying && currentAd"
            class="ad-overlay"
        >
            <!-- Video Ad (YouTube / Vimeo) -->
            <div
                v-if="currentAd.media_type === 'video' && adEmbedUrl"
                class="ad-video-container"
            >
                <iframe
                    ref="videoIframe"
                    :key="adEmbedUrl"
                    :src="adEmbedUrl"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    class="ad-video-player"
                />
            </div>

            <!-- Audio Ad: visual overlay only (audio plays through Icecast stream) -->
            <div
                v-else-if="currentAd.media_type === 'audio'"
                class="ad-audio-container"
            >
                <div class="ad-audio-visual">
                    <div class="ad-audio-icon">&#127925;</div>
                    <div class="ad-audio-title">{{ currentAd.name }}</div>
                    <div class="ad-pulse">
                        <div
                            v-for="i in 7"
                            :key="i"
                            class="ad-pulse-bar"
                            :style="{ animationDelay: `${i * 0.12}s` }"
                        />
                    </div>
                </div>
            </div>

            <!-- Fallback: unknown media -->
            <div v-else class="ad-audio-container">
                <div class="ad-audio-visual">
                    <div class="ad-pulse">
                        <div
                            v-for="i in 5"
                            :key="i"
                            class="ad-pulse-bar"
                            :style="{ animationDelay: `${i * 0.15}s` }"
                        />
                    </div>
                </div>
            </div>

            <!-- Ad Info Badge -->
            <div class="ad-badge">
                <span class="ad-badge-text">ANUNCIO</span>
                <span
                    v-if="countdown > 0"
                    class="ad-countdown"
                >{{ countdown }}s</span>
            </div>

            <!-- Advertiser name -->
            <div
                v-if="currentAd.advertiser_name"
                class="ad-advertiser"
            >
                {{ currentAd.advertiser_name }}
            </div>
        </div>
    </transition>
</template>

<script setup lang="ts">
import {computed, ref, onMounted, onUnmounted} from 'vue';
import {useAxios} from '~/vendor/axios';
import {usePlayerStore} from '~/functions/usePlayerStore';

interface AdInfo {
    id: number;
    name: string;
    advertiser_name: string | null;
    media_type: 'audio' | 'video';
    media_url: string | null;
    media_path: string | null;
    duration: number;
}

interface AdResponse {
    is_ad_playing: boolean;
    ad: AdInfo | null;
}

interface Props {
    stationShortName: string;
}

const props = defineProps<Props>();

const {axiosSilent} = useAxios();
const playerStore = usePlayerStore();

const isAdPlaying = ref(false);
const currentAd = ref<AdInfo | null>(null);
const countdown = ref(0);
const videoIframe = ref<HTMLIFrameElement | null>(null);

// Track the last processed ad ID to avoid re-triggering same ad
let lastAdId: number | null = null;
let pollInterval: ReturnType<typeof setInterval> | null = null;
let countdownInterval: ReturnType<typeof setInterval> | null = null;
// Remember volume before muting for ad
let volumeBeforeAd: number | null = null;
let wasMutedBeforeAd = false;
// Safety: max ad duration fallback (5 min)
const MAX_AD_DURATION = 300;

// --- COMPUTED: embed URL for video ads ---
const adEmbedUrl = computed(() => {
    if (!currentAd.value?.media_url) return '';
    const url = currentAd.value.media_url;

    // YouTube with JS API enabled + autoplay
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        const videoId = extractYouTubeId(url);
        if (!videoId) return '';
        return `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=0&controls=0&showinfo=0&rel=0&modestbranding=1&enablejsapi=1&origin=${encodeURIComponent(window.location.origin)}`;
    }

    // Vimeo
    if (url.includes('vimeo.com')) {
        const match = url.match(/vimeo\.com\/(\d+)/);
        if (!match) return '';
        return `https://player.vimeo.com/video/${match[1]}?autoplay=1&muted=0&controls=0&title=0&byline=0&portrait=0`;
    }

    return url;
});

// --- HELPERS ---
function extractYouTubeId(url: string): string {
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/,
    ];
    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match?.[1]) return match[1];
    }
    return '';
}

function formatSecs(s: number): string {
    const m = Math.floor(s / 60);
    const sec = Math.floor(s % 60);
    return `${m}:${sec.toString().padStart(2, '0')}`;
}

// --- MUTE / UNMUTE background stream ---
function muteBackgroundStream() {
    // Save current state
    volumeBeforeAd = playerStore.volume;
    wasMutedBeforeAd = playerStore.isMuted;
    // Mute the main stream
    if (!playerStore.isMuted) {
        playerStore.toggleMute();
    }
}

function unmuteBackgroundStream() {
    // Restore previous mute state
    if (volumeBeforeAd !== null) {
        if (wasMutedBeforeAd) {
            // Was already muted, leave it muted
            if (!playerStore.isMuted) playerStore.toggleMute();
        } else {
            // Was not muted, unmute
            if (playerStore.isMuted) playerStore.toggleMute();
        }
        volumeBeforeAd = null;
    }
}

// --- AD LIFECYCLE ---
function startAd(ad: AdInfo) {
    if (currentAd.value?.id === ad.id) return; // Already playing this ad

    currentAd.value = ad;
    isAdPlaying.value = true;
    lastAdId = ad.id;

    // Only mute for VIDEO ads (audio ads play through the Icecast stream naturally)
    if (ad.media_type === 'video') {
        muteBackgroundStream();
        setupYouTubeListener();
    }

    // Fallback countdown
    const fallbackDuration = ad.duration > 0
        ? Math.min(ad.duration, MAX_AD_DURATION)
        : (ad.media_type === 'video' ? 60 : 30);
    startCountdown(fallbackDuration);
}

function endAd() {
    const wasVideoAd = currentAd.value?.media_type === 'video';

    isAdPlaying.value = false;
    currentAd.value = null;
    stopCountdown();

    window.removeEventListener('message', onYouTubeMessage);

    // Only unmute for video ads (audio ads never muted the stream)
    if (wasVideoAd) {
        unmuteBackgroundStream();
    }
}

// --- YOUTUBE END DETECTION ---
function setupYouTubeListener() {
    // Listen for YouTube postMessage events to detect video end
    window.addEventListener('message', onYouTubeMessage);
}

function onYouTubeMessage(event: MessageEvent) {
    // YouTube sends state changes via postMessage
    try {
        let data = event.data;
        if (typeof data === 'string') {
            data = JSON.parse(data);
        }
        // YouTube IFrame API: playerState 0 = ended
        if (data?.event === 'onStateChange' && data?.info === 0) {
            endAd();
        }
        // Also check the info object format
        if (data?.info?.playerState === 0) {
            endAd();
        }
    } catch {
        // Not a YouTube message, ignore
    }
}

// --- COUNTDOWN (fallback timer) ---
function startCountdown(duration: number) {
    stopCountdown();
    countdown.value = Math.ceil(duration);

    countdownInterval = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            // Fallback: if media hasn't ended naturally, force end
            endAd();
        }
    }, 1000);
}

function stopCountdown() {
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }
}

// --- POLLING ---
async function checkForAd() {
    if (!props.stationShortName) return;

    try {
        const {data} = await axiosSilent.get<AdResponse>(
            `/api/station/${props.stationShortName}/advertisement`
        );

        if (data.is_ad_playing && data.ad) {
            // Prevent replaying the same ad we just finished.
            // Server cache may still be active after the ad ended on the frontend.
            if (data.ad.id === lastAdId && !isAdPlaying.value) {
                return; // Same ad still in server cache, already played
            }
            // Start the ad if not already playing this exact one
            if (!isAdPlaying.value || currentAd.value?.id !== data.ad.id) {
                startAd(data.ad);
            }
        } else {
            // Server says no ad playing
            if (isAdPlaying.value) {
                // Let video ads finish via countdown or YouTube end event
                if (currentAd.value?.media_type === 'video' && countdown.value > 0) {
                    return;
                }
                // Media is done, end the ad
                endAd();
            }
            // Server confirms no ad playing — clear lastAdId so the same
            // ad can play again in a future cycle
            lastAdId = null;
        }
    } catch {
        // Silently fail - don't interrupt playback on network errors
    }
}

// --- LIFECYCLE ---
onMounted(() => {
    checkForAd();
    pollInterval = setInterval(checkForAd, 3000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
    stopCountdown();
    window.removeEventListener('message', onYouTubeMessage);
    // Make sure we unmute if component is destroyed while ad is playing
    if (isAdPlaying.value) {
        unmuteBackgroundStream();
    }
});
</script>

<style scoped>
.ad-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ad-video-container {
    width: 100%;
    height: 100%;
}

.ad-video-player {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ad-audio-container {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
}

.ad-audio-visual {
    text-align: center;
    max-width: 500px;
    padding: 40px;
}

.ad-audio-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.ad-audio-title {
    color: #fff;
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 30px;
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
}

.ad-pulse {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 100px;
    margin-bottom: 30px;
}

.ad-pulse-bar {
    width: 10px;
    height: 30px;
    background: linear-gradient(180deg, #e94560 0%, #533483 100%);
    border-radius: 5px;
    animation: adPulse 1.2s ease-in-out infinite;
}

@keyframes adPulse {
    0%, 100% { height: 30px; }
    50% { height: 80px; }
}

.ad-audio-progress {
    width: 100%;
}

.ad-progress-bar {
    width: 100%;
    height: 6px;
    background: rgba(255,255,255,0.15);
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 8px;
}

.ad-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #e94560, #533483);
    border-radius: 3px;
    transition: width 0.3s linear;
}

.ad-progress-time {
    color: rgba(255,255,255,0.6);
    font-size: 14px;
    font-variant-numeric: tabular-nums;
}

.ad-badge {
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(233, 69, 96, 0.9);
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 10000;
    backdrop-filter: blur(10px);
}

.ad-badge-text {
    text-transform: uppercase;
    letter-spacing: 2px;
}

.ad-countdown {
    background: rgba(255, 255, 255, 0.2);
    padding: 2px 8px;
    border-radius: 4px;
    font-variant-numeric: tabular-nums;
}

.ad-advertiser {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    color: rgba(255, 255, 255, 0.7);
    font-size: 16px;
    z-index: 10000;
}

/* Transitions */
.ad-fade-enter-active,
.ad-fade-leave-active {
    transition: opacity 0.5s ease;
}

.ad-fade-enter-from,
.ad-fade-leave-to {
    opacity: 0;
}
</style>
