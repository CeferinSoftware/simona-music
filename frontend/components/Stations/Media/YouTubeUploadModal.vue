<template>
    <modal-form
        ref="$modal"
        :loading="loading"
        :title="$gettext('Añadir desde YouTube')"
        :error="error"
        :disable-save-button="!form.youtube_url || !form.title"
        @submit="doSubmit"
        @hidden="clearContents"
    >
        <template #default>
            <div class="alert alert-info mb-3">
                <icon :icon="IconInfo" class="me-2" />
                {{ $gettext('Introduce la URL de un vídeo de YouTube. El vídeo se reproducirá directamente desde YouTube en la página pública.') }}
            </div>

            <div class="row g-3">
                <form-group-field
                    id="youtube_url"
                    class="col-md-12"
                    :field="r$.youtube_url"
                    input-type="url"
                    :label="$gettext('URL de YouTube')"
                    placeholder="https://www.youtube.com/watch?v=..."
                    autofocus
                />

                <div v-if="videoPreview" class="col-md-12">
                    <div class="card">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center">
                                <img 
                                    v-if="videoPreview.thumbnail"
                                    :src="videoPreview.thumbnail" 
                                    alt="Thumbnail"
                                    class="me-3 rounded"
                                    style="width: 120px; height: 68px; object-fit: cover;"
                                >
                                <div>
                                    <div class="fw-bold">{{ videoPreview.title }}</div>
                                    <small class="text-muted">{{ videoPreview.channel }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form-group-field
                    id="yt_title"
                    class="col-md-12"
                    :field="r$.title"
                    :label="$gettext('Título de la Canción')"
                />

                <form-group-field
                    id="yt_artist"
                    class="col-md-12"
                    :field="r$.artist"
                    :label="$gettext('Artista')"
                />

                <form-group-select
                    id="yt_genre"
                    class="col-md-12"
                    :field="r$.genre"
                    :options="genreOptions"
                    :label="$gettext('Categoría/Género')"
                    clearable
                />

                <form-group-field
                    id="yt_album"
                    class="col-md-12"
                    :field="r$.album"
                    :label="$gettext('Álbum')"
                />
            </div>
        </template>

        <template #save-button-name>
            <icon :icon="IconAdd" class="me-1" />
            {{ $gettext('Añadir a Biblioteca') }}
        </template>
    </modal-form>
</template>

<script setup lang="ts">
import {ref, computed, watch} from "vue";
import {useTranslate} from "~/vendor/gettext";
import {useAxios} from "~/vendor/axios.ts";
import {useNotify} from "~/components/Common/Toasts/useNotify.ts";
import ModalForm from "~/components/Common/ModalForm.vue";
import FormGroupField from "~/components/Form/FormGroupField.vue";
import FormGroupSelect from "~/components/Form/FormGroupSelect.vue";
import Icon from "~/components/Common/Icons/Icon.vue";
import {IconInfo, IconAdd} from "~/components/Common/Icons/icons.ts";
import {useAppRegle} from "~/vendor/regle.ts";
import {required, url} from "@regle/rules";

interface VideoPreview {
    title: string;
    channel: string;
    thumbnail: string;
}

interface FormData {
    youtube_url: string;
    title: string;
    artist: string;
    genre: string;
    album: string;
}

const props = defineProps<{
    addFromYoutubeUrl: string;
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
const videoPreview = ref<VideoPreview | null>(null);

const form = ref<FormData>({
    youtube_url: '',
    title: '',
    artist: '',
    genre: '',
    album: ''
});

const {r$} = useAppRegle(form, {
    youtube_url: {required, url},
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

// Extraer ID de YouTube de la URL
const extractYouTubeId = (url: string): string | null => {
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
        /youtube\.com\/v\/([^&\n?#]+)/
    ];
    
    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match) {
            return match[1];
        }
    }
    return null;
};

// Watch para actualizar la preview cuando cambia la URL
watch(() => form.value.youtube_url, async (newUrl) => {
    videoPreview.value = null;
    
    if (!newUrl) return;
    
    const videoId = extractYouTubeId(newUrl);
    if (!videoId) return;
    
    // Crear preview básica con thumbnail de YouTube
    videoPreview.value = {
        title: '',
        channel: '',
        thumbnail: `https://img.youtube.com/vi/${videoId}/mqdefault.jpg`
    };
    
    // Intentar extraer título del video usando oEmbed
    try {
        const oembedUrl = `https://www.youtube.com/oembed?url=${encodeURIComponent(newUrl)}&format=json`;
        const response = await fetch(oembedUrl);
        if (response.ok) {
            const data = await response.json();
            videoPreview.value.title = data.title || '';
            videoPreview.value.channel = data.author_name || '';
            
            // Auto-rellenar título si está vacío
            if (!form.value.title && data.title) {
                // Intentar separar artista - título
                const parts = data.title.split(' - ');
                if (parts.length >= 2) {
                    form.value.artist = parts[0].trim();
                    form.value.title = parts.slice(1).join(' - ').trim();
                } else {
                    form.value.title = data.title;
                }
            }
        }
    } catch {
        // Ignorar errores de fetch
    }
}, {debounce: 500});

const clearContents = () => {
    form.value = {
        youtube_url: '',
        title: '',
        artist: '',
        genre: '',
        album: ''
    };
    videoPreview.value = null;
    error.value = null;
};

const show = () => {
    clearContents();
    $modal.value?.show();
};

const doSubmit = async () => {
    r$.$validate();
    if (r$.$invalid) {
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        await axios.post(props.addFromYoutubeUrl, {
            youtube_url: form.value.youtube_url,
            title: form.value.title,
            artist: form.value.artist,
            genre: form.value.genre,
            album: form.value.album
        });

        notifySuccess($gettext('Vídeo de YouTube añadido correctamente a la biblioteca.'));
        $modal.value?.hide();
        emit('relist');
    } catch (e: any) {
        error.value = e.response?.data?.message || $gettext('Error al añadir el vídeo.');
    } finally {
        loading.value = false;
    }
};

defineExpose({
    show
});
</script>
