<template>
    <footer id="footer">
        {{ $gettext('Powered by') }}
        <a
            href="https://simonamusic.net/"
            target="_blank"
        >Simona Music</a>
        &bull;
        <span v-html="version"/>
        &bull;
        <span v-html="platform"/><br>
        {{ $gettext('Visit our website') }}
        <a
            href="https://simonamusic.net/"
            target="_blank"
        >
            {{ $gettext('simonamusic.net') }}
        </a>
    </footer>
</template>

<script setup lang="ts">
import {useAzuraCastDashboardGlobals} from "~/vendor/azuracast.ts";

const {version, platform} = useAzuraCastDashboardGlobals();
</script>
