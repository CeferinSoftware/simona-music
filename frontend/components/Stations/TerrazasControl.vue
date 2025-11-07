<template>
    <card-page header-id="hdr_terrazas_control">
        <template #header="{id}">
            <div class="d-flex align-items-center">
                <h3
                    :id="id"
                    class="card-title my-0"
                >
                    {{ $gettext('Control de Terrazas') }}
                </h3>
            </div>
        </template>

        <audio-visualizer-panel
            ref="visualizerRef"
            :stream-url="currentStreamUrl"
        />

        <div class="card-body">
            <data-table
                id="terrazas_control_table"
                :fields="terrazasFields"
                :provider="terrazasItemProvider"
                :responsive="true"
                :show-toolbar="false"
                :hide-on-loading="false"
            >
                <template #cell(status)="{ item }">
                    <span
                        v-if="item.is_online"
                        class="badge text-bg-success"
                    >
                        {{ $gettext('En Línea') }}
                    </span>
                    <span
                        v-else
                        class="badge text-bg-danger"
                    >
                        {{ $gettext('Fuera de Línea') }}
                    </span>
                </template>

                <template #cell(station)="{ item }">
                    <div>
                        <div class="h6 mb-1">
                            {{ item.station.name }}
                        </div>
                        <div class="small text-muted">
                            <span v-if="item.live?.is_live" class="badge text-bg-info me-1">
                                <icon :icon="IconMic" class="sm" />
                                {{ $gettext('En Vivo') }}
                            </span>
                            <span class="me-2">
                                {{ item.station.backend }}
                            </span>
                            <span v-if="item.station.is_public">
                                <icon :icon="IconPublic" class="sm" />
                                {{ $gettext('Público') }}
                            </span>
                        </div>
                    </div>
                </template>

                <template #cell(now_playing)="{ item }">
                    <div
                        v-if="!item.is_online"
                        class="text-muted"
                    >
                        {{ $gettext('Estación fuera de línea') }}
                    </div>
                    <div
                        v-else-if="item.now_playing?.song?.title"
                        class="d-flex align-items-center"
                    >
                        <div
                            v-if="item.now_playing.song.art"
                            class="flex-shrink-0 me-2"
                        >
                            <img
                                :src="item.now_playing.song.art"
                                alt="Album Art"
                                class="rounded"
                                style="width: 40px; height: 40px; object-fit: cover;"
                            >
                        </div>
                        <div class="flex-fill">
                            <div class="fw-bold">
                                {{ item.now_playing.song.title }}
                            </div>
                            <div class="text-muted small">
                                {{ item.now_playing.song.artist }}
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="text-muted"
                    >
                        {{ item.now_playing?.song?.text ?? $gettext('Sin información') }}
                    </div>
                </template>

                <template #cell(listeners)="{ item }">
                    <div class="d-flex flex-column align-items-center">
                        <div class="h5 mb-0">
                            {{ item.listeners?.total ?? 0 }}
                        </div>
                        <small class="text-muted">
                            {{ $gettext('oyentes') }}
                        </small>
                    </div>
                </template>

                <template #cell(actions)="{ item }">
                    <div class="d-flex gap-2">
                        <button
                            v-if="item.is_online"
                            type="button"
                            class="btn btn-sm"
                            :class="playingStationId === item.station.id ? 'btn-warning' : 'btn-success'"
                            @click="togglePlayStation(item)"
                        >
                            <icon :icon="playingStationId === item.station.id ? IconPauseCircle : IconPlay" />
                            <span class="d-none d-lg-inline">
                                {{ playingStationId === item.station.id ? $gettext('Pausar') : $gettext('Reproducir') }}
                            </span>
                        </button>
                        
                        <button
                            v-if="item.is_online && !item.live?.is_live && canBroadcast(item.station.id)"
                            type="button"
                            class="btn btn-sm btn-primary"
                            :disabled="processing[item.station.id]"
                            @click="skipSong(item.station.id)"
                        >
                            <icon :icon="IconSkipNext" />
                            <span class="d-none d-lg-inline">{{ $gettext('Siguiente') }}</span>
                        </button>
                        
                        <div class="btn-group">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <icon :icon="IconMenu" />
                                <span class="d-none d-lg-inline ms-1">{{ $gettext('Más') }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <router-link
                                        :to="{
                                            name: 'stations:index',
                                            params: { station_id: item.station.id }
                                        }"
                                        class="dropdown-item"
                                    >
                                        <icon :icon="IconHome" />
                                        {{ $gettext('Dashboard') }}
                                    </router-link>
                                </li>
                                <li v-if="canBroadcast(item.station.id)">
                                    <router-link
                                        :to="{
                                            name: 'stations:playlists:index',
                                            params: { station_id: item.station.id }
                                        }"
                                        class="dropdown-item"
                                    >
                                        <icon :icon="IconPlaylist" />
                                        {{ $gettext('Playlists') }}
                                    </router-link>
                                </li>
                                <li v-if="canBroadcast(item.station.id)">
                                    <router-link
                                        :to="{
                                            name: 'stations:queue',
                                            params: { station_id: item.station.id }
                                        }"
                                        class="dropdown-item"
                                    >
                                        <icon :icon="IconHistory" />
                                        {{ $gettext('Cola de Reproducción') }}
                                    </router-link>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li v-if="item.is_online && canBroadcast(item.station.id)">
                                    <button
                                        class="dropdown-item"
                                        :disabled="processing[item.station.id]"
                                        @click="restartBackend(item.station.id)"
                                    >
                                        <icon :icon="IconRefresh" />
                                        {{ $gettext('Reiniciar Backend') }}
                                    </button>
                                </li>
                                <li v-if="item.is_online && canBroadcast(item.station.id)">
                                    <button
                                        class="dropdown-item"
                                        :disabled="processing[item.station.id]"
                                        @click="restartFrontend(item.station.id)"
                                    >
                                        <icon :icon="IconRefresh" />
                                        {{ $gettext('Reiniciar Frontend') }}
                                    </button>
                                </li>
                                <li v-if="item.live?.is_live && canBroadcast(item.station.id)">
                                    <button
                                        class="dropdown-item text-danger"
                                        :disabled="processing[item.station.id]"
                                        @click="disconnectStreamer(item.station.id)"
                                    >
                                        <icon :icon="IconStop" />
                                        {{ $gettext('Desconectar Streamer') }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </template>
            </data-table>
        </div>
    </card-page>
</template>

<script setup lang="ts">
import {ref, computed} from 'vue';
import {useAxios} from '~/vendor/axios';
import {useTranslate} from '~/vendor/gettext';
import {getApiUrl} from '~/router.ts';
import CardPage from '~/components/Common/CardPage.vue';
import DataTable, {DataTableField} from '~/components/Common/DataTable.vue';
import Icon from '~/components/Common/Icons/Icon.vue';
import AudioVisualizerPanel from '~/components/Stations/AudioVisualizerPanel.vue';
import {
    IconSkipNext,
    IconSettings,
    IconMic,
    IconPublic,
    IconMenu,
    IconHome,
    IconPlaylist,
    IconHistory,
    IconRefresh,
    IconStop,
    IconPlay,
    IconPauseCircle
} from '~/components/Common/Icons/icons.ts';
import {ApiNowPlaying, StationPermissions} from '~/entities/ApiInterfaces.ts';
import {useApiItemProvider} from '~/functions/dataTable/useApiItemProvider.ts';
import {QueryKeys} from '~/entities/Queries.ts';
import {useQueryClient} from '@tanstack/vue-query';
import {userAllowedForStation} from '~/acl.ts';
import {useNotify} from '~/functions/useNotify.ts';

const {$gettext} = useTranslate();
const {axios} = useAxios();
const queryClient = useQueryClient();
const {notifySuccess, notifyError} = useNotify();

const stationsUrl = getApiUrl('/frontend/dashboard/stations');

const processing = ref<Record<number, boolean>>({});
const visualizerRef = ref<InstanceType<typeof AudioVisualizerPanel> | null>(null);
const currentStreamUrl = ref<string>('');
const playingStationId = ref<number | null>(null);

const canBroadcast = (stationId: number): boolean => {
    return userAllowedForStation(stationId, StationPermissions.Broadcasting);
};

const togglePlayStation = async (station: ApiNowPlaying) => {
    if (!station.station.listen_url) {
        notifyError($gettext('No hay URL de transmisión disponible.'));
        return;
    }

    if (playingStationId.value === station.station.id) {
        // Pause current station
        visualizerRef.value?.pause();
        playingStationId.value = null;
        currentStreamUrl.value = '';
    } else {
        // Play new station
        currentStreamUrl.value = station.station.listen_url;
        playingStationId.value = station.station.id;
        
        // Wait for next tick to ensure URL is set
        await new Promise(resolve => setTimeout(resolve, 100));
        visualizerRef.value?.play();
    }
};

const skipSong = async (stationId: number) => {
    processing.value[stationId] = true;

    try {
        const skipUrl = `/api/station/${stationId}/backend/skip`;
        await axios.post(skipUrl);
        
        notifySuccess($gettext('Canción saltada correctamente.'));
        
        // Refrescar datos de la estación
        await queryClient.invalidateQueries({
            queryKey: [QueryKeys.Dashboard, 'stations']
        });
    } catch (error) {
        console.error('Error al saltar canción:', error);
        notifyError($gettext('Error al saltar la canción. Inténtalo de nuevo.'));
    } finally {
        processing.value[stationId] = false;
    }
};

const restartBackend = async (stationId: number) => {
    processing.value[stationId] = true;

    try {
        const restartUrl = `/api/station/${stationId}/backend/restart`;
        await axios.post(restartUrl);
        
        notifySuccess($gettext('Backend reiniciado correctamente.'));
        
        await queryClient.invalidateQueries({
            queryKey: [QueryKeys.Dashboard, 'stations']
        });
    } catch (error) {
        console.error('Error al reiniciar backend:', error);
        notifyError($gettext('Error al reiniciar el backend.'));
    } finally {
        processing.value[stationId] = false;
    }
};

const restartFrontend = async (stationId: number) => {
    processing.value[stationId] = true;

    try {
        const restartUrl = `/api/station/${stationId}/frontend/restart`;
        await axios.post(restartUrl);
        
        notifySuccess($gettext('Frontend reiniciado correctamente.'));
        
        await queryClient.invalidateQueries({
            queryKey: [QueryKeys.Dashboard, 'stations']
        });
    } catch (error) {
        console.error('Error al reiniciar frontend:', error);
        notifyError($gettext('Error al reiniciar el frontend.'));
    } finally {
        processing.value[stationId] = false;
    }
};

const disconnectStreamer = async (stationId: number) => {
    processing.value[stationId] = true;

    try {
        const disconnectUrl = `/api/station/${stationId}/backend/disconnect`;
        await axios.post(disconnectUrl);
        
        notifySuccess($gettext('Streamer desconectado correctamente.'));
        
        await queryClient.invalidateQueries({
            queryKey: [QueryKeys.Dashboard, 'stations']
        });
    } catch (error) {
        console.error('Error al desconectar streamer:', error);
        notifyError($gettext('Error al desconectar el streamer.'));
    } finally {
        processing.value[stationId] = false;
    }
};

const terrazasFields = computed<DataTableField<ApiNowPlaying>[]>(() => [
    {
        key: 'status',
        label: $gettext('Estado'),
        sortable: false,
        class: 'shrink'
    },
    {
        key: 'station',
        label: $gettext('Terraza'),
        sortable: true
    },
    {
        key: 'now_playing',
        label: $gettext('Reproduciendo Ahora'),
        sortable: false
    },
    {
        key: 'listeners',
        label: $gettext('Oyentes'),
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

const terrazasItemProvider = useApiItemProvider<ApiNowPlaying>(
    stationsUrl,
    [QueryKeys.Dashboard, 'stations'],
    {
        refetchInterval: 5 * 1000 // Polling cada 5 segundos
    }
);
</script>

<style scoped>
.btn-group {
    gap: 0.5rem;
}

.dropdown-item icon {
    margin-right: 0.5rem;
}

.d-flex.gap-2 {
    gap: 0.5rem;
}
</style>
