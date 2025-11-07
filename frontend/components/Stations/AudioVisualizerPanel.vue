<template>
    <div class="audio-visualizer-panel">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">{{ $gettext('Visualización de Audio') }}</h5>
            <div class="btn-group btn-group-sm" role="group">
                <button
                    type="button"
                    class="btn"
                    :class="visualizationType === 'vu' ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="visualizationType = 'vu'"
                >
                    {{ $gettext('VU Meter') }}
                </button>
                <button
                    type="button"
                    class="btn"
                    :class="visualizationType === 'waveform' ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="visualizationType = 'waveform'"
                >
                    {{ $gettext('Waveform') }}
                </button>
                <button
                    type="button"
                    class="btn"
                    :class="visualizationType === 'both' ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="visualizationType = 'both'"
                >
                    {{ $gettext('Ambos') }}
                </button>
            </div>
        </div>

        <div v-if="!isAnalyzing" class="text-center py-4 text-muted">
            <p>{{ $gettext('Haz clic en reproducir en una estación para ver la visualización de audio.') }}</p>
        </div>

        <div v-else class="visualization-container">
            <div v-if="visualizationType === 'vu' || visualizationType === 'both'" class="mb-3">
                <vu-meter
                    :left-level="analyzerData.leftLevel"
                    :right-level="analyzerData.rightLevel"
                />
            </div>
            <div v-if="visualizationType === 'waveform' || visualizationType === 'both'">
                <waveform
                    :frequency-data="analyzerData.frequencyData"
                    :width="600"
                    :height="120"
                />
            </div>
        </div>

        <!-- Hidden audio element -->
        <audio
            ref="audioElement"
            style="display: none;"
            crossorigin="anonymous"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useTranslate } from '~/vendor/gettext';
import { useAudioAnalyzer } from '~/functions/useAudioAnalyzer';
import VUMeter from '~/components/Common/VUMeter.vue';
import Waveform from '~/components/Common/Waveform.vue';

interface Props {
    streamUrl?: string;
    autoPlay?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    streamUrl: '',
    autoPlay: false
});

const { $gettext } = useTranslate();
const audioElement = ref<HTMLAudioElement | null>(null);
const visualizationType = ref<'vu' | 'waveform' | 'both'>('both');

const { isAnalyzing, analyzerData, startAnalyzing, stopAnalyzing } = useAudioAnalyzer(audioElement);

// Watch stream URL changes
watch(() => props.streamUrl, async (newUrl) => {
    stopAnalyzing();
    
    if (!audioElement.value || !newUrl) {
        return;
    }

    try {
        audioElement.value.src = newUrl;
        
        if (props.autoPlay) {
            await audioElement.value.play();
            await startAnalyzing();
        }
    } catch (error) {
        console.error('Error playing audio:', error);
    }
}, { immediate: true });

// Expose methods for parent component
defineExpose({
    play: async () => {
        if (audioElement.value && props.streamUrl) {
            try {
                await audioElement.value.play();
                await startAnalyzing();
            } catch (error) {
                console.error('Error playing audio:', error);
            }
        }
    },
    pause: () => {
        if (audioElement.value) {
            audioElement.value.pause();
            stopAnalyzing();
        }
    },
    stop: () => {
        stopAnalyzing();
        if (audioElement.value) {
            audioElement.value.pause();
            audioElement.value.src = '';
        }
    }
});
</script>

<style scoped>
.audio-visualizer-panel {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.visualization-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}
</style>
