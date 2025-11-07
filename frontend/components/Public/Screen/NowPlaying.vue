<template>
    <div class="screen-container now-playing">
        <div class="now-playing-info" v-if="nowPlaying">
            <div class="album-art" v-if="nowPlaying.now_playing.song.art">
                <img :src="nowPlaying.now_playing.song.art" alt="Album Art" />
            </div>
            <div class="song-details">
                <h1 class="song-title">{{ nowPlaying.now_playing.song.title }}</h1>
                <h2 class="song-artist">{{ nowPlaying.now_playing.song.artist }}</h2>
                <p class="song-album" v-if="nowPlaying.now_playing.song.album">
                    {{ nowPlaying.now_playing.song.album }}
                </p>
            </div>
            
            <!-- Audio Visualization -->
            <div class="visualization-section" v-if="nowPlaying.station.listen_url">
                <div class="d-flex justify-content-center gap-3 mb-3">
                    <vu-meter
                        :left-level="analyzerData.leftLevel"
                        :right-level="analyzerData.rightLevel"
                    />
                    <waveform
                        :frequency-data="analyzerData.frequencyData"
                        :width="500"
                        :height="150"
                    />
                </div>
            </div>
        </div>
        <div class="loading" v-else>
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ $gettext('Cargando...') }}</span>
            </div>
        </div>

        <!-- Hidden audio element -->
        <audio
            ref="audioElement"
            :src="nowPlaying?.station?.listen_url"
            autoplay
            style="display: none;"
            crossorigin="anonymous"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { useAxios } from '~/vendor/axios';
import { useTranslate } from '~/vendor/gettext';
import { useAudioAnalyzer } from '~/functions/useAudioAnalyzer';
import VUMeter from '~/components/Common/VUMeter.vue';
import Waveform from '~/components/Common/Waveform.vue';

interface Props {
    station: {
        id: number;
        name: string;
    };
    screen: {
        id: number;
        name: string;
        content_type: string;
        metadata: any;
    };
}

const props = defineProps<Props>();
const { axios } = useAxios();
const { $gettext } = useTranslate();

const nowPlaying = ref<any>(null);
const pollingInterval = ref<number | null>(null);
const audioElement = ref<HTMLAudioElement | null>(null);

const { analyzerData, startAnalyzing } = useAudioAnalyzer(audioElement);

const fetchNowPlaying = async () => {
    try {
        const { data } = await axios.get(`/api/nowplaying/${props.station.id}`);
        nowPlaying.value = data;
    } catch (error) {
        console.error('Error fetching now playing:', error);
    }
};

// Start audio analysis when audio element is ready and playing
watch(audioElement, async (newAudioElement) => {
    if (newAudioElement) {
        newAudioElement.addEventListener('playing', async () => {
            await startAnalyzing();
        });
    }
});

onMounted(() => {
    fetchNowPlaying();
    // Poll every 10 seconds
    pollingInterval.value = window.setInterval(fetchNowPlaying, 10000);
});

onBeforeUnmount(() => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
    }
});
</script>

<style scoped>
.screen-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.now-playing-info {
    text-align: center;
    color: white;
    max-width: 800px;
}

.album-art {
    margin-bottom: 2rem;
}

.album-art img {
    width: 400px;
    height: 400px;
    object-fit: cover;
    border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.song-title {
    font-size: 3rem;
    font-weight: bold;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.song-artist {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.song-album {
    font-size: 1.5rem;
    opacity: 0.7;
}

.visualization-section {
    margin-top: 2rem;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 1rem;
}

.loading {
    color: white;
}
</style>
