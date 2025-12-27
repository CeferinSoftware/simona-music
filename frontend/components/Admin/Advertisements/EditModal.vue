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
        <tabs content-class="mt-3">
            <tab :label="$gettext('Información General')">
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
                        :options="statuses"
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
                        :options="priorityOptionsFormatted"
                        :label="$gettext('Prioridad')"
                    />

                    <form-group-field
                        id="form_description"
                        class="col-md-12"
                        :field="r$.description"
                        input-type="textarea"
                        :label="$gettext('Descripción')"
                    />
                </div>
            </tab>

            <tab :label="$gettext('Media')">
                <div class="row g-3">
                    <form-group-select
                        id="form_media_type"
                        class="col-md-6"
                        :field="r$.media_type"
                        :options="mediaTypes"
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
                        :label="$gettext('URL del Media (YouTube, etc.)')"
                        :description="$gettext('URL externa del archivo de audio/vídeo.')"
                    />

                    <form-group-field
                        id="form_media_path"
                        class="col-md-12"
                        :field="r$.media_path"
                        :label="$gettext('Ruta del Archivo')"
                        :description="$gettext('Ruta interna del archivo si se subió.')"
                    />
                </div>
            </tab>

            <tab :label="$gettext('Segmentación')">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">{{ $gettext('Categorías Musicales') }}</label>
                        <p class="text-muted small">{{ $gettext('Selecciona las categorías de estaciones donde se reproducirá este anuncio. Deja vacío para todas.') }}</p>
                        <div class="row">
                            <div 
                                v-for="(label, value) in categories" 
                                :key="value"
                                class="col-md-3 col-sm-4 col-6"
                            >
                                <div class="form-check">
                                    <input
                                        :id="'cat_' + value"
                                        v-model="form.target_categories"
                                        class="form-check-input"
                                        type="checkbox"
                                        :value="value"
                                    >
                                    <label class="form-check-label" :for="'cat_' + value">
                                        {{ label }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mt-4">
                        <label class="form-label">{{ $gettext('Provincias Objetivo') }}</label>
                        <p class="text-muted small">{{ $gettext('Selecciona las provincias donde se reproducirá este anuncio. Deja vacío para todas.') }}</p>
                        <div class="row">
                            <div 
                                v-for="prov in spanishProvinces" 
                                :key="prov"
                                class="col-md-3 col-sm-4 col-6"
                            >
                                <div class="form-check">
                                    <input
                                        :id="'prov_' + prov"
                                        v-model="form.target_provinces"
                                        class="form-check-input"
                                        type="checkbox"
                                        :value="prov"
                                    >
                                    <label class="form-check-label" :for="'prov_' + prov">
                                        {{ prov }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </tab>

            <tab :label="$gettext('Programación')">
                <div class="row g-3">
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
                        :label="$gettext('Máximo de reproducciones (0 = ilimitado)')"
                    />

                    <form-group-field
                        id="form_start_date"
                        class="col-md-6"
                        :field="r$.start_date"
                        input-type="datetime-local"
                        :label="$gettext('Fecha de inicio')"
                    />

                    <form-group-field
                        id="form_end_date"
                        class="col-md-6"
                        :field="r$.end_date"
                        input-type="datetime-local"
                        :label="$gettext('Fecha de fin')"
                    />

                    <form-group-field
                        id="form_time_start"
                        class="col-md-6"
                        :field="r$.time_start"
                        input-type="time"
                        :label="$gettext('Hora de inicio diaria')"
                    />

                    <form-group-field
                        id="form_time_end"
                        class="col-md-6"
                        :field="r$.time_end"
                        input-type="time"
                        :label="$gettext('Hora de fin diaria')"
                    />

                    <div class="col-md-12">
                        <label class="form-label">{{ $gettext('Días Activos') }}</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div 
                                v-for="(label, value) in weekDays" 
                                :key="value"
                                class="form-check"
                            >
                                <input
                                    :id="'day_' + value"
                                    v-model="form.active_days"
                                    class="form-check-input"
                                    type="checkbox"
                                    :value="Number(value)"
                                >
                                <label class="form-check-label" :for="'day_' + value">
                                    {{ label }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </tab>
        </tabs>
    </modal-form>
</template>

<script setup lang="ts">
import {ref, computed} from "vue";
import {useTranslate} from "~/vendor/gettext";
import ModalForm from "~/components/Common/ModalForm.vue";
import FormGroupField from "~/components/Form/FormGroupField.vue";
import FormGroupSelect from "~/components/Form/FormGroupSelect.vue";
import Tabs from "~/components/Common/Tabs.vue";
import Tab from "~/components/Common/Tab.vue";
import {useAxios} from "~/vendor/axios.ts";
import {useNotify} from "~/components/Common/Toasts/useNotify.ts";
import {useAppRegle} from "~/vendor/regle.ts";
import {required} from "@regle/rules";

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
    target_categories: string[];
    target_provinces: string[];
    target_cities: string[];
    start_date: string | null;
    end_date: string | null;
    max_plays: number;
    play_frequency: number;
    time_start: string | null;
    time_end: string | null;
    active_days: number[];
}

const props = defineProps<{
    createUrl: string;
    categories: Record<string, string>;
    mediaTypes: Record<string, string>;
    statuses: Record<string, string>;
    weekDays: Record<number, string>;
    priorityOptions: Record<number, string>;
    spanishProvinces: string[];
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
    target_categories: [],
    target_provinces: [],
    target_cities: [],
    start_date: null,
    end_date: null,
    max_plays: 0,
    play_frequency: 5,
    time_start: null,
    time_end: null,
    active_days: [1, 2, 3, 4, 5, 6, 7],
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
    start_date: {},
    end_date: {},
    max_plays: {},
    play_frequency: {},
    time_start: {},
    time_end: {},
});

const priorityOptionsFormatted = computed(() => {
    const options: Record<string, string> = {};
    for (const [value, label] of Object.entries(props.priorityOptions)) {
        options[value] = label;
    }
    return options;
});

const clearContents = () => {
    form.value = getDefaultForm();
    editUrl.value = null;
    error.value = null;
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
            target_categories: data.target_categories || [],
            target_provinces: data.target_provinces || [],
            target_cities: data.target_cities || [],
            active_days: data.active_days || [1, 2, 3, 4, 5, 6, 7],
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
        const dataToSend = {
            ...form.value,
        };

        if (isEditMode.value) {
            await axios.put(editUrl.value!, dataToSend);
            notifySuccess($gettext('Anuncio actualizado correctamente.'));
        } else {
            await axios.post(props.createUrl, dataToSend);
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
