<template>
    <div class="card">
        <div class="card-header text-bg-primary">
            <h5 class="card-title">{{ $gettext('Branding de Estación') }}</h5>
        </div>
        <div class="card-body">
            <form @submit.prevent="saveBranding">
                <div class="row">
                    <!-- Primary Color -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ $gettext('Color Primario') }}</label>
                        <div class="input-group">
                            <input
                                type="color"
                                class="form-control form-control-color"
                                v-model="brandingForm.primary_color"
                            />
                            <input
                                type="text"
                                class="form-control"
                                v-model="brandingForm.primary_color"
                                placeholder="#007bff"
                                pattern="^#[0-9A-Fa-f]{6}$"
                            />
                        </div>
                        <small class="form-text text-muted">
                            {{ $gettext('Color principal para botones y elementos destacados') }}
                        </small>
                    </div>

                    <!-- Secondary Color -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ $gettext('Color Secundario') }}</label>
                        <div class="input-group">
                            <input
                                type="color"
                                class="form-control form-control-color"
                                v-model="brandingForm.secondary_color"
                            />
                            <input
                                type="text"
                                class="form-control"
                                v-model="brandingForm.secondary_color"
                                placeholder="#6c757d"
                                pattern="^#[0-9A-Fa-f]{6}$"
                            />
                        </div>
                        <small class="form-text text-muted">
                            {{ $gettext('Color secundario para elementos de apoyo') }}
                        </small>
                    </div>

                    <!-- Background Color -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ $gettext('Color de Fondo') }}</label>
                        <div class="input-group">
                            <input
                                type="color"
                                class="form-control form-control-color"
                                v-model="brandingForm.background_color"
                            />
                            <input
                                type="text"
                                class="form-control"
                                v-model="brandingForm.background_color"
                                placeholder="#ffffff"
                                pattern="^#[0-9A-Fa-f]{6}$"
                            />
                        </div>
                        <small class="form-text text-muted">
                            {{ $gettext('Color de fondo de la página pública') }}
                        </small>
                    </div>

                    <!-- Text Color -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ $gettext('Color de Texto') }}</label>
                        <div class="input-group">
                            <input
                                type="color"
                                class="form-control form-control-color"
                                v-model="brandingForm.text_color"
                            />
                            <input
                                type="text"
                                class="form-control"
                                v-model="brandingForm.text_color"
                                placeholder="#212529"
                                pattern="^#[0-9A-Fa-f]{6}$"
                            />
                        </div>
                        <small class="form-text text-muted">
                            {{ $gettext('Color del texto principal') }}
                        </small>
                    </div>

                    <!-- Logo URL -->
                    <div class="col-12 mb-3">
                        <label class="form-label">{{ $gettext('URL del Logo') }}</label>
                        <input
                            type="url"
                            class="form-control"
                            v-model="brandingForm.logo_url"
                            :placeholder="$gettext('https://ejemplo.com/logo.png')"
                        />
                        <small class="form-text text-muted">
                            {{ $gettext('URL de la imagen del logo que se mostrará en páginas públicas') }}
                        </small>
                    </div>

                    <!-- Logo Preview -->
                    <div class="col-12 mb-3" v-if="brandingForm.logo_url">
                        <label class="form-label">{{ $gettext('Vista Previa del Logo') }}</label>
                        <div class="border rounded p-3 text-center bg-light">
                            <img
                                :src="brandingForm.logo_url"
                                alt="Logo"
                                style="max-width: 300px; max-height: 150px;"
                                @error="logoError = true"
                            />
                            <p v-if="logoError" class="text-danger mt-2">
                                {{ $gettext('Error al cargar la imagen') }}
                            </p>
                        </div>
                    </div>

                    <!-- Custom CSS -->
                    <div class="col-12 mb-3">
                        <label class="form-label">{{ $gettext('CSS Personalizado (Avanzado)') }}</label>
                        <textarea
                            class="form-control font-monospace"
                            v-model="brandingForm.public_custom_css"
                            rows="8"
                            :placeholder="cssPlaceholder"
                        ></textarea>
                        <small class="form-text text-muted">
                            {{ $gettext('CSS adicional que se aplicará a las páginas públicas') }}
                        </small>
                    </div>

                    <!-- Preview Colors -->
                    <div class="col-12 mb-3">
                        <label class="form-label">{{ $gettext('Vista Previa de Colores') }}</label>
                        <div class="border rounded p-3" :style="previewStyle">
                            <h4 :style="{ color: brandingForm.text_color || '#212529' }">
                                {{ $gettext('Texto de ejemplo') }}
                            </h4>
                            <button
                                type="button"
                                class="btn me-2"
                                :style="primaryButtonStyle"
                            >
                                {{ $gettext('Botón Primario') }}
                            </button>
                            <button
                                type="button"
                                class="btn"
                                :style="secondaryButtonStyle"
                            >
                                {{ $gettext('Botón Secundario') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-between">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        @click="resetBranding"
                    >
                        {{ $gettext('Restablecer') }}
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="isSaving"
                    >
                        <span v-if="!isSaving">{{ $gettext('Guardar Branding') }}</span>
                        <span v-else>
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            {{ $gettext('Guardando...') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useTranslate } from '~/vendor/gettext';
import { useAxios } from '~/vendor/axios';
import { useNotify } from '~/components/Common/Toasts/useNotify';

interface BrandingConfig {
    primary_color: string | null;
    secondary_color: string | null;
    background_color: string | null;
    text_color: string | null;
    logo_url: string | null;
    public_custom_css: string | null;
}

const props = defineProps<{
    stationId: number;
}>();

const { $gettext } = useTranslate();
const { axios } = useAxios();
const { notifySuccess, notifyError } = useNotify();

const brandingForm = ref<BrandingConfig>({
    primary_color: '#007bff',
    secondary_color: '#6c757d',
    background_color: '#ffffff',
    text_color: '#212529',
    logo_url: null,
    public_custom_css: null,
});

const isSaving = ref(false);
const logoError = ref(false);

const cssPlaceholder = `/* Ejemplo de CSS personalizado */
:root {
  --custom-font: 'Arial', sans-serif;
}
body {
  font-family: var(--custom-font);
}`;

const previewStyle = computed(() => ({
    backgroundColor: brandingForm.value.background_color || '#ffffff',
    color: brandingForm.value.text_color || '#212529',
}));

const primaryButtonStyle = computed(() => ({
    backgroundColor: brandingForm.value.primary_color || '#007bff',
    borderColor: brandingForm.value.primary_color || '#007bff',
    color: '#ffffff',
}));

const secondaryButtonStyle = computed(() => ({
    backgroundColor: brandingForm.value.secondary_color || '#6c757d',
    borderColor: brandingForm.value.secondary_color || '#6c757d',
    color: '#ffffff',
}));

const loadBranding = async () => {
    try {
        const { data } = await axios.get(`/api/station/${props.stationId}/branding`);
        brandingForm.value = {
            primary_color: data.primary_color || '#007bff',
            secondary_color: data.secondary_color || '#6c757d',
            background_color: data.background_color || '#ffffff',
            text_color: data.text_color || '#212529',
            logo_url: data.logo_url || null,
            public_custom_css: data.public_custom_css || null,
        };
    } catch (error) {
        console.error('Error loading branding:', error);
    }
};

const saveBranding = async () => {
    isSaving.value = true;
    try {
        await axios.put(`/api/station/${props.stationId}/branding`, brandingForm.value);
        notifySuccess($gettext('Branding guardado correctamente'));
    } catch (error: any) {
        notifyError(error?.response?.data?.message || $gettext('Error al guardar el branding'));
        console.error('Error saving branding:', error);
    } finally {
        isSaving.value = false;
    }
};

const resetBranding = () => {
    brandingForm.value = {
        primary_color: '#007bff',
        secondary_color: '#6c757d',
        background_color: '#ffffff',
        text_color: '#212529',
        logo_url: null,
        public_custom_css: null,
    };
};

watch(() => brandingForm.value.logo_url, () => {
    logoError.value = false;
});

onMounted(() => {
    loadBranding();
});
</script>

<style scoped>
.form-control-color {
    width: 80px;
    padding: 0.25rem;
}
</style>
