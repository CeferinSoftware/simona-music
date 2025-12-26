<template>
    <div>
        <!-- Botón para abrir selector de archivos -->
        <div class="file-drop-target upload-area" @click="openFilePicker">
            <div class="text-center py-3">
                <icon :icon="IconUpload" class="mb-2" style="font-size: 2rem;" />
                <div>{{ $gettext('Arrastra archivos aquí o haz clic para seleccionar') }}</div>
                <small class="text-muted">{{ $gettext('Se pedirán los metadatos antes de subir cada archivo') }}</small>
            </div>
        </div>

        <!-- Input oculto para selección de archivos -->
        <input
            ref="fileInput"
            type="file"
            :accept="validMimeTypes.join(',')"
            multiple
            class="d-none"
            @change="onFilesSelected"
        >

        <!-- Modal de metadatos pre-subida -->
        <modal-form
            ref="$metadataModal"
            :title="$gettext('Información de la Canción')"
            :disable-save-button="!metadataForm.title"
            @submit="doUploadWithMetadata"
            @hidden="onModalHidden"
        >
            <template #default>
                <div v-if="pendingFile" class="mb-3">
                    <div class="alert alert-info">
                        <strong>{{ $gettext('Archivo:') }}</strong> {{ pendingFile.name }}
                        <br>
                        <small class="text-muted">{{ formatFileSize(pendingFile.size) }}</small>
                    </div>
                </div>

                <div class="row g-3">
                    <form-group-field
                        id="meta_title"
                        class="col-md-12"
                        :field="r$.title"
                        :label="$gettext('Título de la Canción')"
                        autofocus
                    />

                    <form-group-field
                        id="meta_artist"
                        class="col-md-12"
                        :field="r$.artist"
                        :label="$gettext('Artista')"
                    />

                    <form-group-select
                        id="meta_genre"
                        class="col-md-12"
                        :field="r$.genre"
                        :options="genreOptions"
                        :label="$gettext('Categoría/Género')"
                        clearable
                    />

                    <form-group-field
                        id="meta_album"
                        class="col-md-12"
                        :field="r$.album"
                        :label="$gettext('Álbum')"
                    />
                </div>

                <div v-if="remainingFiles.length > 0" class="mt-3 text-muted">
                    <small>
                        {{ $gettext('Archivos pendientes:') }} {{ remainingFiles.length }}
                    </small>
                </div>
            </template>

            <template #save-button-name>
                {{ $gettext('Subir Canción') }}
            </template>
        </modal-form>

        <!-- Progreso de subida -->
        <div v-if="uploadingFiles.length > 0" class="upload-progress mt-3">
            <div 
                v-for="file in uploadingFiles" 
                :key="file.id"
                class="uploading-file pt-1"
                :class="{ 'text-success': file.completed, 'text-danger': file.error }"
            >
                <h6 class="fileuploadname m-0">{{ file.name }}</h6>
                <div v-if="!file.completed && !file.error" class="progress h-20 my-1">
                    <div
                        class="progress-bar h-20"
                        role="progressbar"
                        :style="{width: file.progress + '%'}"
                    />
                </div>
                <div v-if="file.error" class="upload-status text-danger">
                    {{ file.error }}
                </div>
                <div v-if="file.completed" class="text-success">
                    <icon :icon="IconCheck" /> {{ $gettext('Subido correctamente') }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import {ref, computed} from "vue";
import {useTranslate} from "~/vendor/gettext";
import {useAxios} from "~/vendor/axios.ts";
import {useAzuraCast} from "~/vendor/azuracast";
import ModalForm from "~/components/Common/ModalForm.vue";
import FormGroupField from "~/components/Form/FormGroupField.vue";
import FormGroupSelect from "~/components/Form/FormGroupSelect.vue";
import Icon from "~/components/Common/Icons/Icon.vue";
import {IconUpload, IconCheck} from "~/components/Common/Icons/icons.ts";
import formatFileSize from "~/functions/formatFileSize";
import {useAppRegle} from "~/vendor/regle.ts";
import {required} from "@regle/rules";

interface MetadataForm {
    title: string;
    artist: string;
    genre: string;
    album: string;
}

interface UploadingFile {
    id: string;
    name: string;
    progress: number;
    completed: boolean;
    error: string | null;
}

const props = withDefaults(
    defineProps<{
        uploadUrl: string,
        currentDirectory: string,
        searchPhrase: string,
        validMimeTypes?: string[]
    }>(),
    {
        validMimeTypes: () => ['audio/*']
    }
);

const emit = defineEmits<{
    (e: 'relist'): void
}>();

const {$gettext} = useTranslate();
const {axios} = useAxios();
const {apiCsrf} = useAzuraCast();

const fileInput = ref<HTMLInputElement | null>(null);
const $metadataModal = ref<InstanceType<typeof ModalForm> | null>(null);

const pendingFile = ref<File | null>(null);
const remainingFiles = ref<File[]>([]);
const uploadingFiles = ref<UploadingFile[]>([]);

const metadataForm = ref<MetadataForm>({
    title: '',
    artist: '',
    genre: '',
    album: ''
});

const {r$} = useAppRegle(metadataForm, {
    title: {required},
    artist: {},
    genre: {},
    album: {}
});

const genreOptions = computed(() => ({
    'rock': 'Rock',
    'pop': 'Pop',
    'electronic': 'Electrónica',
    'hiphop': 'Hip Hop',
    'reggaeton': 'Reggaeton',
    'bachata': 'Bachata',
    'salsa': 'Salsa',
    'jazz': 'Jazz',
    'classical': 'Clásica',
    'country': 'Country',
    'rnb': 'R&B',
    'latin': 'Latina',
    'indie': 'Indie',
    'metal': 'Metal',
    'folk': 'Folk',
    'blues': 'Blues',
    'reggae': 'Reggae',
    'soul': 'Soul',
    'funk': 'Funk',
    'disco': 'Disco',
    'house': 'House',
    'techno': 'Techno',
    'trance': 'Trance',
    'ambient': 'Ambient',
    'lounge': 'Lounge',
    'chillout': 'Chillout',
    'flamenco': 'Flamenco',
    'other': 'Otros',
}));

const targetUrl = computed(() => {
    const url = new URL(props.uploadUrl, document.location.href);
    url.searchParams.set('currentDirectory', props.currentDirectory);
    url.searchParams.set('searchPhrase', props.searchPhrase);
    return url.toString();
});

const openFilePicker = () => {
    fileInput.value?.click();
};

const onFilesSelected = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);
    
    if (files.length === 0) return;

    // Guardar todos los archivos y procesar el primero
    remainingFiles.value = files.slice(1);
    processNextFile(files[0]);
    
    // Limpiar input para permitir seleccionar el mismo archivo otra vez
    target.value = '';
};

const processNextFile = (file: File) => {
    pendingFile.value = file;
    
    // Intentar extraer título y artista del nombre del archivo
    const fileName = file.name.replace(/\.[^/.]+$/, ''); // Quitar extensión
    const parts = fileName.split(' - ');
    
    if (parts.length >= 2) {
        metadataForm.value.artist = parts[0].trim();
        metadataForm.value.title = parts.slice(1).join(' - ').trim();
    } else {
        metadataForm.value.title = fileName;
        metadataForm.value.artist = '';
    }
    
    metadataForm.value.genre = '';
    metadataForm.value.album = '';
    
    $metadataModal.value?.show();
};

const doUploadWithMetadata = async () => {
    if (!pendingFile.value) return;

    const file = pendingFile.value;
    const fileId = Date.now().toString();

    // Añadir a la lista de archivos en subida
    uploadingFiles.value.push({
        id: fileId,
        name: file.name,
        progress: 0,
        completed: false,
        error: null
    });

    $metadataModal.value?.hide();

    try {
        const formData = new FormData();
        formData.append('file_data', file);
        formData.append('title', metadataForm.value.title);
        formData.append('artist', metadataForm.value.artist);
        formData.append('genre', metadataForm.value.genre);
        formData.append('album', metadataForm.value.album);

        await axios.post(targetUrl.value, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-API-CSRF': apiCsrf
            },
            onUploadProgress: (progressEvent) => {
                const progress = progressEvent.total 
                    ? Math.round((progressEvent.loaded * 100) / progressEvent.total)
                    : 0;
                
                const uploadFile = uploadingFiles.value.find(f => f.id === fileId);
                if (uploadFile) {
                    uploadFile.progress = progress;
                }
            }
        });

        // Marcar como completado
        const uploadFile = uploadingFiles.value.find(f => f.id === fileId);
        if (uploadFile) {
            uploadFile.completed = true;
        }

        emit('relist');

        // Limpiar después de un momento
        setTimeout(() => {
            uploadingFiles.value = uploadingFiles.value.filter(f => f.id !== fileId);
        }, 3000);

    } catch (error: any) {
        const uploadFile = uploadingFiles.value.find(f => f.id === fileId);
        if (uploadFile) {
            uploadFile.error = error.response?.data?.message || $gettext('Error al subir el archivo');
        }
    }

    // Procesar siguiente archivo si hay más
    if (remainingFiles.value.length > 0) {
        const nextFile = remainingFiles.value.shift()!;
        setTimeout(() => processNextFile(nextFile), 500);
    }
};

const onModalHidden = () => {
    // Si se cierra el modal sin subir, procesar siguiente si hay
    if (remainingFiles.value.length > 0) {
        const nextFile = remainingFiles.value.shift()!;
        setTimeout(() => processNextFile(nextFile), 300);
    }
    pendingFile.value = null;
};
</script>

<style scoped>
.upload-area {
    border: 2px dashed var(--bs-border-color);
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.upload-area:hover {
    border-color: var(--bs-primary);
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}
</style>
