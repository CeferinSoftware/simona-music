<template>
    <div class="screen-container custom-screen">
        <div class="custom-content" v-html="customHtml"></div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

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

const customHtml = computed(() => props.screen.metadata?.html || '<h1>Sin contenido</h1>');
</script>

<style scoped>
.screen-container {
    min-height: 100vh;
    padding: 2rem;
}

.custom-content {
    width: 100%;
    height: 100%;
}
</style>
