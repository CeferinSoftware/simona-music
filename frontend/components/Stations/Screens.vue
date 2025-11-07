<template>
    <screens-manager :list-url="listUrl" />
</template>

<script setup lang="ts">
import ScreensManager from '~/components/Stations/ScreensManager.vue';
import {getStationApiUrl} from '~/router';

const listUrl = getStationApiUrl('/screens');
</script>
