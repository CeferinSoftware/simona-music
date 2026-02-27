<template>
    <transition name="ad-fade">
        <div
            v-if="isAdPlaying && currentAd"
            class="ad-overlay"
        >
            <!-- Video Ad -->
            <div
                v-if="currentAd.media_type === 'video' && adEmbedUrl"
                class="ad-video-container"
            >
                <iframe
                    :key="adEmbedUrl"
                    :src="adEmbedUrl"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    class="ad-video-player"
                />
            </div>

            <!-- Audio Ad (show visual) -->
            <div
                v-else
                class="ad-audio-container"
            >
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
import {computed, ref, watch, onMounted, onUnmounted} from 'vue';
import {useAxios} from '~/vendor/axios';

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

const {axios} = useAxios();
const isAdPlaying = ref(false);
const currentAd = ref<AdInfo | null>(null);
const countdown = ref(0);

let pollInterval: ReturnType<typeof setInterval> | null = null;
let countdownInterval: ReturnType<typeof setInterval> | null = null;

const adEmbedUrl = computed(() => {
    if (!currentAd.value?.media_url) return '';
    
    const url = currentAd.value.media_url;
    
    // YouTube
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        const videoId = extractYouTubeId(url);
        if (!videoId) return '';
        return `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=0&controls=0&showinfo=0&rel=0&modestbranding=1`;
    }
    
    // Vimeo
    if (url.includes('vimeo.com')) {
        const match = url.match(/vimeo\.com\/(\d+)/);
        if (!match) return '';
        return `https://player.vimeo.com/video/${match[1]}?autoplay=1&muted=0&controls=0&title=0&byline=0&portrait=0`;
    }
    
    return url;
});

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

async function checkForAd() {
    if (!props.stationShortName) return;
    
    try {
        const {data} = await axios.get<AdResponse>(
            `/api/station/${props.stationShortName}/advertisement`
        );
        
        if (data.is_ad_playing && data.ad) {
            // New ad started
            if (!isAdPlaying.value || currentAd.value?.id !== data.ad.id) {
                currentAd.value = data.ad;
                isAdPlaying.value = true;
                startCountdown(data.ad.duration);
            }
        } else {
            isAdPlaying.value = false;
            currentAd.value = null;
            stopCountdown();
        }
    } catch {
        // Silently fail — don't disrupt the player
    }
}

function startCountdown(duration: number) {
    stopCountdown();
    // Enforce minimum 15 seconds for the ad overlay
    const effectiveDuration = duration > 0 ? duration : 30;
    countdown.value = Math.ceil(effectiveDuration);
    
    countdownInterval = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            stopCountdown();
            isAdPlaying.value = false;
            currentAd.value = null;
        }
    }, 1000);
}

function stopCountdown() {
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }
}

onMounted(() => {
    // Poll every 3 seconds for ad state (needs to be fast enough to catch ads)
    checkForAd();
    pollInterval = setInterval(checkForAd, 3000);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
    stopCountdown();
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
}

.ad-pulse {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 120px;
}

.ad-pulse-bar {
    width: 12px;
    height: 40px;
    background: linear-gradient(180deg, #e94560 0%, #533483 100%);
    border-radius: 6px;
    animation: adPulse 1.2s ease-in-out infinite;
}

@keyframes adPulse {
    0%, 100% { height: 40px; }
    50% { height: 100px; }
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
