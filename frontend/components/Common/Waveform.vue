<template>
    <div class="waveform">
        <canvas 
            ref="canvasRef" 
            :width="width" 
            :height="height"
            class="waveform-canvas"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';

interface Props {
    frequencyData: Uint8Array;
    width?: number;
    height?: number;
}

const props = withDefaults(defineProps<Props>(), {
    width: 400,
    height: 100
});

const canvasRef = ref<HTMLCanvasElement | null>(null);
let animationFrameId: number | null = null;

const draw = () => {
    if (!canvasRef.value) return;

    const canvas = canvasRef.value;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Clear canvas
    ctx.fillStyle = 'rgba(0, 0, 0, 0.3)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Draw waveform
    const bufferLength = props.frequencyData.length;
    const barWidth = canvas.width / bufferLength;
    
    // Create gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
    gradient.addColorStop(0, '#ef4444');
    gradient.addColorStop(0.3, '#f97316');
    gradient.addColorStop(0.5, '#facc15');
    gradient.addColorStop(0.7, '#4ade80');
    gradient.addColorStop(1, '#22c55e');

    for (let i = 0; i < bufferLength; i++) {
        const barHeight = (props.frequencyData[i] / 255) * canvas.height;
        const x = i * barWidth;
        const y = canvas.height - barHeight;

        ctx.fillStyle = gradient;
        ctx.fillRect(x, y, barWidth - 1, barHeight);
    }
};

// Watch for changes in frequency data
watch(
    () => props.frequencyData,
    () => {
        draw();
    },
    { deep: true }
);

onMounted(() => {
    draw();
});

onBeforeUnmount(() => {
    if (animationFrameId !== null) {
        cancelAnimationFrame(animationFrameId);
    }
});
</script>

<style scoped>
.waveform {
    padding: 12px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
}

.waveform-canvas {
    display: block;
    border-radius: 4px;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
}
</style>
