<template>
    <div class="qr-request-wrapper">
        <!-- Station Selection -->
        <div class="mb-4" v-if="stations.length > 1">
            <label class="form-label">{{ $gettext('Estación') }}</label>
            <select 
                class="form-select form-select-lg"
                v-model="selectedStationId"
                @change="loadRequestableMedia"
            >
                <option 
                    v-for="station in stations"
                    :key="station.id"
                    :value="station.id"
                >
                    {{ station.name }}
                </option>
            </select>
        </div>

        <!-- Search Bar -->
        <div class="mb-4">
            <label class="form-label">{{ $gettext('Buscar canción') }}</label>
            <input
                type="text"
                class="form-control form-control-lg"
                v-model="searchQuery"
                :placeholder="$gettext('Buscar por título o artista...')"
            />
        </div>

        <!-- Song Selection -->
        <div class="media-list mb-4" v-if="filteredMedia.length > 0">
            <div 
                v-for="item in filteredMedia.slice(0, 20)"
                :key="item.request_id"
                class="media-item"
                :class="{ selected: selectedMedia?.request_id === item.request_id }"
                @click="selectedMedia = item"
            >
                <div class="media-info">
                    <div class="media-title">{{ item.song.title }}</div>
                    <div class="media-artist text-muted">{{ item.song.artist }}</div>
                </div>
            </div>
        </div>
        <div v-else-if="isLoading" class="text-center py-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">{{ $gettext('Cargando...') }}</span>
            </div>
        </div>

        <!-- Request Form (shown when song is selected) -->
        <div v-if="selectedMedia && !submittedRequestId" class="request-form">
            <h5 class="mb-3">{{ $gettext('Información del solicitante (opcional)') }}</h5>
            
            <div class="mb-3">
                <label class="form-label">{{ $gettext('Tu nombre') }}</label>
                <input
                    type="text"
                    class="form-control"
                    v-model="requesterName"
                    :placeholder="$gettext('Anónimo')"
                    maxlength="100"
                />
            </div>

            <div class="mb-3">
                <label class="form-label">{{ $gettext('URL de tu avatar') }}</label>
                <input
                    type="url"
                    class="form-control"
                    v-model="requesterAvatar"
                    :placeholder="$gettext('https://ejemplo.com/avatar.jpg')"
                    maxlength="500"
                />
            </div>

            <div class="mb-3">
                <label class="form-label">{{ $gettext('Comentario') }}</label>
                <textarea
                    class="form-control"
                    v-model="comment"
                    :placeholder="$gettext('Mensaje opcional...')"
                    rows="3"
                ></textarea>
            </div>

            <button
                type="button"
                class="btn btn-primary btn-lg w-100"
                @click="submitRequest"
                :disabled="isSubmitting"
            >
                <span v-if="!isSubmitting">{{ $gettext('Enviar solicitud') }}</span>
                <span v-else>
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    {{ $gettext('Enviando...') }}
                </span>
            </button>
        </div>

        <!-- Status Display (shown after submission) -->
        <div v-if="submittedRequestId" class="request-status">
            <div class="alert" :class="statusAlertClass">
                <h5 class="alert-heading">{{ statusTitle }}</h5>
                <p class="mb-0">{{ statusMessage }}</p>
                <div class="mt-3">
                    <strong>{{ $gettext('Estado:') }}</strong>
                    <span class="badge ms-2" :class="statusBadgeClass">
                        {{ statusLabel }}
                    </span>
                </div>
            </div>

            <button
                type="button"
                class="btn btn-secondary w-100 mt-3"
                @click="resetForm"
            >
                {{ $gettext('Hacer otra solicitud') }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue';
import { useTranslate } from '~/vendor/gettext';
import { useAxios } from '~/vendor/axios';
import { useNotify } from '~/components/Common/Toasts/useNotify';

interface Song {
    title: string;
    artist: string;
    album: string;
    genre: string;
    art: string;
    custom_fields: Record<string, any>;
}

interface RequestableMedia {
    request_id: string;
    request_url: string;
    song: Song;
}

interface Station {
    id: number;
    name: string;
}

interface RequestStatus {
    id: number;
    status: 'pending' | 'queued' | 'accepted' | 'rejected';
    track_title: string;
    track_artist: string;
    requester_name: string | null;
    requester_avatar: string | null;
    comment: string | null;
    timestamp: number;
    played_at: number | null;
}

const props = defineProps<{
    stations: Station[];
}>();

const { $gettext } = useTranslate();
const { axios } = useAxios();
const { notifySuccess, notifyError } = useNotify();

// State
const selectedStationId = ref<number>(props.stations[0]?.id || 0);
const requestableMedia = ref<RequestableMedia[]>([]);
const searchQuery = ref('');
const selectedMedia = ref<RequestableMedia | null>(null);
const requesterName = ref('');
const requesterAvatar = ref('');
const comment = ref('');
const isLoading = ref(false);
const isSubmitting = ref(false);
const submittedRequestId = ref<number | null>(null);
const requestStatus = ref<RequestStatus | null>(null);
const pollingInterval = ref<number | null>(null);

// Computed
const filteredMedia = computed(() => {
    if (!searchQuery.value) {
        return requestableMedia.value;
    }

    const query = searchQuery.value.toLowerCase();
    return requestableMedia.value.filter(item => 
        item.song.title.toLowerCase().includes(query) ||
        item.song.artist.toLowerCase().includes(query) ||
        item.song.album.toLowerCase().includes(query)
    );
});

const statusAlertClass = computed(() => {
    switch (requestStatus.value?.status) {
        case 'pending':
            return 'alert-info';
        case 'queued':
            return 'alert-warning';
        case 'accepted':
            return 'alert-success';
        case 'rejected':
            return 'alert-danger';
        default:
            return 'alert-info';
    }
});

const statusBadgeClass = computed(() => {
    switch (requestStatus.value?.status) {
        case 'pending':
            return 'bg-info';
        case 'queued':
            return 'bg-warning';
        case 'accepted':
            return 'bg-success';
        case 'rejected':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
});

const statusLabel = computed(() => {
    switch (requestStatus.value?.status) {
        case 'pending':
            return $gettext('Pendiente');
        case 'queued':
            return $gettext('En cola');
        case 'accepted':
            return $gettext('Aceptada / Reproducida');
        case 'rejected':
            return $gettext('Rechazada');
        default:
            return '';
    }
});

const statusTitle = computed(() => {
    switch (requestStatus.value?.status) {
        case 'pending':
            return $gettext('Solicitud recibida');
        case 'queued':
            return $gettext('¡Tu canción está en cola!');
        case 'accepted':
            return $gettext('¡Tu canción fue reproducida!');
        case 'rejected':
            return $gettext('Solicitud rechazada');
        default:
            return '';
    }
});

const statusMessage = computed(() => {
    if (!requestStatus.value) return '';
    
    const { track_title, track_artist } = requestStatus.value;
    
    switch (requestStatus.value.status) {
        case 'pending':
            return $gettext('Tu solicitud de "%{title}" por %{artist} ha sido recibida y será procesada pronto.', {
                title: track_title,
                artist: track_artist
            });
        case 'queued':
            return $gettext('"%{title}" por %{artist} está ahora en la cola de reproducción.', {
                title: track_title,
                artist: track_artist
            });
        case 'accepted':
            return $gettext('"%{title}" por %{artist} ya fue reproducida. ¡Gracias por tu solicitud!', {
                title: track_title,
                artist: track_artist
            });
        case 'rejected':
            return $gettext('Tu solicitud no pudo ser procesada.');
        default:
            return '';
    }
});

// Methods
const loadRequestableMedia = async () => {
    if (!selectedStationId.value) return;

    isLoading.value = true;
    try {
        const { data } = await axios.get(`/api/station/${selectedStationId.value}/requests`);
        requestableMedia.value = data;
    } catch (error) {
        notifyError($gettext('Error al cargar las canciones disponibles'));
        console.error(error);
    } finally {
        isLoading.value = false;
    }
};

const submitRequest = async () => {
    if (!selectedMedia.value) return;

    isSubmitting.value = true;
    try {
        const { data } = await axios.post(selectedMedia.value.request_url, {
            requester_name: requesterName.value || null,
            requester_avatar: requesterAvatar.value || null,
            comment: comment.value || null,
        });

        if (data.success) {
            notifySuccess(data.message);
            submittedRequestId.value = data.request_id;
            // Start polling for status
            startStatusPolling();
        } else {
            notifyError(data.message);
        }
    } catch (error: any) {
        notifyError(error?.response?.data?.message || $gettext('Error al enviar la solicitud'));
        console.error(error);
    } finally {
        isSubmitting.value = false;
    }
};

const fetchRequestStatus = async () => {
    if (!submittedRequestId.value) return;

    try {
        const { data } = await axios.get(
            `/api/station/${selectedStationId.value}/request-status/${submittedRequestId.value}`
        );
        requestStatus.value = data;

        // Stop polling if request is accepted or rejected
        if (data.status === 'accepted' || data.status === 'rejected') {
            stopStatusPolling();
        }
    } catch (error) {
        console.error('Error fetching request status:', error);
    }
};

const startStatusPolling = () => {
    // Initial fetch
    fetchRequestStatus();

    // Poll every 5 seconds
    pollingInterval.value = window.setInterval(() => {
        fetchRequestStatus();
    }, 5000);
};

const stopStatusPolling = () => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
};

const resetForm = () => {
    selectedMedia.value = null;
    requesterName.value = '';
    requesterAvatar.value = '';
    comment.value = '';
    submittedRequestId.value = null;
    requestStatus.value = null;
    searchQuery.value = '';
    stopStatusPolling();
};

// Lifecycle
onMounted(() => {
    loadRequestableMedia();
});

watch(selectedStationId, () => {
    resetForm();
    loadRequestableMedia();
});

onBeforeUnmount(() => {
    stopStatusPolling();
});
</script>

<style scoped>
.qr-request-wrapper {
    max-width: 600px;
    margin: 0 auto;
    padding: 1rem;
}

.media-list {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid var(--bs-border-color);
    border-radius: 0.5rem;
}

.media-item {
    padding: 1rem;
    border-bottom: 1px solid var(--bs-border-color);
    cursor: pointer;
    transition: background-color 0.2s;
}

.media-item:hover {
    background-color: var(--bs-light);
}

.media-item.selected {
    background-color: var(--bs-primary);
    color: white;
}

.media-item.selected .text-muted {
    color: rgba(255, 255, 255, 0.8) !important;
}

.media-item:last-child {
    border-bottom: none;
}

.media-title {
    font-weight: 600;
    font-size: 1rem;
}

.media-artist {
    font-size: 0.875rem;
}

.request-form,
.request-status {
    background-color: var(--bs-light);
    padding: 1.5rem;
    border-radius: 0.5rem;
}
</style>
