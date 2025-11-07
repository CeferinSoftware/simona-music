<template>
    <div class="vu-meter">
        <div class="vu-channel">
            <div class="vu-label">L</div>
            <div class="vu-bar-container">
                <div 
                    class="vu-bar"
                    :style="{ height: `${leftLevel}%` }"
                    :class="getBarClass(leftLevel)"
                />
                <div class="vu-scale">
                    <div class="vu-tick" v-for="i in 5" :key="i" />
                </div>
            </div>
        </div>
        <div class="vu-channel">
            <div class="vu-label">R</div>
            <div class="vu-bar-container">
                <div 
                    class="vu-bar"
                    :style="{ height: `${rightLevel}%` }"
                    :class="getBarClass(rightLevel)"
                />
                <div class="vu-scale">
                    <div class="vu-tick" v-for="i in 5" :key="i" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    leftLevel: number;
    rightLevel: number;
}

const props = defineProps<Props>();

const getBarClass = (level: number) => {
    if (level > 90) return 'vu-bar-clip';
    if (level > 70) return 'vu-bar-hot';
    if (level > 50) return 'vu-bar-warm';
    return 'vu-bar-normal';
};
</script>

<style scoped>
.vu-meter {
    display: flex;
    gap: 12px;
    padding: 12px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    min-height: 150px;
}

.vu-channel {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.vu-label {
    font-size: 12px;
    font-weight: bold;
    color: #fff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.vu-bar-container {
    position: relative;
    width: 24px;
    height: 120px;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 4px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
}

.vu-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    transition: height 0.05s ease-out;
    border-radius: 4px 4px 0 0;
}

.vu-bar-normal {
    background: linear-gradient(to top, #4ade80, #22c55e);
}

.vu-bar-warm {
    background: linear-gradient(to top, #4ade80, #22c55e, #facc15);
}

.vu-bar-hot {
    background: linear-gradient(to top, #4ade80, #22c55e, #facc15, #f97316);
}

.vu-bar-clip {
    background: linear-gradient(to top, #4ade80, #22c55e, #facc15, #f97316, #ef4444);
}

.vu-scale {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column-reverse;
    justify-content: space-between;
    padding: 4px 0;
    pointer-events: none;
}

.vu-tick {
    height: 1px;
    background: rgba(255, 255, 255, 0.3);
}
</style>
