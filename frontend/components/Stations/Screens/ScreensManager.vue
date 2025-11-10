<template>
    <section
        class="card"
        role="region"
    >
        <div class="card-header text-bg-primary">
            <h2 class="card-title">
                {{ $gettext('Screen Management') }}
            </h2>
        </div>

        <div class="card-body">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <p class="text-muted mb-0">
                    {{ $gettext('Manage display screens for your station. Screens can show now playing information, custom content, or be controlled remotely.') }}
                </p>
                <button
                    type="button"
                    class="btn btn-primary"
                    @click="openCreateModal"
                >
                    <icon :icon="IconAdd" />
                    {{ $gettext('Add Screen') }}
                </button>
            </div>

            <loading :loading="isLoading">
                <div
                    v-if="screens.length === 0"
                    class="alert alert-info"
                >
                    {{ $gettext('No screens configured yet. Click "Add Screen" to create your first screen.') }}
                </div>

                <data-table
                    v-else
                    id="station_screens"
                    :fields="fields"
                    :items="screens"
                    :responsive="true"
                >
                    <template #cell(name)="row">
                        <div>
                            <strong>{{ row.item.name }}</strong>
                            <br>
                            <small class="text-muted">{{ row.item.description }}</small>
                        </div>
                    </template>

                    <template #cell(is_active)="row">
                        <span
                            class="badge"
                            :class="row.item.is_active ? 'bg-success' : 'bg-secondary'"
                        >
                            {{ row.item.is_active ? $gettext('Active') : $gettext('Inactive') }}
                        </span>
                    </template>

                    <template #cell(content_type)="row">
                        <span class="badge bg-info">
                            {{ formatContentType(row.item.content_type) }}
                        </span>
                    </template>

                    <template #cell(public_url)="row">
                        <a
                            :href="row.item.public_url"
                            target="_blank"
                            class="btn btn-sm btn-outline-primary"
                        >
                            <icon :icon="IconOpenInNew" />
                            {{ $gettext('Open') }}
                        </a>
                    </template>

                    <template #cell(actions)="row">
                        <div class="btn-group btn-group-sm">
                            <button
                                type="button"
                                class="btn btn-sm"
                                :class="row.item.is_active ? 'btn-warning' : 'btn-success'"
                                :title="row.item.is_active ? $gettext('Deactivate') : $gettext('Activate')"
                                @click="toggleActive(row.item)"
                            >
                                <icon :icon="row.item.is_active ? IconPause : IconPlayArrow" />
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm btn-primary"
                                :title="$gettext('Edit')"
                                @click="openEditModal(row.item)"
                            >
                                <icon :icon="IconEdit" />
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm btn-danger"
                                :title="$gettext('Delete')"
                                @click="confirmDelete(row.item)"
                            >
                                <icon :icon="IconDelete" />
                            </button>
                        </div>
                    </template>
                </data-table>
            </loading>
        </div>

        <!-- Modal para crear/editar pantalla -->
        <modal
            id="screen_modal"
            ref="modal"
            size="lg"
            :title="isEditing ? $gettext('Edit Screen') : $gettext('Add Screen')"
            @hidden="resetForm"
        >
            <form @submit.prevent="saveScreen">
                <div class="row g-3">
                    <form-group-field
                        id="screen_name"
                        class="col-md-6"
                        :field="v$.form.name"
                        input-class="form-control"
                        :label="$gettext('Screen Name')"
                        :description="$gettext('A descriptive name for this screen')"
                    />

                    <form-group-checkbox
                        id="screen_is_active"
                        class="col-md-6"
                        :field="v$.form.is_active"
                        :label="$gettext('Active')"
                        :description="$gettext('Whether this screen is currently active')"
                    />

                    <form-group-field
                        id="screen_description"
                        class="col-md-12"
                        :field="v$.form.description"
                        input-type="textarea"
                        input-class="form-control"
                        :label="$gettext('Description')"
                        :description="$gettext('Optional description for this screen')"
                    />

                    <form-group-select
                        id="screen_content_type"
                        class="col-md-6"
                        :field="v$.form.content_type"
                        :label="$gettext('Content Type')"
                        :description="$gettext('Type of content to display on this screen')"
                        :options="contentTypeOptions"
                    />

                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <icon :icon="IconInfo" />
                            {{ $gettext('Screen URL will be generated automatically and can be opened in a browser or embedded in displays.') }}
                        </div>
                    </div>
                </div>

                <invisible-submit-button />
            </form>

            <template #modal-footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="close"
                >
                    {{ $gettext('Close') }}
                </button>
                <button
                    type="button"
                    class="btn btn-primary"
                    :disabled="v$.$invalid"
                    @click="saveScreen"
                >
                    <icon :icon="IconSave" />
                    {{ isEditing ? $gettext('Update') : $gettext('Create') }}
                </button>
            </template>
        </modal>
    </section>
</template>

<script setup lang="ts">
import {ref, computed, onMounted} from 'vue';
import DataTable from '~/components/Common/DataTable.vue';
import Icon from '~/components/Common/Icons/Icon.vue';
import Loading from '~/components/Common/Loading.vue';
import Modal from '~/components/Common/Modal.vue';
import FormGroupField from '~/components/Form/FormGroupField.vue';
import FormGroupCheckbox from '~/components/Form/FormGroupCheckbox.vue';
import FormGroupSelect from '~/components/Form/FormGroupSelect.vue';
import InvisibleSubmitButton from '~/components/Common/InvisibleSubmitButton.vue';
import {useAxios} from '~/vendor/axios';
import {useNotify} from '~/components/Common/Toasts/useNotify';
import {useTranslate} from '~/vendor/gettext';
import {useVuelidate} from '@vuelidate/core';
import {required} from '@vuelidate/validators';
import useConfirmAndDelete from '~/functions/useConfirmAndDelete';
import {
    IconAdd,
    IconEdit,
    IconDelete,
    IconSave,
    IconOpenInNew,
    IconPlayArrow,
    IconPause,
    IconInfo
} from '~/components/Common/Icons/icons';

const props = defineProps<{
    apiUrl: string
}>();

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
    description: string;
    is_active: boolean;
    content_type: string;
}

const {$gettext} = useTranslate();
const {axios} = useAxios();
const {notifySuccess, notifyError} = useNotify();

const isLoading = ref(true);
const screens = ref<Screen[]>([]);
const modal = ref<InstanceType<typeof Modal> | null>(null);
const isEditing = ref(false);
const editingId = ref<number | null>(null);

const form = ref<ScreenForm>({
    name: '',
    description: '',
    is_active: true,
    content_type: 'nowplaying'
});

const validations = {
    form: {
        name: {required},
        description: {},
        is_active: {},
        content_type: {required}
    }
};

const v$ = useVuelidate(validations, {form});

const fields = [
    {key: 'name', label: $gettext('Name'), sortable: true},
    {key: 'is_active', label: $gettext('Status'), sortable: true},
    {key: 'content_type', label: $gettext('Content Type'), sortable: true},
    {key: 'public_url', label: $gettext('URL'), sortable: false},
    {key: 'actions', label: $gettext('Actions'), sortable: false, class: 'text-end'}
];

const contentTypeOptions = [
    {value: 'nowplaying', text: $gettext('Now Playing')},
    {value: 'fullscreen', text: $gettext('Fullscreen Player')},
    {value: 'custom', text: $gettext('Custom Content')}
];

const formatContentType = (type: string): string => {
    const option = contentTypeOptions.find(opt => opt.value === type);
    return option ? option.text : type;
};

const loadScreens = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(props.apiUrl);
        screens.value = response.data;
    } catch (error) {
        notifyError($gettext('Failed to load screens.'));
        console.error(error);
    } finally {
        isLoading.value = false;
    }
};

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
    modal.value?.show();
};

const openEditModal = (screen: Screen) => {
    isEditing.value = true;
    editingId.value = screen.id;
    form.value = {
        name: screen.name,
        description: screen.description || '',
        is_active: screen.is_active,
        content_type: screen.content_type
    };
    modal.value?.show();
};

const saveScreen = async () => {
    const isValid = await v$.value.$validate();
    if (!isValid) {
        return;
    }

    try {
        if (isEditing.value && editingId.value) {
            await axios.put(`${props.apiUrl}/${editingId.value}`, form.value);
            notifySuccess($gettext('Screen updated successfully.'));
        } else {
            await axios.post(props.apiUrl, form.value);
            notifySuccess($gettext('Screen created successfully.'));
        }
        
        modal.value?.hide();
        await loadScreens();
    } catch (error) {
        notifyError($gettext('Failed to save screen.'));
        console.error(error);
    }
};

const toggleActive = async (screen: Screen) => {
    try {
        await axios.put(`${props.apiUrl}/${screen.id}`, {
            ...screen,
            is_active: !screen.is_active
        });
        notifySuccess(
            screen.is_active 
                ? $gettext('Screen deactivated.')
                : $gettext('Screen activated.')
        );
        await loadScreens();
    } catch (error) {
        notifyError($gettext('Failed to update screen status.'));
        console.error(error);
    }
};

const {doDelete} = useConfirmAndDelete(
    $gettext('Delete Screen?'),
    () => loadScreens()
);

const confirmDelete = (screen: Screen) => {
    doDelete(
        `${props.apiUrl}/${screen.id}`,
        $gettext('Screen deleted successfully.')
    );
};

const resetForm = () => {
    form.value = {
        name: '',
        description: '',
        is_active: true,
        content_type: 'nowplaying'
    };
    v$.value.$reset();
};

const close = () => {
    modal.value?.hide();
};

onMounted(() => {
    loadScreens();
});
</script>
