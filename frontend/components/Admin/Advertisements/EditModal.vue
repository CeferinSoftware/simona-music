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
    </modal-form>
</template>

<script setup lang="ts">
import {ref, computed} from "vue";
import {useTranslate} from "~/vendor/gettext";
import ModalForm from "~/components/Common/ModalForm.vue";
import FormGroupField from "~/components/Form/FormGroupField.vue";
import FormGroupSelect from "~/components/Form/FormGroupSelect.vue";
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
    max_plays: number;
    play_frequency: number;
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
        if (isEditMode.value) {
            await axios.put(editUrl.value!, form.value);
            notifySuccess($gettext('Anuncio actualizado correctamente.'));
        } else {
            await axios.post(props.createUrl, form.value);
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
