<template>
    <card-page :title="$gettext('Gestión de Anuncios Publicitarios')">
        <template #header-actions>
            <add-button
                :text="$gettext('Nuevo Anuncio')"
                @click="doCreate"
            />
        </template>

        <template #description>
            {{ $gettext('Administra los anuncios publicitarios que se insertarán en las estaciones según su categoría y ubicación geográfica.') }}
        </template>

        <data-table
            id="advertisements"
            paginated
            :fields="fields"
            :provider="listItemProvider"
        >
            <template #cell(name)="{item}">
                <div class="typography-subheading">
                    {{ item.name }}
                </div>
                <div class="text-muted small">
                    {{ item.advertiser_name || $gettext('Sin anunciante') }}
                </div>
            </template>

            <template #cell(media_type)="{item}">
                <span 
                    class="badge"
                    :class="item.media_type === 'video' ? 'text-bg-primary' : 'text-bg-secondary'"
                >
                    {{ item.media_type === 'video' ? $gettext('Vídeo') : $gettext('Audio') }}
                </span>
            </template>

            <template #cell(status)="{item}">
                <span 
                    class="badge"
                    :class="getStatusClass(item.status)"
                >
                    {{ getStatusLabel(item.status) }}
                </span>
            </template>

            <template #cell(stats)="{item}">
                <div class="d-flex flex-column align-items-center">
                    <div class="h6 mb-0">{{ item.play_count }}</div>
                    <small class="text-muted">{{ $gettext('reproducciones') }}</small>
                </div>
            </template>

            <template #cell(actions)="row">
                <div class="btn-group btn-group-sm">
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="doEdit(row.item.links.self)"
                    >
                        {{ $gettext('Editar') }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-danger"
                        @click="doDelete(row.item.links.self)"
                    >
                        {{ $gettext('Eliminar') }}
                    </button>
                </div>
            </template>
        </data-table>
    </card-page>

    <advertisements-edit-modal
        ref="$editModal"
        :create-url="listUrl"
        @relist="() => relist()"
    />
</template>

<script setup lang="ts">
import DataTable, {DataTableField} from "~/components/Common/DataTable.vue";
import {useTranslate} from "~/vendor/gettext";
import {useTemplateRef} from "vue";
import useHasEditModal from "~/functions/useHasEditModal";
import useConfirmAndDelete from "~/functions/useConfirmAndDelete";
import CardPage from "~/components/Common/CardPage.vue";
import {getApiUrl} from "~/router";
import AddButton from "~/components/Common/AddButton.vue";
import {useApiItemProvider} from "~/functions/dataTable/useApiItemProvider.ts";
import AdvertisementsEditModal from "~/components/Admin/Advertisements/EditModal.vue";

interface Advertisement {
    id: number;
    name: string;
    advertiser_name: string | null;
    media_type: string;
    status: string;
    priority: number;
    play_count: number;
    links: {
        self: string;
    };
}

const listUrl = getApiUrl('/admin/advertisements');

const {$gettext} = useTranslate();

const fields: DataTableField<Advertisement>[] = [
    {
        key: 'name',
        isRowHeader: true,
        label: $gettext('Nombre'),
        sortable: true
    },
    {
        key: 'media_type',
        label: $gettext('Tipo'),
        sortable: false,
        class: 'shrink'
    },
    {
        key: 'status',
        label: $gettext('Estado'),
        sortable: true,
        class: 'shrink'
    },
    {
        key: 'stats',
        label: $gettext('Reproducciones'),
        sortable: true,
        class: 'shrink text-center'
    },
    {
        key: 'actions',
        label: $gettext('Acciones'),
        sortable: false,
        class: 'shrink'
    }
];

const {listItemProvider, relist} = useApiItemProvider<Advertisement>(
    listUrl,
    ['admin-advertisements', 'list']
);

const $editModal = useTemplateRef('$editModal');

const {doCreate, doEdit} = useHasEditModal($editModal);
const {doDelete} = useConfirmAndDelete(
    $gettext('¿Eliminar este anuncio?'),
    () => relist()
);

const getStatusClass = (status: string): string => {
    switch (status) {
        case 'active': return 'text-bg-success';
        case 'inactive': return 'text-bg-secondary';
        case 'scheduled': return 'text-bg-info';
        case 'expired': return 'text-bg-danger';
        default: return 'text-bg-secondary';
    }
};

const getStatusLabel = (status: string): string => {
    switch (status) {
        case 'active': return $gettext('Activo');
        case 'inactive': return $gettext('Inactivo');
        case 'scheduled': return $gettext('Programado');
        case 'expired': return $gettext('Expirado');
        default: return status;
    }
};
</script>
