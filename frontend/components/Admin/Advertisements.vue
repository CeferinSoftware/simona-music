<template>
    <loading :loading="propsLoading" lazy>
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
                        <icon :icon="item.media_type === 'video' ? IconVolumeUp : IconMusicNote" class="sm" />
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

                <template #cell(categories)="{item}">
                    <div v-if="item.categories && item.categories.length">
                        <span 
                            v-for="cat in item.categories.slice(0, 3)" 
                            :key="cat"
                            class="badge text-bg-info me-1"
                        >
                            {{ getCategoryLabel(cat) }}
                        </span>
                        <span v-if="item.categories.length > 3" class="text-muted">
                            +{{ item.categories.length - 3 }}
                        </span>
                    </div>
                    <span v-else class="text-muted">{{ $gettext('Todas') }}</span>
                </template>

                <template #cell(locations)="{item}">
                    <div v-if="item.locations && item.locations.length">
                        <span 
                            v-for="loc in item.locations.slice(0, 2)" 
                            :key="loc.id"
                            class="badge text-bg-secondary me-1"
                        >
                            {{ loc.province }}{{ loc.city ? ` - ${loc.city}` : '' }}
                        </span>
                        <span v-if="item.locations.length > 2" class="text-muted">
                            +{{ item.locations.length - 2 }}
                        </span>
                    </div>
                    <span v-else class="text-muted">{{ $gettext('Todas') }}</span>
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
                            class="btn"
                            :class="row.item.status === 'active' ? 'btn-warning' : 'btn-success'"
                            @click="toggleStatus(row.item)"
                        >
                            {{ row.item.status === 'active' ? $gettext('Pausar') : $gettext('Activar') }}
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
            v-if="props"
            ref="$editModal"
            :create-url="listUrl"
            :categories="props.categories"
            :media-types="props.mediaTypes"
            :statuses="props.statuses"
            :week-days="props.weekDays"
            :priority-options="props.priorityOptions"
            :spanish-provinces="props.spanishProvinces"
            @relist="() => relist()"
        />
    </loading>
</template>

<script setup lang="ts">
import DataTable, {DataTableField} from "~/components/Common/DataTable.vue";
import {useTranslate} from "~/vendor/gettext";
import {useTemplateRef, computed} from "vue";
import useHasEditModal from "~/functions/useHasEditModal";
import useConfirmAndDelete from "~/functions/useConfirmAndDelete";
import CardPage from "~/components/Common/CardPage.vue";
import {getApiUrl} from "~/router";
import AddButton from "~/components/Common/AddButton.vue";
import {useNotify} from "~/components/Common/Toasts/useNotify.ts";
import {useAxios} from "~/vendor/axios.ts";
import {useApiItemProvider} from "~/functions/dataTable/useApiItemProvider.ts";
import {useQuery} from "@tanstack/vue-query";
import Loading from "~/components/Common/Loading.vue";
import Icon from "~/components/Common/Icons/Icon.vue";
import {IconMusicNote, IconVolumeUp} from "~/components/Common/Icons/icons.ts";
import AdvertisementsEditModal from "~/components/Admin/Advertisements/EditModal.vue";

interface AdvertisementProps {
    categories: Record<string, string>;
    mediaTypes: Record<string, string>;
    statuses: Record<string, string>;
    weekDays: Record<number, string>;
    priorityOptions: Record<number, string>;
    spanishProvinces: Record<string, string>;
}

interface Advertisement {
    id: number;
    name: string;
    advertiser_name: string | null;
    media_type: string;
    status: string;
    priority: number;
    play_count: number;
    categories: string[];
    locations: Array<{id: number; province: string; city: string | null; sector: string | null}>;
    links: {
        self: string;
    };
}

const listUrl = getApiUrl('/admin/advertisements');
const propsUrl = getApiUrl('/admin/vue/advertisements');

const {axios} = useAxios();

const {data: props, isLoading: propsLoading} = useQuery<AdvertisementProps>({
    queryKey: ['admin-advertisements', 'props'],
    queryFn: async ({signal}) => {
        const {data} = await axios.get<AdvertisementProps>(propsUrl.value, {signal});
        return data;
    },
});

const {$gettext} = useTranslate();
const {notifySuccess, notifyError} = useNotify();

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
        key: 'categories',
        label: $gettext('Categorías'),
        sortable: false
    },
    {
        key: 'locations',
        label: $gettext('Ubicaciones'),
        sortable: false
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

const getCategoryLabel = (category: string): string => {
    return props.value?.categories[category] || category;
};

const toggleStatus = async (item: Advertisement) => {
    try {
        const newStatus = item.status === 'active' ? 'inactive' : 'active';
        await axios.put(item.links.self, { status: newStatus });
        notifySuccess($gettext('Estado del anuncio actualizado.'));
        relist();
    } catch (error) {
        notifyError($gettext('Error al cambiar el estado del anuncio.'));
    }
};
</script>
