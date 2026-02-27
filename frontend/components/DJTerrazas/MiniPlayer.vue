<template>
    <div class="mini-player border-top pt-2 mt-2">
        <!-- Now Playing -->
        <div class="d-flex align-items-center mb-2">
            <img
                v-if="nowPlaying?.now_playing?.song?.art"
                :src="nowPlaying.now_playing.song.art"
                class="rounded me-2 flex-shrink-0"
                width="40"
                height="40"
                alt=""
            >
            <div
                v-else
                class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center flex-shrink-0"
                style="width:40px;height:40px"
            >
                <icon :icon="IconMusic" class="text-white" />
            </div>
            <div class="text-truncate flex-grow-1 me-2">
                <div class="fw-semibold small text-truncate">
                    {{ currentTitle || $gettext('Sin reproducción') }}
                </div>
                <div class="text-muted small text-truncate">
                    {{ currentArtist || '—' }}
                </div>
            </div>
            <span
                v-if="isStreamerLive"
                class="badge text-bg-danger me-1"
            >LIVE</span>
        </div>

        <!-- Transport Controls -->
        <div class="d-flex align-items-center justify-content-center gap-1 mb-2">
            <button
                class="btn btn-sm btn-outline-secondary"
                :disabled="!isOnline || skipping"
                :title="$gettext('Saltar canción')"
                @click="doSkip"
            >
                <icon :icon="IconSkipNext" />
            </button>
        </div>

        <!-- Playlist Selector -->
        <div v-if="playlists.length > 0" class="mb-2">
            <div class="input-group input-group-sm">
                <label class="input-group-text" :for="'pl-select-'+stationId">
                    <icon :icon="IconPlaylist" />
                </label>
                <select
                    :id="'pl-select-'+stationId"
                    v-model="selectedPlaylistId"
                    class="form-select form-select-sm"
                    @change="togglePlaylist"
                >
                    <option value="">{{ $gettext('— Seleccionar lista —') }}</option>
                    <option
                        v-for="pl in playlists"
                        :key="pl.id"
                        :value="pl.id"
                    >
                        {{ pl.name }}
                        <template v-if="pl.is_enabled"> ✓</template>
                    </option>
                </select>
            </div>
        </div>

        <!-- Upload Quick -->
        <div class="mb-1">
            <label
                :for="'upload-'+stationId"
                class="btn btn-sm btn-outline-primary w-100"
                :class="{ disabled: uploading }"
            >
                <icon :icon="IconUpload" class="me-1" />
                {{ uploading ? $gettext('Subiendo...') : $gettext('Subir canción') }}
            </label>
            <input
                :id="'upload-'+stationId"
                type="file"
                accept="audio/*"
                class="d-none"
                :disabled="uploading"
                @change="onFileSelected"
            >
            <div v-if="uploading" class="progress mt-1" style="height: 4px">
                <div
                    class="progress-bar progress-bar-striped progress-bar-animated"
                    :style="{ width: uploadProgress + '%' }"
                />
            </div>
        </div>

        <!-- Status message -->
        <div v-if="statusMessage" class="small mt-1" :class="statusClass">
            {{ statusMessage }}
        </div>
    </div>
</template>

<script setup lang="ts">
import {ref, computed, onMounted, onUnmounted} from 'vue';
import {useAxios} from '~/vendor/axios';
import {useTranslate} from '~/vendor/gettext';
import Icon from '~/components/Common/Icons/Icon.vue';
import {
    IconSkipNext,
    IconPlaylist,
    IconUpload,
    IconMusic,
} from '~/components/Common/Icons/icons.ts';

interface NowPlayingData {
    now_playing?: {
        song?: {
            title?: string;
            artist?: string;
            art?: string;
        };
    };
    live?: {
        is_live?: boolean;
        streamer_name?: string;
    };
}

interface PlaylistItem {
    id: number;
    name: string;
    is_enabled: boolean;
    links?: {
        self?: string;
        toggle?: string;
    };
}

const props = defineProps<{
    stationId: number;
    isOnline: boolean;
}>();

const {$gettext} = useTranslate();
const {axios} = useAxios();

const nowPlaying = ref<NowPlayingData | null>(null);
const playlists = ref<PlaylistItem[]>([]);
const selectedPlaylistId = ref<string>('');
const skipping = ref(false);
const uploading = ref(false);
const uploadProgress = ref(0);
const statusMessage = ref('');
const statusClass = ref('text-muted');
let pollTimer: ReturnType<typeof setInterval> | null = null;

const currentTitle = computed(() => nowPlaying.value?.now_playing?.song?.title || '');
const currentArtist = computed(() => nowPlaying.value?.now_playing?.song?.artist || '');
const isStreamerLive = computed(() => nowPlaying.value?.live?.is_live || false);

const showStatus = (msg: string, cls: string = 'text-muted', timeout: number = 3000) => {
    statusMessage.value = msg;
    statusClass.value = cls;
    if (timeout > 0) {
        setTimeout(() => { statusMessage.value = ''; }, timeout);
    }
};

// --- NOW PLAYING ---
const fetchNowPlaying = async () => {
    try {
        const {data} = await axios.get(`/api/station/${props.stationId}/nowplaying`);
        nowPlaying.value = data;
    } catch {
        // silent
    }
};

// --- PLAYLISTS ---
const fetchPlaylists = async () => {
    try {
        const {data} = await axios.get(`/api/station/${props.stationId}/playlists`, {
            params: {internal: true, rowCount: 0}
        });
        const rows = data.rows ?? data;
        playlists.value = Array.isArray(rows) ? rows : [];
    } catch {
        playlists.value = [];
    }
};

const togglePlaylist = async () => {
    if (!selectedPlaylistId.value) return;
    const pl = playlists.value.find(p => p.id === Number(selectedPlaylistId.value));
    if (!pl) return;

    try {
        await axios.put(`/api/station/${props.stationId}/playlist/${pl.id}/toggle`);
        showStatus(
            pl.is_enabled
                ? $gettext('Lista desactivada: ') + pl.name
                : $gettext('Lista activada: ') + pl.name,
            'text-success'
        );
        await fetchPlaylists();
    } catch {
        showStatus($gettext('Error al cambiar la lista'), 'text-danger');
    }
    selectedPlaylistId.value = '';
};

// --- SKIP ---
const doSkip = async () => {
    skipping.value = true;
    try {
        await axios.post(`/api/station/${props.stationId}/backend/skip`);
        showStatus($gettext('Canción saltada'), 'text-success');
        setTimeout(() => fetchNowPlaying(), 1500);
    } catch {
        showStatus($gettext('Error al saltar canción'), 'text-danger');
    } finally {
        skipping.value = false;
    }
};

// --- UPLOAD ---
const onFileSelected = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    uploading.value = true;
    uploadProgress.value = 0;

    const formData = new FormData();
    formData.append('file', file);

    try {
        // Upload via Flow-compatible endpoint
        // AzuraCast uses chunked upload, but for simplicity we use the basic upload
        await axios.post(`/api/station/${props.stationId}/files/upload`, formData, {
            headers: {'Content-Type': 'multipart/form-data'},
            params: {
                flowChunkNumber: 1,
                flowChunkSize: file.size,
                flowCurrentChunkSize: file.size,
                flowTotalSize: file.size,
                flowTotalChunks: 1,
                flowFilename: file.name,
                flowRelativePath: file.name,
            },
            onUploadProgress: (progressEvent) => {
                if (progressEvent.total) {
                    uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                }
            }
        });
        showStatus($gettext('Canción subida: ') + file.name, 'text-success');
        await fetchPlaylists();
    } catch (e: any) {
        const msg = e.response?.data?.message || $gettext('Error al subir el archivo');
        showStatus(msg, 'text-danger', 5000);
    } finally {
        uploading.value = false;
        uploadProgress.value = 0;
        input.value = '';
    }
};

// --- LIFECYCLE ---
onMounted(async () => {
    await Promise.all([fetchNowPlaying(), fetchPlaylists()]);
    pollTimer = setInterval(fetchNowPlaying, 10000);
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
});
</script>

<style scoped>
.mini-player {
    font-size: 0.85rem;
}
</style>
