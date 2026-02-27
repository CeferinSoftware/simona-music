<template>
    <modal-form
        ref="$modal"
        :loading="loading"
        :title="isEditMode ? $gettext('Editar Anuncio') : $gettext('Nuevo Anuncio')"
        :error="error"
        :disable-save-button="r$.$invalid"
        size="lg"
        @submit="doSubmit"
        @hidden="clearContents"
    >
        <div class="row g-3">
            <form-group-field
                id="form_name"
                class="col-md-8"
                :field="r$.name"
                :label="$gettext('Nombre del Anuncio')"
            />

            <form-group-select
                id="form_status"
                class="col-md-4"
                :field="r$.status"
                :options="statusOptions"
                :label="$gettext('Estado')"
            />

            <form-group-field
                id="form_advertiser_name"
                class="col-md-6"
                :field="r$.advertiser_name"
                :label="$gettext('Nombre del Anunciante')"
            />

            <form-group-select
                id="form_priority"
                class="col-md-6"
                :field="r$.priority"
                :options="priorityOptions"
                :label="$gettext('Prioridad')"
            />

            <form-group-field
                id="form_description"
                class="col-md-12"
                :field="r$.description"
                input-type="textarea"
                :label="$gettext('Descripción')"
            />

            <form-group-select
                id="form_media_type"
                class="col-md-6"
                :field="r$.media_type"
                :options="mediaTypeOptions"
                :label="$gettext('Tipo de Media')"
            />

            <form-group-field
                id="form_duration"
                class="col-md-6"
                :field="r$.duration"
                input-type="number"
                :input-attrs="{step: '0.1', min: '0'}"
                :label="$gettext('Duración (segundos)')"
            />

            <form-group-field
                id="form_media_url"
                class="col-md-12"
                :field="r$.media_url"
                input-type="url"
                :label="$gettext('URL del Media')"
                :description="$gettext('URL externa del archivo de audio/vídeo (YouTube, etc.).')"
            />

            <form-group-field
                id="form_play_frequency"
                class="col-md-6"
                :field="r$.play_frequency"
                input-type="number"
                :input-attrs="{min: '1'}"
                :label="$gettext('Frecuencia (cada X canciones)')"
            />

            <form-group-field
                id="form_max_plays"
                class="col-md-6"
                :field="r$.max_plays"
                input-type="number"
                :input-attrs="{min: '0'}"
                :label="$gettext('Máximo reproducciones (0 = ilimitado)')"
            />
        </div>

        <!-- Sección de selección de Terrazas/Estaciones -->
        <fieldset class="mt-4">
            <legend class="h6 border-bottom pb-2">
                {{ $gettext('Terrazas Objetivo') }}
                <small class="text-muted fw-normal">
                    {{ $gettext('(dejar vacío = todas las terrazas)') }}
                </small>
            </legend>

            <!-- Filtros -->
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <input
                        v-model="stationFilter.search"
                        type="text"
                        class="form-control form-control-sm"
                        :placeholder="$gettext('Buscar por nombre...')"
                    />
                </div>
                <div class="col-md-3">
                    <select
                        v-model="stationFilter.province"
                        class="form-select form-select-sm"
                    >
                        <option value="">{{ $gettext('Todas las provincias') }}</option>
                        <option
                            v-for="prov in availableProvinces"
                            :key="prov"
                            :value="prov"
                        >{{ prov }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select
                        v-model="stationFilter.category"
                        class="form-select form-select-sm"
                    >
                        <option value="">{{ $gettext('Todas las categorías') }}</option>
                        <option
                            v-for="cat in availableCategories"
                            :key="cat"
                            :value="cat"
                        >{{ cat }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select
                        v-model="stationFilter.status"
                        class="form-select form-select-sm"
                    >
                        <option value="">{{ $gettext('Todas') }}</option>
                        <option value="enabled">{{ $gettext('Activas') }}</option>
                        <option value="disabled">{{ $gettext('Inactivas') }}</option>
                    </select>
                </div>
            </div>

            <!-- Acciones masivas -->
            <div class="d-flex gap-2 mb-2">
                <button
                    type="button"
                    class="btn btn-outline-primary btn-sm"
                    @click="selectAllFiltered"
                >
                    {{ $gettext('Seleccionar Filtradas') }}
                    <span class="badge text-bg-secondary ms-1">{{ filteredStations.length }}</span>
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    @click="deselectAll"
                >
                    {{ $gettext('Deseleccionar Todas') }}
                </button>
                <span class="ms-auto text-muted small align-self-center">
                    {{ form.target_stations?.length || 0 }} {{ $gettext('seleccionadas') }}
                </span>
            </div>

            <!-- Lista de estaciones -->
            <div class="station-list border rounded p-2" style="max-height: 250px; overflow-y: auto;">
                <div
                    v-if="filteredStations.length === 0"
                    class="text-muted text-center py-3"
                >
                    {{ $gettext('No se encontraron terrazas con los filtros aplicados.') }}
                </div>
                <div
                    v-for="station in filteredStations"
                    :key="station.id"
                    class="form-check py-1 border-bottom"
                >
                    <input
                        :id="'station_' + station.id"
                        v-model="form.target_stations"
                        :value="station.id"
                        class="form-check-input"
                        type="checkbox"
                    />
                    <label
                        :for="'station_' + station.id"
                        class="form-check-label d-flex justify-content-between w-100"
                    >
                        <span>
                            <strong>{{ station.name }}</strong>
                            <span v-if="station.city || station.province" class="text-muted small ms-2">
                                {{ [station.city, station.province].filter(Boolean).join(', ') }}
                            </span>
                        </span>
                        <span class="d-flex align-items-center gap-1">
                            <span
                                v-if="station.ad_category"
                                class="badge text-bg-info"
                                style="font-size: 0.7em"
                            >{{ station.ad_category }}</span>
                            <span
                                class="badge"
                                :class="station.is_enabled ? 'text-bg-success' : 'text-bg-secondary'"
                                style="font-size: 0.7em"
                            >{{ station.is_enabled ? $gettext('Activa') : $gettext('Inactiva') }}</span>
                        </span>
                    </label>
                </div>
            </div>
        </fieldset>
    </modal-form>
</template>

<script setup lang="ts">
import {ref, computed, onMounted} from "vue";
import {useTranslate} from "~/vendor/gettext";
import ModalForm from "~/components/Common/ModalForm.vue";
import FormGroupField from "~/components/Form/FormGroupField.vue";
import FormGroupSelect from "~/components/Form/FormGroupSelect.vue";
import {useAxios} from "~/vendor/axios.ts";
import {useNotify} from "~/components/Common/Toasts/useNotify.ts";
import {useAppRegle} from "~/vendor/regle.ts";
import {required} from "@regle/rules";
import {getApiUrl} from "~/router.ts";

interface StationInfo {
    id: number;
    name: string;
    short_name: string | null;
    ad_category: string | null;
    province: string | null;
    city: string | null;
    is_enabled: boolean;
}

interface FormData {
    name: string;
    description: string | null;
    media_type: string;
    media_path: string | null;
    media_url: string | null;
    duration: number;
    status: string;
    priority: number;
    advertiser_name: string | null;
    max_plays: number;
    play_frequency: number;
    target_stations: number[];
}

const props = defineProps<{
    createUrl: string;
}>();

const emit = defineEmits<{
    (e: 'relist'): void;
}>();

const {$gettext} = useTranslate();
const {axios} = useAxios();
const {notifySuccess, notifyError} = useNotify();

const $modal = ref<InstanceType<typeof ModalForm> | null>(null);
const loading = ref(false);
const error = ref<string | null>(null);
const editUrl = ref<string | null>(null);

const isEditMode = computed(() => editUrl.value !== null);

// Opciones estáticas
const statusOptions = {
    'active': 'Activo',
    'inactive': 'Inactivo',
    'scheduled': 'Programado'
};

const mediaTypeOptions = {
    'audio': 'Audio',
    'video': 'Vídeo'
};

const priorityOptions = {
    '1': 'Muy Baja',
    '3': 'Baja',
    '5': 'Normal',
    '7': 'Alta',
    '10': 'Máxima'
};

// ---- Estaciones / Terrazas ----
const allStations = ref<StationInfo[]>([]);
const stationFilter = ref({
    search: '',
    province: '',
    category: '',
    status: '' as '' | 'enabled' | 'disabled',
});

const availableProvinces = computed(() => {
    const set = new Set<string>();
    allStations.value.forEach(s => {
        if (s.province) set.add(s.province);
    });
    return Array.from(set).sort();
});

const availableCategories = computed(() => {
    const set = new Set<string>();
    allStations.value.forEach(s => {
        if (s.ad_category) set.add(s.ad_category);
    });
    return Array.from(set).sort();
});

const filteredStations = computed(() => {
    return allStations.value.filter(s => {
        if (stationFilter.value.search) {
            const q = stationFilter.value.search.toLowerCase();
            if (!s.name.toLowerCase().includes(q)) return false;
        }
        if (stationFilter.value.province && s.province !== stationFilter.value.province) return false;
        if (stationFilter.value.category && s.ad_category !== stationFilter.value.category) return false;
        if (stationFilter.value.status === 'enabled' && !s.is_enabled) return false;
        if (stationFilter.value.status === 'disabled' && s.is_enabled) return false;
        return true;
    });
});

const selectAllFiltered = () => {
    const current = new Set(form.value.target_stations || []);
    filteredStations.value.forEach(s => current.add(s.id));
    form.value.target_stations = Array.from(current);
};

const deselectAll = () => {
    form.value.target_stations = [];
};

const loadStations = async () => {
    try {
        const {data} = await axios.get(getApiUrl('/admin/vue/advertisements'));
        if (data.stations) {
            allStations.value = data.stations;
        }
    } catch (e) {
        // Silenciar - las estaciones simplemente no se mostrarán
    }
};

onMounted(() => {
    loadStations();
});

// ---- Formulario ----
const getDefaultForm = (): FormData => ({
    name: '',
    description: null,
    media_type: 'audio',
    media_path: null,
    media_url: null,
    duration: 0,
    status: 'active',
    priority: 5,
    advertiser_name: null,
    max_plays: 0,
    play_frequency: 5,
    target_stations: [],
});

const form = ref<FormData>(getDefaultForm());

const {r$} = useAppRegle(form, {
    name: {required},
    media_type: {required},
    status: {},
    priority: {},
    description: {},
    advertiser_name: {},
    media_path: {},
    media_url: {},
    duration: {},
    max_plays: {},
    play_frequency: {},
    target_stations: {},
});

const clearContents = () => {
    form.value = getDefaultForm();
    editUrl.value = null;
    error.value = null;
    stationFilter.value = {search: '', province: '', category: '', status: ''};
};

const create = () => {
    clearContents();
    $modal.value?.show();
};

const edit = async (recordUrl: string) => {
    clearContents();
    editUrl.value = recordUrl;
    loading.value = true;

    try {
        const {data} = await axios.get(recordUrl);
        form.value = {
            ...getDefaultForm(),
            ...data,
            target_stations: data.target_stations || [],
        };
        $modal.value?.show();
    } catch (e) {
        notifyError($gettext('Error al cargar el anuncio.'));
    } finally {
        loading.value = false;
    }
};

const doSubmit = async () => {
    r$.$validate();
    if (r$.$invalid) {
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        const payload = {
            ...form.value,
            target_stations: form.value.target_stations.length > 0 ? form.value.target_stations : null,
        };

        if (isEditMode.value) {
            await axios.put(editUrl.value!, payload);
            notifySuccess($gettext('Anuncio actualizado correctamente.'));
        } else {
            await axios.post(props.createUrl, payload);
            notifySuccess($gettext('Anuncio creado correctamente.'));
        }

        $modal.value?.hide();
        emit('relist');
    } catch (e: any) {
        error.value = e.response?.data?.message || $gettext('Error al guardar el anuncio.');
    } finally {
        loading.value = false;
    }
};

defineExpose({
    create,
    edit,
});
</script>
