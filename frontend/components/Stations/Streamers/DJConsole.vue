<template>
    <section
        role="region"
        class="dj-console-wrapper"
    >
        <div class="alert alert-info mb-3">
            <h5 class="alert-heading">
                <icon :icon="IconInfo" />
                {{ $gettext('Mesa Virtual de DJ - Web DJ') }}
            </h5>
            <p class="mb-0">
                {{ $gettext('Control your station live from this interface or connect professional DJ software using the credentials below.') }}
            </p>
        </div>

        <div class="row g-3">
            <div class="col-md-4 mb-sm-4">
                <settings-panel :station-name="stationName" />
            </div>

            <div class="col-md-8">
                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <microphone-panel />
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <mixer-panel />
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6 mb-sm-4">
                        <playlist-panel id="playlist_1" />
                    </div>

                    <div class="col-md-6">
                        <playlist-panel id="playlist_2" />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import MixerPanel from "~/components/Public/WebDJ/MixerPanel.vue";
import MicrophonePanel from "~/components/Public/WebDJ/MicrophonePanel.vue";
import PlaylistPanel from "~/components/Public/WebDJ/PlaylistPanel.vue";
import SettingsPanel from "~/components/Public/WebDJ/SettingsPanel.vue";
import Icon from "~/components/Common/Icons/Icon.vue";
import {IconInfo} from "~/components/Common/Icons/icons";
import {useProvideWebDjNode} from "~/components/Public/WebDJ/useWebDjNode";
import {useProvideWebcaster, WebcasterProps} from "~/components/Public/WebDJ/useWebcaster";
import {useProvideMixer} from "~/components/Public/WebDJ/useMixerValue";
import {useProvidePassthroughSync} from "~/components/Public/WebDJ/usePassthroughSync";

interface DJConsoleProps extends WebcasterProps {
    stationName: string | null
}

const props = defineProps<DJConsoleProps>();

const webcaster = useProvideWebcaster(props);

useProvideWebDjNode(webcaster);

useProvideMixer(1.0);

useProvidePassthroughSync('');
</script>

<style scoped>
.dj-console-wrapper {
    padding: 1rem 0;
}
</style>
