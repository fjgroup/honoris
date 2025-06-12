<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed, onBeforeUnmount } from 'vue';

// --- 1. DEFINIR PROPS ---
const props = defineProps({
    cities: Array,
    mapsForCity: Array,
    mapPlots: Array,
    selectedMap: Object,
    filters: Object,
});

// --- 2. ESTADO REACTIVO (USANDO PROPS INICIALES) ---
const selectedCityId = ref(props.filters?.city_id || null);
const selectedMapId = ref(props.filters?.map_id || null);

const selectedMapImageUrl = computed(() => props.selectedMap?.image_path || null);

const mapImageDimensions = ref({ width: 0, height: 0, naturalWidth: 0, naturalHeight: 0 });
const mapDisplayRef = ref(null);
const mapImageRef = ref(null);
let resizeObserver = null;

// --- 3. ESTADO DE LOS MODALES Y FORMULARIOS (SIN CAMBIOS IMPORTANTES) ---
const addModeActive = ref(false);
const showAddPlotModal = ref(false);
const newPlotPreviewCoords = ref({ x: null, y: null });
const newPlotFormData = useForm({
    map_id: null, plot_identifier: '', coord_x: null, coord_y: null,
    width: 50, height: 50, notes: '', is_active: true,
});

const editingPlot = ref(null);
const showEditPlotModal = ref(false);
const editPlotFormData = useForm({
    _method: 'put',
    map_id: null, plot_identifier: '', coord_x: null, coord_y: null,
    width: 0, height: 0, notes: '', is_active: true,
});

// --- 4. MÉTODOS DE LA UI (SIN CAMBIOS IMPORTANTES) ---
const updateDisplayDimensions = () => {
    if (mapImageRef.value) {
        mapImageDimensions.value.width = mapImageRef.value.offsetWidth;
        mapImageDimensions.value.height = mapImageRef.value.offsetHeight;
    } else {
        mapImageDimensions.value.width = 0;
        mapImageDimensions.value.height = 0;
    }
};

const onMapImageLoad = (event) => {
    const img = event.target;
    mapImageDimensions.value.naturalWidth = img.naturalWidth;
    mapImageDimensions.value.naturalHeight = img.naturalHeight;
    updateDisplayDimensions();
    if (addModeActive.value) {
        newPlotPreviewCoords.value = { x: null, y: null };
    }
};

const getPlotStyle = (plot) => {
    const { naturalWidth, naturalHeight } = mapImageDimensions.value;
    const { width: displayWidth, height: displayHeight } = mapImageDimensions.value;
    if (!naturalWidth || !naturalHeight || !displayWidth || !displayHeight) {
        return { display: 'none' };
    }
    const scaleX = displayWidth / naturalWidth;
    const scaleY = displayHeight / naturalHeight;
    if (isNaN(scaleX) || !isFinite(scaleX) || isNaN(scaleY) || !isFinite(scaleY)) {
        return { display: 'none' };
    }
    return {
        left: `${plot.coord_x * scaleX}px`, top: `${plot.coord_y * scaleY}px`,
        width: `${plot.width * scaleX}px`, height: `${plot.height * scaleY}px`,
        position: 'absolute',
    };
};

const getNewPlotPreviewStyle = () => {
    if (newPlotPreviewCoords.value.x === null || newPlotPreviewCoords.value.y === null ||
        !mapImageDimensions.value.naturalWidth || !mapImageDimensions.value.naturalHeight ||
        !mapImageDimensions.value.width || !mapImageDimensions.value.height) {
        return { display: 'none' };
    }
    const scaleX = mapImageDimensions.value.width / mapImageDimensions.value.naturalWidth;
    const scaleY = mapImageDimensions.value.height / mapImageDimensions.value.naturalHeight;

    if (isNaN(scaleX) || !isFinite(scaleX) || isNaN(scaleY) || !isFinite(scaleY)) {
        return { display: 'none' };
    }

    return {
        left: `${newPlotPreviewCoords.value.x}px`,
        top: `${newPlotPreviewCoords.value.y}px`,
        width: `${newPlotFormData.width * scaleX}px`,
        height: `${newPlotFormData.height * scaleY}px`,
        position: 'absolute',
        transform: 'translate(-50%, -50%)',
    };
};

const toggleAddMode = () => {
    addModeActive.value = !addModeActive.value;
    if (!addModeActive.value) {
        showAddPlotModal.value = false;
        newPlotPreviewCoords.value = { x: null, y: null };
        newPlotFormData.reset();
    } else {
        cancelEditPlot();
    }
};

const handleMapClick = (event) => {
    if (!addModeActive.value || !mapDisplayRef.value || showEditPlotModal.value) return;

    const rect = mapDisplayRef.value.getBoundingClientRect();
    const clickX_on_displayed_image = event.clientX - rect.left;
    const clickY_on_displayed_image = event.clientY - rect.top;

    newPlotPreviewCoords.value = { x: clickX_on_displayed_image, y: clickY_on_displayed_image };

    let actualX = clickX_on_displayed_image;
    let actualY = clickY_on_displayed_image;

    if (mapImageDimensions.value.naturalWidth > 0 && mapImageDimensions.value.naturalHeight > 0 &&
        mapImageDimensions.value.width > 0 && mapImageDimensions.value.height > 0 &&
        (mapImageDimensions.value.width !== mapImageDimensions.value.naturalWidth ||
            mapImageDimensions.value.height !== mapImageDimensions.value.naturalHeight)) {

        const scaleToNaturalX = mapImageDimensions.value.naturalWidth / mapImageDimensions.value.width;
        const scaleToNaturalY = mapImageDimensions.value.naturalHeight / mapImageDimensions.value.height;
        actualX = clickX_on_displayed_image * scaleToNaturalX;
        actualY = clickY_on_displayed_image * scaleToNaturalY;
    }

    newPlotFormData.coord_x = Math.round(actualX);
    newPlotFormData.coord_y = Math.round(actualY);
    newPlotFormData.map_id = props.filters.map_id;
    showAddPlotModal.value = true;
};

const cancelAddPlot = () => {
    showAddPlotModal.value = false; newPlotFormData.reset();
    addModeActive.value = false; newPlotPreviewCoords.value = { x: null, y: null };
};

const handleEditPlotClick = (plot) => {
    if (addModeActive.value) return;
    editingPlot.value = plot;
    editPlotFormData.defaults({
        map_id: plot.map_id, plot_identifier: plot.plot_identifier,
        coord_x: plot.coord_x, coord_y: plot.coord_y,
        width: plot.width, height: plot.height,
        notes: plot.notes, is_active: plot.is_active,
    });
    editPlotFormData.reset();
    editPlotFormData.clearErrors();
    showEditPlotModal.value = true;
};

const confirmDeletePlot = () => {
    if (!editingPlot.value || !confirm('Are you sure you want to delete this plot?')) return;
    deletePlot();
};

const cancelEditPlot = () => {
    showEditPlotModal.value = false; editingPlot.value = null; editPlotFormData.reset();
};

// --- 5. PETICIONES A LA API (USANDO FORM.POST) ---
const submitNewPlot = () => {
    newPlotFormData.post(route('admin.map-plots.store'), {
        preserveScroll: true,
        onSuccess: () => {
            toggleAddMode();
            // El `router.reload` recarga la lista de plots.
            router.reload({ only: ['mapPlots'] });
        },
        onError: (errors) => { console.error('Error saving new plot:', errors); }
    });
};

const submitEditPlot = () => {
    if (!editingPlot.value) return;
    const updateUrl = route('admin.map-plots.update', editingPlot.value.id);
    editPlotFormData.post(updateUrl, {
        preserveScroll: true,
        onSuccess: () => {
            cancelEditPlot();
            // El `router.reload` recarga la lista de plots.
            router.reload({ only: ['mapPlots'] });
        },
        onError: (errors) => console.error('Error updating plot:', errors)
    });
};

const deletePlot = () => {
    if (!editingPlot.value) return;
    editPlotFormData._method = 'delete'; // Cambia el método
    const deleteUrl = route('admin.map-plots.destroy', editingPlot.value.id);

    editPlotFormData.post(deleteUrl, {
        preserveScroll: true,
        onSuccess: () => {
            cancelEditPlot();
            // El `router.reload` recarga la lista de plots.
            router.reload({ only: ['mapPlots'] });
        },
        onError: (errors) => {
            editPlotFormData._method = 'put'; // Restaura el método original
            console.error('Error deleting plot:', errors);
        },
        onFinish: () => {
            editPlotFormData._method = 'put'; // Restaura el método original
        }
    });
};

// --- 6. OBSERVERS PARA LOS SELECTORES ---
watch(selectedCityId, (newCityId) => {
    router.get(route('admin.map-plot-editor.index'), { city_id: newCityId }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

watch(selectedMapId, (newMapId) => {
    router.get(route('admin.map-plot-editor.index'), {
        city_id: selectedCityId.value,
        map_id: newMapId
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

// --- 7. LIFECYCLE HOOKS ---
onBeforeUnmount(() => {
    if (resizeObserver) {
        if (mapImageRef.value) {
            resizeObserver.unobserve(mapImageRef.value);
        }
        resizeObserver.disconnect();
    }
});

watch(selectedMapImageUrl, (newUrl) => {
    if (mapImageRef.value) {
        if (resizeObserver) {
            resizeObserver.disconnect();
        }
        resizeObserver = new ResizeObserver(updateDisplayDimensions);
        resizeObserver.observe(mapImageRef.value);
    } else if (resizeObserver) {
        resizeObserver.disconnect();
    }
}, { flush: 'post' });
</script>

<template>

    <Head title="Map Plot Editor" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Interactive Map Plot Editor</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <!-- SELECTORES -->
                    <div class="mb-4">
                        <label for="city_selector_plot_editor" class="block text-sm font-medium text-gray-700">Select
                            City:</label>
                        <select id="city_selector_plot_editor" v-model="selectedCityId"
                            class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option :value="null">Select a city</option>
                            <option v-for="city in props.cities" :key="city.id" :value="city.id">{{ city.name }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-4" v-if="selectedCityId">
                        <label for="map_selector_plot_editor" class="block text-sm font-medium text-gray-700">Select
                            Map:</label>
                        <select id="map_selector_plot_editor" v-model="selectedMapId"
                            class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option :value="null">{{ props.mapsForCity.length === 0 ? 'No maps for his city' : 'Select                                a map'
                                }}</option>
                            <option v-for="mapItem in props.mapsForCity" :key="mapItem.id" :value="mapItem.id">{{
                                mapItem.name
                                }}</option>
                        </select>
                    </div>

                    <!-- BOTÓN DE AÑADIR -->
                    <div class="my-4" v-if="selectedMapId">
                        <button @click="toggleAddMode"
                            class="px-4 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2"
                            :class="addModeActive ? 'bg-red-500 text-white hover:bg-red-600 focus:ring-red-500' : 'bg-blue-500 text-white hover:bg-blue-600 focus:ring-blue-500'"
                            :disabled="!selectedMapId || showEditPlotModal">
                            {{ addModeActive ? 'Cancel Add Plot' : 'Add New Plot' }}
                        </button>
                    </div>

                    <!-- VISOR DE MAPA -->
                    <div class="mt-6 map-viewer-container" v-if="selectedMapImageUrl">
                        <h3 class="mb-2 text-lg font-medium text-gray-900">Map: {{ props.selectedMap?.name }}</h3>
                        <div ref="mapDisplayRef" class="relative block w-full border border-gray-300" :style="{
                            height: mapImageDimensions.height ? mapImageDimensions.height + 'px' : 'auto',
                            cursor: addModeActive ? 'crosshair' : 'default'
                        }" @click="handleMapClick">
                            <img ref="mapImageRef" :src="selectedMapImageUrl" alt="Selected Map" @load="onMapImageLoad"
                                class="block object-contain w-full h-auto" />

                            <div v-for="plot in props.mapPlots" :key="plot.id"
                                class="absolute flex items-center justify-center border-2 map-plot-existing"
                                :class="plot.is_active ? 'border-red-500 bg-red-500 bg-opacity-30 hover:bg-opacity-50' : 'border-gray-400 bg-gray-500 bg-opacity-30 hover:bg-opacity-50'"
                                :style="getPlotStyle(plot)" @click.stop="handleEditPlotClick(plot)">
                                <span
                                    class="text-white text-xs font-bold p-0.5 bg-black bg-opacity-60 rounded-sm pointer-events-none">{{
                                    plot.plot_identifier }}</span>
                            </div>

                            <div v-if="addModeActive && newPlotPreviewCoords.x !== null"
                                class="absolute bg-green-500 bg-opacity-50 border-2 border-green-300 pointer-events-none new-plot-preview-marker"
                                :style="getNewPlotPreviewStyle()">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODALES (SIN CAMBIOS EN SU ESTRUCTURA) -->
        <div v-if="showAddPlotModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75 modal-overlay">
            <div class="w-full max-w-lg p-6 bg-white rounded-lg shadow-xl modal-content">
                <h3 class="mb-5 text-xl font-semibold">Add New Map Plot</h3>
                <form @submit.prevent="submitNewPlot" class="space-y-4">
                    <div>
                        <label for="new_plot_identifier" class="form-label">Plot Identifier:</label>
                        <input id="new_plot_identifier" type="text" v-model="newPlotFormData.plot_identifier" required
                            class="form-input" />
                        <div v-if="newPlotFormData.errors.plot_identifier" class="form-error">{{
                            newPlotFormData.errors.plot_identifier }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="new_plot_width" class="form-label">Width (px):</label>
                            <input id="new_plot_width" type="number" v-model.number="newPlotFormData.width" required
                                min="1" class="form-input" />
                            <div v-if="newPlotFormData.errors.width" class="form-error">{{ newPlotFormData.errors.width
                                }}</div>
                        </div>
                        <div>
                            <label for="new_plot_height" class="form-label">Height (px):</label>
                            <input id="new_plot_height" type="number" v-model.number="newPlotFormData.height" required
                                min="1" class="form-input" />
                            <div v-if="newPlotFormData.errors.height" class="form-error">{{
                                newPlotFormData.errors.height }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="new_plot_notes" class="form-label">Notes:</label>
                        <textarea id="new_plot_notes" v-model="newPlotFormData.notes" class="form-textarea"
                            rows="3"></textarea>
                        <div v-if="newPlotFormData.errors.notes" class="form-error">{{ newPlotFormData.errors.notes }}
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="newPlotFormData.is_active" class="form-checkbox" />
                            <span class="ml-2 text-sm text-gray-700">Is Active</span>
                        </label>
                        <div v-if="newPlotFormData.errors.is_active" class="form-error">{{
                            newPlotFormData.errors.is_active }}
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Coordinates based on natural image size: (X: {{
                        newPlotFormData.coord_x }},
                        Y: {{ newPlotFormData.coord_y }})</p>
                    <div class="flex justify-end pt-4 space-x-3">
                        <button type="button" @click="cancelAddPlot" class="btn-secondary">Cancel</button>
                        <button type="submit" :disabled="newPlotFormData.processing" class="btn-primary">Save
                            Plot</button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showEditPlotModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75 modal-overlay">
            <div class="w-full max-w-lg p-6 bg-white rounded-lg shadow-xl modal-content">
                <h3 class="mb-5 text-xl font-semibold">Edit Map Plot: {{ editPlotFormData.plot_identifier }}</h3>
                <form @submit.prevent="submitEditPlot" class="space-y-4">
                    <div>
                        <label for="edit_plot_identifier" class="form-label">Plot Identifier:</label>
                        <input id="edit_plot_identifier" type="text" v-model="editPlotFormData.plot_identifier" required
                            class="form-input" />
                        <div v-if="editPlotFormData.errors.plot_identifier" class="form-error">{{
                            editPlotFormData.errors.plot_identifier }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_coord_x" class="form-label">X Coordinate (px):</label>
                            <input id="edit_coord_x" type="number" v-model.number="editPlotFormData.coord_x" required
                                min="0" class="form-input" />
                            <div v-if="editPlotFormData.errors.coord_x" class="form-error">{{
                                editPlotFormData.errors.coord_x }}
                            </div>
                        </div>
                        <div>
                            <label for="edit_coord_y" class="form-label">Y Coordinate (px):</label>
                            <input id="edit_coord_y" type="number" v-model.number="editPlotFormData.coord_y" required
                                min="0" class="form-input" />
                            <div v-if="editPlotFormData.errors.coord_y" class="form-error">{{
                                editPlotFormData.errors.coord_y }}
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_plot_width" class="form-label">Width (px):</label>
                            <input id="edit_plot_width" type="number" v-model.number="editPlotFormData.width" required
                                min="1" class="form-input" />
                            <div v-if="editPlotFormData.errors.width" class="form-error">{{
                                editPlotFormData.errors.width }}
                            </div>
                        </div>
                        <div>
                            <label for="edit_plot_height" class="form-label">Height (px):</label>
                            <input id="edit_plot_height" type="number" v-model.number="editPlotFormData.height" required
                                min="1" class="form-input" />
                            <div v-if="editPlotFormData.errors.height" class="form-error">{{
                                editPlotFormData.errors.height }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="edit_plot_notes" class="form-label">Notes:</label>
                        <textarea id="edit_plot_notes" v-model="editPlotFormData.notes" class="form-textarea"
                            rows="3"></textarea>
                        <div v-if="editPlotFormData.errors.notes" class="form-error">{{ editPlotFormData.errors.notes }}
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="editPlotFormData.is_active" class="form-checkbox" />
                            <span class="ml-2 text-sm text-gray-700">Is Active</span>
                        </label>
                        <div v-if="editPlotFormData.errors.is_active" class="form-error">{{
                            editPlotFormData.errors.is_active }}
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-4">
                        <button type="button" @click="confirmDeletePlot" :disabled="editPlotFormData.processing"
                            class="btn-danger">Delete Plot</button>
                        <div class="space-x-3">
                            <button type="button" @click="cancelEditPlot" class="btn-secondary">Cancel</button>
                            <button type="submit" :disabled="editPlotFormData.processing" class="btn-primary">Save
                                Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.map-viewer-container img {
    display: block;
    user-select: none;
}

.map-plot-existing {
    cursor: pointer;
}

.modal-overlay {
    z-index: 50;
}

.modal-content {
    z-index: 51;
}

/* --- NUEVOS ESTILOS PARA LOS MARCADORES DE DIAMANTE --- */
.map-plot-existing {
    cursor: pointer;
    transform: rotate(45deg);
    /* 1. Gira el contenedor para crear el diamante */
    transition: transform 0.2s ease-in-out, background-color 0.2s;
    /* Transición suave */
}

/* Mejora opcional: hacer que el marcador crezca un poco al pasar el ratón */
.map-plot-existing:hover {
    transform: rotate(45deg) scale(1.1);
}

.map-plot-existing span {
    display: inline-block;
    /* Asegura que la transformación se aplique correctamente */
    transform: rotate(-45deg);
    /* 2. Gira el texto en sentido contrario para enderezarlo */
}

/* También aplicamos la rotación al marcador de vista previa para consistencia */
.new-plot-preview-marker {
    transform: rotate(45deg) translate(-50%, -50%);
    /* Combinamos la rotación con la traslación que ya tenías */
}

/* El `translate` debe estar en el estilo dinámico para el centrado, así que lo movemos. */
/* Es mejor dejar solo la rotación en la clase estática. */
.new-plot-preview-marker {
    transform: rotate(45deg);
}

.form-label {
    @apply block text-sm font-medium text-gray-700 mb-1;
}

.form-input,
.form-select,
.form-textarea {
    @apply mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2;
}

.form-checkbox {
    @apply rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500;
}

.form-error {
    @apply text-red-500 text-xs mt-1;
}

.btn-primary {
    @apply px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-400;
}

.btn-secondary {
    @apply px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500;
}

.btn-danger {
    @apply px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:bg-gray-400;
}
</style>
