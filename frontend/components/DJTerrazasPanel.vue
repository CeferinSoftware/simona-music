<template>
    <dashboard-no-sidebar>
        <div class="row mb-4">
            <div class="col-12">
                <h1>{{ $gettext('Panel DJ - Terrazas Disponibles') }}</h1>
                <p class="text-muted">
                    {{ $gettext('Selecciona una terraza para comenzar a transmitir como DJ.') }}
                </p>
            </div>
        </div>

        <loading :loading="isLoading">
            <div v-if="terrazas.length === 0" class="alert alert-info">
                {{ $gettext('No tienes terrazas disponibles asignadas para transmitir.') }}
            </div>

            <div v-else class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                <div
                    v-for="terraza in terrazas"
                    :key="terraza.id"
                    class="col"
                >
                    <div class="card h-100 terraza-card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">
                                {{ terraza.name }}
                            </h5>
                            <span
                                class="badge"
                                :class="terraza.is_online ? 'text-bg-success' : 'text-bg-danger'"
                            >
                                {{ terraza.is_online ? $gettext('En Línea') : $gettext('Fuera de Línea') }}
                            </span>
                        </div>
                        <div class="card-body">
                            <p v-if="terraza.description" class="card-text text-muted small mb-3">
                                {{ terraza.description }}
                            </p>
                            
                            <div class="mb-3">
                                <div v-if="terraza.province || terraza.city || terraza.sector" class="mb-2">
                                    <icon :icon="IconLocation" class="me-1 text-muted" />
                                    <span class="small">
                                        <template v-if="terraza.sector">{{ terraza.sector }}, </template>
                                        <template v-if="terraza.city">{{ terraza.city }}, </template>
                                        <template v-if="terraza.province">{{ terraza.province }}</template>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <div class="d-grid gap-2">
                                <a
                                    :href="terraza.webdj_url"
                                    class="btn btn-primary"
                                    :class="{ disabled: !terraza.is_online }"
                                    target="_blank"
                                >
                                    <icon :icon="IconMic" class="me-1" />
                                    {{ $gettext('Abrir WebDJ') }}
                                </a>
                                <a
                                    :href="terraza.public_page_url"
                                    class="btn btn-outline-secondary"
                                    target="_blank"
                                >
                                    <icon :icon="IconPublic" class="me-1" />
                                    {{ $gettext('Ver Página Pública') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </loading>
    </dashboard-no-sidebar>
</template>

<script setup lang="ts">
import {ref, onMounted} from 'vue';
import {useAxios} from '~/vendor/axios';
import {useTranslate} from '~/vendor/gettext';
import {getApiUrl} from '~/router.ts';
import DashboardNoSidebar from '~/components/DashboardWrapper.vue';
import Loading from '~/components/Common/Loading.vue';
import Icon from '~/components/Common/Icons/Icon.vue';
import {
    IconMic,
    IconPublic,
    IconRadio as IconLocation
} from '~/components/Common/Icons/icons.ts';

interface Terraza {
    id: number;
    name: string;
    short_name: string;
    description: string | null;
    is_online: boolean;
    province: string | null;
    city: string | null;
    sector: string | null;
    webdj_url: string;
    public_page_url: string;
}

const {$gettext} = useTranslate();
const {axios} = useAxios();

const isLoading = ref(true);
const terrazas = ref<Terraza[]>([]);

const loadTerrazas = async () => {
    isLoading.value = true;
    try {
        const url = getApiUrl('/frontend/dj/terrazas');
        const response = await axios.get(url.value);
        terrazas.value = response.data;
    } catch (error) {
        console.error('Error loading terrazas:', error);
        terrazas.value = [];
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    loadTerrazas();
});
</script>

<style scoped>
.terraza-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.terraza-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.card-header {
    background: linear-gradient(135deg, var(--bs-primary), var(--bs-info));
    color: white;
}

.card-header .card-title {
    color: white;
}
</style>
