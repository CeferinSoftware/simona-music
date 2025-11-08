<template>
    <card-page header-id="hdr_catalogo_terraza">
        <template #header="{id}">
            <div class="d-flex align-items-center">
                <h3
                    :id="id"
                    class="card-title my-0"
                >
                    {{ $gettext('Catálogo Musical') }}
                </h3>
            </div>
        </template>

        <div class="card-body">
            <!-- Filtros -->
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label">{{ $gettext('Buscar') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <icon :icon="IconSearch" />
                        </span>
                        <input
                            v-model="filters.search"
                            type="text"
                            class="form-control"
                            :placeholder="$gettext('Título, artista o álbum')"
                        >
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="text-muted">
                        {{ $gettext('Playlist Activa:') }}
                    </span>
                    <select
                        v-model="activePlaylistId"
                        class="form-select form-select-sm d-inline-block ms-2"
                        style="width: auto;"
                    >
                        <option value="">
                            {{ $gettext('Seleccionar playlist') }}
                        </option>
                        <option
                            v-for="playlist in playlists"
                            :key="playlist.id"
                            :value="playlist.id"
                        >
                            {{ playlist.name }}
                        </option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button
                        type="button"
                        class="btn btn-sm btn-secondary"
                        @click="searchNow"
                    >
                        <icon :icon="IconSearch" />
                        {{ $gettext('Buscar') }}
                    </button>
                    <button
                        v-if="selectedItems.length > 0"
                        type="button"
                        class="btn btn-sm btn-primary"
                        :disabled="!activePlaylistId || adding"
                        @click="addToPlaylist"
                    >
                        <icon :icon="IconAdd" />
                        {{ $gettext('Añadir %{count} canción(es) a playlist', {count: selectedItems.length}) }}
                    </button>
                </div>
            </div>

            <!-- Tabla de Resultados -->
            <data-table
                id="catalogo_terraza_table"
                ref="$dataTable"
                selectable
                paginated
                :fields="catalogoFields"
                :provider="catalogoItemProvider"
                :per-page="20"
                @row-selected="onRowSelected"
            >
                <template #cell(media.art)="{ item }">
                    <img
                        v-if="item.media?.art"
                        :src="item.media.art"
                        alt="Album Art"
                        class="rounded"
                        style="width: 50px; height: 50px; object-fit: cover;"
                    >
                    <div
                        v-else
                        class="rounded bg-secondary d-flex align-items-center justify-content-center"
                        style="width: 50px; height: 50px;"
                    >
                        <icon
                            :icon="IconMusicNote"
                            class="text-white"
                        />
                    </div>
                </template>

                <template #cell(media.title)="{ item }">
                    <div v-if="item.media">
                        <div class="fw-bold">
                            {{ item.media.title }}
                        </div>
                        <div class="text-muted small">
                            {{ item.media.artist }}
                        </div>
                    </div>
                </template>

                <template #cell(media.album)="{ item }">
                    <div class="small">
                        {{ item.media?.album || '-' }}
                    </div>
                </template>

                <template #cell(media.genre)="{ item }">
                    <span
                        v-if="item.media?.genre"
                        class="badge text-bg-secondary"
                    >
                        {{ item.media.genre }}
                    </span>
                </template>

                <template #cell(media.play_count)="{ item }">
                    <div class="text-center" v-if="item.media">
                        <div class="small">
                            {{ item.media.play_count || 0 }}
                        </div>
                        <div
                            v-if="item.media.play_count"
                            class="progress"
                            style="height: 3px;"
                        >
                            <div
                                class="progress-bar"
                                :class="getPopularityClass(item.media.play_count)"
                                :style="{width: getPopularityPercent(item.media.play_count) + '%'}"
                            />
                        </div>
                    </div>
                </template>

                <template #cell(actions)="{ item }">
                    <div class="btn-group btn-group-sm">
                        <button
                            type="button"
                            class="btn btn-outline-primary"
                            :disabled="!activePlaylistId || adding"
                            @click="addSingleToPlaylist(item)"
                            :title="$gettext('Agregar a playlist activa')"
                        >
                            <icon :icon="IconAdd" />
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-success"
                            @click="openBatchModal(item)"
                            :title="$gettext('Agregar a múltiples playlists')"
                        >
                            <icon :icon="IconPlaylist" />
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            @click="playSong(item)"
                            :title="$gettext('Reproducir')"
                        >
                            <icon :icon="IconPlay" />
                        </button>
                    </div>
                </template>
            </data-table>
        </div>

        <batch-playlist-modal ref="$batchModal" />
    </card-page>
</template>

<script setup lang="ts">
import {ref, computed, onMounted, useTemplateRef} from 'vue';
import {useAxios} from '~/vendor/axios';
import {useTranslate} from '~/vendor/gettext';
import {getStationApiUrl} from '~/router.ts';
import CardPage from '~/components/Common/CardPage.vue';
import DataTable, {DataTableField} from '~/components/Common/DataTable.vue';
import Icon from '~/components/Common/Icons/Icon.vue';
import BatchPlaylistModal from '~/components/Stations/BatchPlaylistModal.vue';
import {
    IconSearch,
    IconAdd,
    IconPlay,
    IconMusicNote,
    IconPlaylist
} from '~/components/Common/Icons/icons.ts';
import {useNotify} from '~/components/Common/Toasts/useNotify.ts';
import {usePlayerStore} from '~/functions/usePlayerStore.ts';
import {useApiItemProvider} from '~/functions/useApiItemProvider';
import {queryKeyWithStation, QueryKeys} from '~/functions/queryKeys';
import {FileListRequired} from '~/entities/StationMedia.ts';

type MediaRow = FileListRequired;

interface Playlist {
    id: number;
    name: string;
}

const {$gettext} = useTranslate();
const {axios} = useAxios();
const {notifySuccess, notifyError} = useNotify();
const playerStore = usePlayerStore();

const filesUrl = getStationApiUrl('/files');
const playlistsUrl = getStationApiUrl('/playlists');
const currentDir = ref('');

const filters = ref({
    search: ''
});

const playlists = ref<Playlist[]>([]);
const activePlaylistId = ref<number | string>('');
const selectedItems = ref<MediaRow[]>([]);
const adding = ref(false);

const $dataTable = useTemplateRef('$dataTable');
const $batchModal = useTemplateRef('$batchModal');

// Cargar playlists disponibles
const loadPlaylists = async () => {
    try {
        const {data} = await axios.get(playlistsUrl.value);
        playlists.value = data;
    } catch (error) {
        console.error('Error al cargar playlists:', error);
    }
};

// Provider de datos usando useApiItemProvider (igual que Media.vue)
const catalogoItemProvider = useApiItemProvider<MediaRow>(
    filesUrl,
    queryKeyWithStation([QueryKeys.StationMedia, 'files', currentDir]),
    {
        staleTime: 2 * 60 * 1000
    },
    (config) => {
        config.params.currentDirectory = currentDir.value;
        config.params.searchPhrase = filters.value.search || '';
        return config;
    }
);

const searchNow = () => {
    console.log('Buscando con filtros:', filters.value);
    catalogoItemProvider.refresh();
};

const onRowSelected = (items: MediaRow[]) => {
    selectedItems.value = items;
};

const getPopularityPercent = (playCount: number): number => {
    // Calcular porcentaje relativo (esto es simplificado, idealmente se calcularía contra el máximo de la estación)
    return Math.min((playCount / 100) * 100, 100);
};

const getPopularityClass = (playCount: number): string => {
    const percent = getPopularityPercent(playCount);
    if (percent > 66) return 'bg-success';
    if (percent > 33) return 'bg-warning';
    return 'bg-info';
};

const addToPlaylist = async () => {
    if (!activePlaylistId.value || selectedItems.value.length === 0) {
        return;
    }

    adding.value = true;

    try {
        const batchUrl = getStationApiUrl('/files/batch');
        await axios.put(batchUrl.value, {
            'do': 'playlist',
            'playlists': [activePlaylistId.value],
            'files': selectedItems.value
                .filter(item => item.media !== null)
                .map(item => item.media!.unique_id)
        });

        notifySuccess(
            $gettext('%{count} canción(es) añadida(s) a la playlist correctamente.', {
                count: selectedItems.value.length
            })
        );

        selectedItems.value = [];
        // Refrescar tabla para limpiar selección
        catalogoItemProvider.refresh();
    } catch (error) {
        console.error('Error al añadir a playlist:', error);
        notifyError($gettext('Error al añadir canciones a la playlist.'));
    } finally {
        adding.value = false;
    }
};

const addSingleToPlaylist = async (item: MediaRow) => {
    if (!activePlaylistId.value || !item.media) {
        notifyError($gettext('Selecciona una playlist primero.'));
        return;
    }

    adding.value = true;

    try {
        const batchUrl = getStationApiUrl('/files/batch');
        await axios.put(batchUrl.value, {
            'do': 'playlist',
            'playlists': [activePlaylistId.value],
            'files': [item.media.unique_id]
        });

        notifySuccess($gettext('Canción añadida a la playlist correctamente.'));
    } catch (error) {
        console.error('Error al añadir canción:', error);
        notifyError($gettext('Error al añadir la canción a la playlist.'));
    } finally {
        adding.value = false;
    }
};

const playSong = (item: MediaRow) => {
    if (!item.media) return;
    
    playerStore.toggle({
        url: item.media.links.play,
        title: `${item.media.title} - ${item.media.artist}`,
        isStream: false
    });
};

const openBatchModal = (item: MediaRow) => {
    if (!item.media) return;
    
    $batchModal.value?.open({
        id: item.media.id,
        title: item.media.title,
        artist: item.media.artist,
        storage_location_id: item.media.storage_location_id
    });
};

const catalogoFields = computed<DataTableField<MediaRow>[]>(() => [
    {
        key: 'media.art',
        label: '',
        sortable: false,
        class: 'shrink'
    },
    {
        key: 'media.title',
        label: $gettext('Canción'),
        sortable: true
    },
    {
        key: 'media.album',
        label: $gettext('Álbum'),
        sortable: true
    },
    {
        key: 'media.genre',
        label: $gettext('Género'),
        sortable: true,
        class: 'shrink'
    },
    {
        key: 'media.play_count',
        label: $gettext('Popularidad'),
        sortable: true,
        class: 'shrink text-center'
    },
    {
        key: 'actions',
        label: $gettext('Acciones'),
        sortable: false,
        class: 'shrink'
    }
]);

onMounted(() => {
    loadPlaylists();
});
</script>

<style scoped>
.progress {
    margin-top: 2px;
}
</style>
