<template>
    <!-- Fullscreen Display Mode (for TV/Projector screens) -->
    <fullscreen-display
        v-if="isFullscreenMode"
        :current-song="currentSong"
        :station-short-name="stationShortName"
        :display-mode="displayMode"
        :now-playing-props="nowPlayingProps"
    />

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
    if (!currentNp.value?.now_playing?.song) return null;
    
    return {
        id: currentNp.value.now_playing.song.id || '',
        title: currentNp.value.now_playing.song.title || '',
        artist: currentNp.value.now_playing.song.artist || '',
        video_url: (currentNp.value.now_playing.song as any).video_url || null
    };
});

const onNowPlayingUpdate = (newNowPlaying: ApiNowPlaying) => {
    history.value = newNowPlaying?.song_history;
    currentNp.value = newNowPlaying;
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
