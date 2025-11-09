<template>
    <!-- Fullscreen Display Mode (for TV/Projector screens) -->
    <fullscreen-display
        v-if="isFullscreenMode"
        :current-song="currentSong"
        :station-short-name="stationShortName"
        :display-mode="displayMode"
        :radio-player-props="radioPlayerProps"
        :is-loading="!currentSong"
    />

    <!-- Loading state for fullscreen -->
    <div
        v-else-if="isFullscreenMode && !currentNp"
        class="fullscreen-display"
        style="display: flex; align-items: center; justify-content: center; height: 100vh; background: #1a1a2e;"
    >
        <div class="text-center">
            <div class="spinner-border text-light mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-light">Cargando...</p>
        </div>
    </div>

    <!-- Normal Player Mode -->
    <div
        v-else
        class="public-page"
    >
        <div class="card">
            <div class="card-body">
                <h2 class="card-title mb-3">
                    {{ stationName }}
                </h2>

                <div class="stations nowplaying">
                    <radio-player
                        v-bind="props"
                        @np_updated="onNowPlayingUpdate"
                    />
                </div>
            </div>
            <div class="card-body buttons pt-0">
                <a
                    class="btn btn-link text-secondary"
                    @click.prevent="openSongHistoryModal"
                >
                    <icon :icon="IconHistory" />
                    <span>
                        {{ $gettext('Song History') }}
                    </span>
                </a>
                <a
                    v-if="enableRequests"
                    class="btn btn-link text-secondary"
                    @click.prevent="openRequestModal"
                >
                    <icon :icon="IconHelp" />
                    <span>
                        {{ $gettext('Request Song') }}
                    </span>
                </a>
                <a
                    class="btn btn-link text-secondary"
                    :href="downloadPlaylistUri"
                >
                    <icon :icon="IconDownload" />
                    <span>
                        {{ $gettext('Playlist') }}
                    </span>
                </a>
            </div>
        </div>
    </div>

    <song-history-modal
        v-if="!isFullscreenMode"
        ref="$songHistoryModal"
        :show-album-art="showAlbumArt"
        :history="history"
    />

    <request-modal
        v-if="enableRequests && !isFullscreenMode"
        ref="$requestModal"
        v-bind="props"
    />

    <lightbox
        v-if="!isFullscreenMode"
        ref="$lightbox"
    />
</template>

<script setup lang="ts">
import SongHistoryModal from "~/components/Public/FullPlayer/SongHistoryModal.vue";
import RequestModal from "~/components/Public/FullPlayer/RequestModal.vue";
import Icon from "~/components/Common/Icons/Icon.vue";
import RadioPlayer, {PlayerProps} from "~/components/Public/Player.vue";
import FullscreenDisplay from "~/components/Public/FullscreenDisplay.vue";
import {computed, shallowRef, useTemplateRef, ref, watch} from "vue";
import Lightbox from "~/components/Common/Lightbox.vue";
import {useProvideLightbox} from "~/vendor/lightbox";
import {IconDownload, IconHelp, IconHistory} from "~/components/Common/Icons/icons.ts";
import {RequestsProps} from "~/components/Public/Requests.vue";
import {ApiNowPlaying, ApiNowPlayingSongHistory} from "~/entities/ApiInterfaces.ts";

interface FullPlayerProps extends PlayerProps, RequestsProps {
    stationName: string,
    enableRequests?: boolean,
    downloadPlaylistUri: string
}

const props = withDefaults(
    defineProps<FullPlayerProps>(),
    {
        enableRequests: false
    }
);

const history = shallowRef<ApiNowPlayingSongHistory[]>([]);
const currentNp = ref<ApiNowPlaying | null>(null);
const audioElement = ref<HTMLAudioElement | null>(null);

// Detect if we should show fullscreen mode
const isFullscreenMode = computed(() => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.has('display') || urlParams.has('fullscreen');
});

const stationShortName = computed(() => {
    return currentNp.value?.station?.shortcode || '';
});

const displayMode = computed<'videoclips' | 'waveform'>(() => {
    return (currentNp.value?.station as any)?.display_mode || 'waveform';
});

const currentSong = computed(() => {
    // Validación completa: asegurar que tenemos datos reales de now_playing
    if (!currentNp.value?.now_playing) {
        console.error('🔴 FullPlayer: No now_playing data');
        return null;
    }
    
    console.error('🟢 FullPlayer: now_playing exists, checking sh_id...');

    const nowPlaying = currentNp.value.now_playing;
    
    // Verificar que sh_id > 0 indica que hay una canción real reproduciéndose
    if (!nowPlaying.sh_id || nowPlaying.sh_id === 0) {
        console.error('🟡 FullPlayer: sh_id is 0 (no active song)');
        return null;
    }

    console.error('🟢 FullPlayer: sh_id valid:', nowPlaying.sh_id);

    // Verificar que el objeto song existe y tiene datos
    if (!nowPlaying.song || !nowPlaying.song.title) {
        console.error('🔴 FullPlayer: No song data or title');
        return null;
    }
    
    console.error('🟢 FullPlayer: song exists, creating currentSong object...');
    
    const song = {
        id: nowPlaying.song.id || nowPlaying.sh_id?.toString() || '',
        title: nowPlaying.song.title || '',
        artist: nowPlaying.song.artist || '',
        video_url: (nowPlaying.song as any).video_url || null
    };
    
    console.error('✅ FullPlayer: currentSong =', song);
    console.error('✅ FullPlayer: video_url =', song.video_url);
    
    return song;
});

// Props to pass to the RadioPlayer inside FullscreenDisplay
// Use the original nowPlayingProps from backend, add autoplay for fullscreen mode
const radioPlayerProps = computed(() => {
    return {
        nowPlayingProps: props.nowPlayingProps, // Keep original {stationShortName, useStatic, useSse}
        showAlbumArt: props.showAlbumArt,
        autoplay: isFullscreenMode.value // Enable autoplay in fullscreen mode
    };
});

const onNowPlayingUpdate = (newNowPlaying: ApiNowPlaying) => {
    console.error('📡 FullPlayer: NowPlaying updated =', newNowPlaying);
    history.value = newNowPlaying?.song_history;
    currentNp.value = newNowPlaying;
    
    // Forzar evaluación del computed en fullscreen mode
    if (isFullscreenMode.value) {
        console.error('📺 FullPlayer: Fullscreen mode active, evaluating currentSong...');
        const song = currentSong.value;
        console.error('📺 FullPlayer: currentSong evaluated =', song);
    }
}

// Get audio element reference for visualization
function getAudioElement() {
    const audioElements = document.querySelectorAll('audio');
    if (audioElements.length > 0) {
        audioElement.value = audioElements[0] as HTMLAudioElement;
    }
}

watch(currentNp, () => {
    setTimeout(getAudioElement, 1000);
}, { immediate: true });

const $songHistoryModal = useTemplateRef('$songHistoryModal');

const openSongHistoryModal = () => {
    $songHistoryModal.value?.open();
}

const $requestModal = useTemplateRef('$requestModal');

const openRequestModal = () => {
    $requestModal.value?.open();
}

const $lightbox = useTemplateRef('$lightbox');

useProvideLightbox($lightbox);
</script>
