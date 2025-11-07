<template>
    <q-r-request-form :stations="[station]" />
</template>

<script setup lang="ts">
import QRRequestForm from '~/components/Public/Requests/QRRequestForm.vue';

interface Station {
    id: number;
    name: string;
}

defineProps<{
    station: Station;
}>();
</script>
