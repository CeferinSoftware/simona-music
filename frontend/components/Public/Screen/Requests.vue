<template>
    <div class="screen-container requests-screen">
        <h1 class="text-center mb-4">{{ $gettext('Solicitudes Pendientes') }}</h1>
        <div class="requests-list" v-if="requests.length > 0">
            <div 
                v-for="(request, index) in requests"
                :key="request.id"
                class="request-item"
            >
                <div class="request-number">{{ index + 1 }}</div>
                <div class="request-details">
                    <div class="song-title">{{ request.track_title }}</div>
                    <div class="song-artist">{{ request.track_artist }}</div>
                    <div class="requester-name" v-if="request.requester_name">
                        {{ $gettext('Pedido por:') }} {{ request.requester_name }}
                    </div>
                </div>
            </div>
        </div>
        <div class="no-requests" v-else>
            <p>{{ $gettext('No hay solicitudes pendientes') }}</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useAxios } from '~/vendor/axios';
import { useTranslate } from '~/vendor/gettext';

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

const requests = ref<any[]>([]);
const pollingInterval = ref<number | null>(null);

const fetchRequests = async () => {
    try {
        // Aquí necesitamos un endpoint que liste los requests pendientes
        // Por ahora usaremos un placeholder
        requests.value = [];
    } catch (error) {
        console.error('Error fetching requests:', error);
    }
};

onMounted(() => {
    fetchRequests();
    pollingInterval.value = window.setInterval(fetchRequests, 5000);
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
    padding: 2rem;
    background: #f8f9fa;
}

h1 {
    color: #333;
    font-weight: bold;
}

.requests-list {
    max-width: 800px;
    margin: 0 auto;
}

.request-item {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    margin-bottom: 1rem;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.request-number {
    font-size: 2rem;
    font-weight: bold;
    color: var(--bs-primary);
    margin-right: 1.5rem;
    min-width: 50px;
    text-align: center;
}

.request-details {
    flex: 1;
}

.song-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.song-artist {
    font-size: 1.25rem;
    color: #666;
    margin-bottom: 0.5rem;
}

.requester-name {
    font-size: 1rem;
    color: #999;
    font-style: italic;
}

.no-requests {
    text-align: center;
    padding: 4rem;
    font-size: 1.5rem;
    color: #999;
}
</style>
