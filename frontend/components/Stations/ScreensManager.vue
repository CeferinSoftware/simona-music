<template>
    <div class="card">
        <div class="card-header text-bg-primary d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ $gettext('Gestionar Pantallas') }}</h5>
            <button
                type="button"
                class="btn btn-sm btn-light"
                @click="openCreateModal"
            >
                <icon-add />
                {{ $gettext('Nueva Pantalla') }}
            </button>
        </div>
        <div class="card-body">
            <data-table
                id="screens_table"
                ref="dataTable"
                :fields="fields"
                :api-url="listUrl"
                :show-toolbar="false"
            >
                <template #cell(is_active)="row">
                    <span 
                        class="badge"
                        :class="row.item.is_active ? 'bg-success' : 'bg-secondary'"
                    >
                        {{ row.item.is_active ? $gettext('Activa') : $gettext('Inactiva') }}
                    </span>
                </template>
                <template #cell(content_type)="row">
                    {{ getContentTypeLabel(row.item.content_type) }}
                </template>
                <template #cell(actions)="row">
                    <button
                        type="button"
                        class="btn btn-sm btn-secondary me-1"
                        @click="copyUrl(row.item.public_url)"
                        :title="$gettext('Copiar URL')"
                    >
                        <icon-link />
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary me-1"
                        @click="editScreen(row.item)"
                    >
                        <icon-edit />
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-danger"
                        @click="deleteScreen(row.item)"
                    >
                        <icon-delete />
                    </button>
                </template>
            </data-table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <modal-form
        ref="modal"
        :title="isEditing ? $gettext('Editar Pantalla') : $gettext('Nueva Pantalla')"
        size="lg"
        @submit="saveScreen"
    >
        <div class="mb-3">
            <label class="form-label">{{ $gettext('Nombre') }}</label>
            <input
                v-model="screenForm.name"
                type="text"
                class="form-control"
                required
                maxlength="100"
            />
        </div>

        <div class="mb-3">
            <label class="form-label">{{ $gettext('Descripción') }}</label>
            <textarea
                v-model="screenForm.description"
                class="form-control"
                rows="3"
            ></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ $gettext('Tipo de Contenido') }}</label>
            <select v-model="screenForm.content_type" class="form-select">
                <option value="nowplaying">{{ $gettext('Now Playing') }}</option>
                <option value="requests">{{ $gettext('Lista de Solicitudes') }}</option>
                <option value="custom">{{ $gettext('Personalizado') }}</option>
            </select>
        </div>

        <div class="mb-3">
            <div class="form-check form-switch">
                <input
                    v-model="screenForm.is_active"
                    type="checkbox"
                    class="form-check-input"
                    id="screen_active"
                />
                <label class="form-check-label" for="screen_active">
                    {{ $gettext('Pantalla Activa') }}
                </label>
            </div>
        </div>

        <div v-if="screenForm.content_type === 'custom'" class="mb-3">
            <label class="form-label">{{ $gettext('HTML Personalizado') }}</label>
            <textarea
                v-model="customHtml"
                class="form-control font-monospace"
                rows="8"
                placeholder="<h1>Mi contenido personalizado</h1>"
            ></textarea>
        </div>
    </modal-form>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useTranslate } from '~/vendor/gettext';
import { useAxios } from '~/vendor/axios';
import { useNotify } from '~/components/Common/Toasts/useNotify';
import DataTable, { DataTableField } from '~/components/Common/DataTable.vue';
import ModalForm from '~/components/Common/ModalForm.vue';
import { IconAdd, IconEdit, IconDelete, IconLink } from '~/components/Common/Icons/icons';

interface Screen {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    content_type: string;
    metadata: any;
    public_url: string;
    created_at: number;
    updated_at: number;
}

interface ScreenForm {
    name: string;
    description: string | null;
    is_active: boolean;
    content_type: string;
    metadata: any;
}

const props = defineProps<{
    listUrl: string;
}>();

const { $gettext } = useTranslate();
const { axios } = useAxios();
const { notifySuccess, notifyError } = useNotify();

const dataTable = ref<InstanceType<typeof DataTable>>();
const modal = ref<InstanceType<typeof ModalForm>>();
const isEditing = ref(false);
const editingId = ref<number | null>(null);
const customHtml = ref('');

const screenForm = ref<ScreenForm>({
    name: '',
    description: null,
    is_active: true,
    content_type: 'nowplaying',
    metadata: null,
});

const fields = computed<DataTableField[]>(() => [
    {
        key: 'name',
        label: $gettext('Nombre'),
        sortable: true,
        isRowHeader: true,
    },
    {
        key: 'description',
        label: $gettext('Descripción'),
        sortable: false,
    },
    {
        key: 'content_type',
        label: $gettext('Tipo de Contenido'),
        sortable: true,
    },
    {
        key: 'is_active',
        label: $gettext('Estado'),
        sortable: true,
    },
    {
        key: 'actions',
        label: $gettext('Acciones'),
        sortable: false,
        class: 'shrink',
    },
]);

const getContentTypeLabel = (type: string): string => {
    switch (type) {
        case 'nowplaying':
            return $gettext('Now Playing');
        case 'requests':
            return $gettext('Lista de Solicitudes');
        case 'custom':
            return $gettext('Personalizado');
        default:
            return type;
    }
};

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    screenForm.value = {
        name: '',
        description: null,
        is_active: true,
        content_type: 'nowplaying',
        metadata: null,
    };
    customHtml.value = '';
    modal.value?.show();
};

const editScreen = (screen: Screen) => {
    isEditing.value = true;
    editingId.value = screen.id;
    screenForm.value = {
        name: screen.name,
        description: screen.description,
        is_active: screen.is_active,
        content_type: screen.content_type,
        metadata: screen.metadata,
    };
    customHtml.value = screen.metadata?.html || '';
    modal.value?.show();
};

const saveScreen = async () => {
    try {
        const metadata = screenForm.value.content_type === 'custom' 
            ? { html: customHtml.value }
            : null;

        const data = {
            ...screenForm.value,
            metadata,
        };

        if (isEditing.value && editingId.value) {
            await axios.put(`${props.listUrl}/${editingId.value}`, data);
            notifySuccess($gettext('Pantalla actualizada correctamente'));
        } else {
            await axios.post(props.listUrl, data);
            notifySuccess($gettext('Pantalla creada correctamente'));
        }

        modal.value?.hide();
        dataTable.value?.refresh();
    } catch (error: any) {
        notifyError(error?.response?.data?.message || $gettext('Error al guardar la pantalla'));
    }
};

const deleteScreen = async (screen: Screen) => {
    if (!confirm($gettext('¿Estás seguro de que deseas eliminar esta pantalla?'))) {
        return;
    }

    try {
        await axios.delete(`${props.listUrl}/${screen.id}`);
        notifySuccess($gettext('Pantalla eliminada correctamente'));
        dataTable.value?.refresh();
    } catch (error: any) {
        notifyError(error?.response?.data?.message || $gettext('Error al eliminar la pantalla'));
    }
};

const copyUrl = async (url: string) => {
    try {
        await navigator.clipboard.writeText(url);
        notifySuccess($gettext('URL copiada al portapapeles'));
    } catch (error) {
        notifyError($gettext('Error al copiar la URL'));
    }
};
</script>
