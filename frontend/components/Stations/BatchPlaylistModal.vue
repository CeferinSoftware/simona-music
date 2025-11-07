<template>
    <modal
        id="batch_playlist_modal"
        ref="$modal"
        size="lg"
        :title="$gettext('Agregar a Múltiples Playlists')"
        @hidden="onHidden"
    >
        <div class="modal-body">
            <div v-if="selectedMedia" class="alert alert-info mb-3">
                <strong>{{ $gettext('Canción seleccionada:') }}</strong>
                <div class="mt-1">
                    <strong>{{ selectedMedia.title }}</strong> - {{ selectedMedia.artist }}
                </div>
            </div>

            <div class="mb-3">
                <h5>{{ $gettext('Selecciona las estaciones y playlists') }}</h5>
                <p class="text-muted small">
                    {{ $gettext('Selecciona las playlists de cada estación donde quieres agregar esta canción.') }}
                </p>
            </div>

            <div v-if="loading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">{{ $gettext('Cargando...') }}</span>
                </div>
            </div>

            <div v-else>
                <div
                    v-for="station in stations"
                    :key="station.id"
                    class="station-section mb-4"
                >
                    <div class="station-header d-flex align-items-center mb-2">
                        <input
                            :id="`station-${station.id}`"
                            v-model="selectedStations"
                            type="checkbox"
                            class="form-check-input me-2"
                            :value="station.id"
                            @change="onStationToggle(station.id)"
                        >
                        <label
                            :for="`station-${station.id}`"
                            class="form-check-label fw-bold"
                        >
                            {{ station.name }}
                        </label>
                    </div>

                    <div v-if="selectedStations.includes(station.id)" class="playlists-list ms-4">
                        <div
                            v-for="playlist in station.playlists"
                            :key="playlist.id"
                            class="form-check"
                        >
                            <input
                                :id="`playlist-${playlist.id}`"
                                v-model="selectedPlaylists[station.id]"
                                type="checkbox"
                                class="form-check-input"
                                :value="playlist.id"
                            >
                            <label
                                :for="`playlist-${playlist.id}`"
                                class="form-check-label"
                            >
                                {{ playlist.name }}
                                <span class="badge text-bg-secondary ms-1">
                                    {{ playlist.num_songs }} {{ $gettext('canciones') }}
                                </span>
                            </label>
                        </div>
                        <div v-if="station.playlists.length === 0" class="text-muted small">
                            {{ $gettext('No hay playlists disponibles en esta estación') }}
                        </div>
                    </div>
                </div>

                <div v-if="stations.length === 0" class="alert alert-warning">
                    {{ $gettext('No hay estaciones disponibles') }}
                </div>
            </div>

            <div v-if="Object.keys(getSelectedOperations()).length > 0" class="alert alert-success mt-3">
                <strong>{{ $gettext('Resumen:') }}</strong>
                <div class="mt-2">
                    {{ $gettext('Se agregará la canción a') }}
                    <strong>{{ Object.keys(getSelectedOperations()).length }}</strong>
                    {{ $gettext('playlist(s)') }}
                </div>
            </div>
        </div>

        <template #modal-footer="slotProps">
            <button
                type="button"
                class="btn btn-secondary"
                @click="slotProps.close"
            >
                {{ $gettext('Cancelar') }}
            </button>
            <button
                type="button"
                class="btn btn-primary"
                :disabled="processing || Object.keys(getSelectedOperations()).length === 0"
                @click="submitBatch"
            >
                <span v-if="processing">
                    <span class="spinner-border spinner-border-sm me-1" />
                    {{ $gettext('Procesando...') }}
                </span>
                <span v-else>
                    {{ $gettext('Agregar a Playlists') }}
                </span>
            </button>
        </template>
    </modal>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useTranslate } from '~/vendor/gettext';
import { useAxios } from '~/vendor/axios';
import { useNotify } from '~/components/Common/Toasts/useNotify';
import Modal from '~/components/Common/Modal.vue';
import { getApiUrl } from '~/router';

interface Media {
    id: number;
    title: string;
    artist: string;
    storage_location_id: number;
}

interface Playlist {
    id: number;
    name: string;
    num_songs: number;
}

interface Station {
    id: number;
    name: string;
    playlists: Playlist[];
    media_storage_location_id: number;
}

const { $gettext } = useTranslate();
const { axios } = useAxios();
const { notifySuccess, notifyError } = useNotify();

const $modal = ref<InstanceType<typeof Modal> | null>(null);
const loading = ref(false);
const processing = ref(false);
const selectedMedia = ref<Media | null>(null);
const stations = ref<Station[]>([]);
const selectedStations = ref<number[]>([]);
const selectedPlaylists = ref<Record<number, number[]>>({});

const open = async (media: Media) => {
    selectedMedia.value = media;
    selectedStations.value = [];
    selectedPlaylists.value = {};
    
    $modal.value?.show();
    
    await loadStations();
};

const loadStations = async () => {
    loading.value = true;
    try {
        // Get all stations
        const stationsUrl = getApiUrl('/frontend/dashboard/stations');
        const { data: stationsData } = await axios.get(stationsUrl);
        
        // For each station, get playlists
        const stationsWithPlaylists = await Promise.all(
            stationsData.rows.map(async (stationNp: any) => {
                const station = stationNp.station;
                try {
                    const playlistsUrl = getApiUrl(`/station/${station.id}/playlists`);
                    const { data: playlistsData } = await axios.get(playlistsUrl);
                    
                    return {
                        id: station.id,
                        name: station.name,
                        media_storage_location_id: station.media_storage_location_id,
                        playlists: playlistsData
                    };
                } catch (error) {
                    console.error(`Error loading playlists for station ${station.id}:`, error);
                    return {
                        id: station.id,
                        name: station.name,
                        media_storage_location_id: station.media_storage_location_id,
                        playlists: []
                    };
                }
            })
        );
        
        stations.value = stationsWithPlaylists;
    } catch (error) {
        console.error('Error loading stations:', error);
        notifyError($gettext('Error al cargar las estaciones'));
    } finally {
        loading.value = false;
    }
};

const onStationToggle = (stationId: number) => {
    if (!selectedStations.value.includes(stationId)) {
        // If unchecked, remove all playlists for this station
        delete selectedPlaylists.value[stationId];
    } else {
        // If checked, initialize empty array for playlists
        if (!selectedPlaylists.value[stationId]) {
            selectedPlaylists.value[stationId] = [];
        }
    }
};

const getSelectedOperations = () => {
    const operations: Record<string, any> = {};
    
    selectedStations.value.forEach(stationId => {
        const playlists = selectedPlaylists.value[stationId] || [];
        playlists.forEach(playlistId => {
            const key = `${stationId}-${playlistId}`;
            operations[key] = {
                station_id: stationId,
                playlist_id: playlistId,
                media_id: selectedMedia.value?.id
            };
        });
    });
    
    return operations;
};

const submitBatch = async () => {
    const operations = Object.values(getSelectedOperations());
    
    if (operations.length === 0) {
        notifyError($gettext('Debes seleccionar al menos una playlist'));
        return;
    }
    
    processing.value = true;
    
    try {
        const url = getApiUrl('/frontend/batch/add-media-to-playlists');
        const { data } = await axios.post(url, { operations });
        
        if (data.success) {
            notifySuccess(
                $gettext('Canción agregada exitosamente a %{count} playlist(s)', {
                    count: data.success_count
                })
            );
            $modal.value?.hide();
        } else {
            notifyError(
                $gettext('Se completaron %{success} de %{total} operaciones', {
                    success: data.success_count,
                    total: data.total
                })
            );
        }
    } catch (error) {
        console.error('Error in batch operation:', error);
        notifyError($gettext('Error al agregar la canción a las playlists'));
    } finally {
        processing.value = false;
    }
};

const onHidden = () => {
    selectedMedia.value = null;
    selectedStations.value = [];
    selectedPlaylists.value = {};
};

defineExpose({
    open
});
</script>

<style scoped>
.station-section {
    border: 1px solid var(--bs-border-color);
    border-radius: 0.375rem;
    padding: 1rem;
    background: var(--bs-body-bg);
}

.station-header {
    cursor: pointer;
}

.playlists-list {
    max-height: 200px;
    overflow-y: auto;
}

.form-check {
    padding: 0.25rem 0;
}
</style>
