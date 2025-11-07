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
                <div class="col-md-4">
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
                            @input="onFilterChange"
                        >
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">{{ $gettext('Género') }}</label>
                    <select
                        v-model="filters.genre"
                        class="form-select"
                        @change="onFilterChange"
                    >
                        <option value="">
                            {{ $gettext('Todos los géneros') }}
                        </option>
                        <option
                            v-for="genre in availableGenres"
                            :key="genre"
                            :value="genre"
                        >
                            {{ genre }}
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">{{ $gettext('Artista') }}</label>
                    <select
                        v-model="filters.artist"
                        class="form-select"
                        @change="onFilterChange"
                    >
                        <option value="">
                            {{ $gettext('Todos los artistas') }}
                        </option>
                        <option
                            v-for="artist in availableArtists"
                            :key="artist"
                            :value="artist"
                        >
                            {{ artist }}
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">{{ $gettext('Popularidad') }}</label>
                    <select
                        v-model="filters.popularity"
                        class="form-select"
                        @change="onFilterChange"
                    >
                        <option value="">
                            {{ $gettext('Todas') }}
                        </option>
                        <option value="high">
                            {{ $gettext('Alta') }}
                        </option>
                        <option value="medium">
                            {{ $gettext('Media') }}
                        </option>
                        <option value="low">
                            {{ $gettext('Baja') }}
                        </option>
                    </select>
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
                <div>
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
                <template #cell(art)="{ item }">
                    <img
                        v-if="item.art"
                        :src="item.art"
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
                            :icon="IconMusic"
                            class="text-white"
                        />
                    </div>
                </template>

                <template #cell(title)="{ item }">
                    <div>
                        <div class="fw-bold">
                            {{ item.title }}
                        </div>
                        <div class="text-muted small">
                            {{ item.artist }}
                        </div>
                    </div>
                </template>

                <template #cell(album)="{ item }">
                    <div class="small">
                        {{ item.album || '-' }}
                    </div>
                </template>

                <template #cell(genre)="{ item }">
                    <span
                        v-if="item.genre"
                        class="badge text-bg-secondary"
                    >
                        {{ item.genre }}
                    </span>
                </template>

                <template #cell(play_count)="{ item }">
                    <div class="text-center">
                        <div class="small">
                            {{ item.play_count || 0 }}
                        </div>
                        <div
                            v-if="item.play_count"
                            class="progress"
                            style="height: 3px;"
                        >
                            <div
                                class="progress-bar"
                                :class="getPopularityClass(item.play_count)"
                                :style="{width: getPopularityPercent(item.play_count) + '%'}"
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
import {ref, computed, onMounted} from 'vue';
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
    IconMusic,
    IconPlaylist
} from '~/components/Common/Icons/icons.ts';
import {useNotify} from '~/functions/useNotify.ts';
import {usePlayerStore} from '~/functions/usePlayerStore.ts';
import {useDebounceFn} from '@vueuse/core';

interface MediaFile {
    unique_id: string;
    id: number;
    title: string;
    artist: string;
    album: string | null;
    genre: string | null;
    art: string | null;
    play_count: number;
    links: {
        play: string;
        self: string;
    };
}

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

const filters = ref({
    search: '',
    genre: '',
    artist: '',
    popularity: ''
});

const availableGenres = ref<string[]>([]);
const availableArtists = ref<string[]>([]);
const playlists = ref<Playlist[]>([]);
const activePlaylistId = ref<number | string>('');
const selectedItems = ref<MediaFile[]>([]);
const adding = ref(false);

const $dataTable = ref();
const $batchModal = ref<InstanceType<typeof BatchPlaylistModal> | null>(null);

// Cargar playlists disponibles
const loadPlaylists = async () => {
    try {
        const {data} = await axios.get(playlistsUrl.value);
        playlists.value = data;
    } catch (error) {
        console.error('Error al cargar playlists:', error);
    }
};

// Cargar filtros (géneros y artistas únicos)
const loadFilters = async () => {
    try {
        const {data} = await axios.get(filesUrl.value, {
            params: {
                searchPhrase: '',
                currentDir: ''
            }
        });

        // Extraer géneros y artistas únicos
        const genres = new Set<string>();
        const artists = new Set<string>();

        data.forEach((item: any) => {
            if (item.media) {
                if (item.media.genre) genres.add(item.media.genre);
                if (item.media.artist) artists.add(item.media.artist);
            }
        });

        availableGenres.value = Array.from(genres).sort();
        availableArtists.value = Array.from(artists).sort();
    } catch (error) {
        console.error('Error al cargar filtros:', error);
    }
};

// Provider de datos con filtros
const catalogoItemProvider = async (options: any) => {
    try {
        const params: any = {
            searchPhrase: filters.value.search,
            currentDir: ''
        };

        const {data} = await axios.get(filesUrl.value, {params});

        // Filtrar solo archivos de media
        let mediaFiles = data
            .filter((item: any) => item.media)
            .map((item: any) => ({
                unique_id: item.media.unique_id,
                id: item.media.id,
                title: item.media.title,
                artist: item.media.artist,
                album: item.media.album,
                genre: item.media.genre,
                art: item.media.art,
                play_count: item.media.play_count || 0,
                links: item.media.links
            }));

        // Aplicar filtros adicionales
        if (filters.value.genre) {
            mediaFiles = mediaFiles.filter((m: MediaFile) => m.genre === filters.value.genre);
        }

        if (filters.value.artist) {
            mediaFiles = mediaFiles.filter((m: MediaFile) => m.artist === filters.value.artist);
        }

        if (filters.value.popularity) {
            const maxPlayCount = Math.max(...mediaFiles.map((m: MediaFile) => m.play_count));
            mediaFiles = mediaFiles.filter((m: MediaFile) => {
                const percent = maxPlayCount > 0 ? (m.play_count / maxPlayCount) * 100 : 0;
                if (filters.value.popularity === 'high') return percent > 66;
                if (filters.value.popularity === 'medium') return percent > 33 && percent <= 66;
                if (filters.value.popularity === 'low') return percent <= 33;
                return true;
            });
        }

        return mediaFiles;
    } catch (error) {
        console.error('Error al cargar catálogo:', error);
        return [];
    }
};

const onFilterChange = useDebounceFn(() => {
    $dataTable.value?.refresh();
}, 300);

const onRowSelected = (items: MediaFile[]) => {
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
            'files': selectedItems.value.map(item => item.unique_id)
        });

        notifySuccess(
            $gettext('%{count} canción(es) añadida(s) a la playlist correctamente.', {
                count: selectedItems.value.length
            })
        );

        selectedItems.value = [];
        $dataTable.value?.clearSelected();
    } catch (error) {
        console.error('Error al añadir a playlist:', error);
        notifyError($gettext('Error al añadir canciones a la playlist.'));
    } finally {
        adding.value = false;
    }
};

const addSingleToPlaylist = async (item: MediaFile) => {
    if (!activePlaylistId.value) {
        notifyError($gettext('Selecciona una playlist primero.'));
        return;
    }

    adding.value = true;

    try {
        const batchUrl = getStationApiUrl('/files/batch');
        await axios.put(batchUrl.value, {
            'do': 'playlist',
            'playlists': [activePlaylistId.value],
            'files': [item.unique_id]
        });

        notifySuccess($gettext('Canción añadida a la playlist correctamente.'));
    } catch (error) {
        console.error('Error al añadir canción:', error);
        notifyError($gettext('Error al añadir la canción a la playlist.'));
    } finally {
        adding.value = false;
    }
};

const playSong = (item: MediaFile) => {
    playerStore.toggle({
        url: item.links.play,
        title: `${item.title} - ${item.artist}`,
        isStream: false
    });
};

const openBatchModal = (item: MediaFile) => {
    $batchModal.value?.open({
        id: item.id,
        title: item.title,
        artist: item.artist,
        storage_location_id: 0 // This will be populated from the API response
    });
};

const catalogoFields = computed<DataTableField<MediaFile>[]>(() => [
    {
        key: 'art',
        label: '',
        sortable: false,
        class: 'shrink'
    },
    {
        key: 'title',
        label: $gettext('Canción'),
        sortable: true
    },
    {
        key: 'album',
        label: $gettext('Álbum'),
        sortable: true
    },
    {
        key: 'genre',
        label: $gettext('Género'),
        sortable: true,
        class: 'shrink'
    },
    {
        key: 'play_count',
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
    loadFilters();
});
</script>

<style scoped>
.progress {
    margin-top: 2px;
}
</style>
