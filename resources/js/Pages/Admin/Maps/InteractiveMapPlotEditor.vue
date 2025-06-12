<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import axios from 'axios';

// --- Reactive State ---
const cities = ref([]);
const selectedCityId = ref(null);
const mapsForCity = ref([]);
const selectedMapId = ref(null);
const selectedMap = ref(null);
const selectedMapImageUrl = ref(null);
const mapPlots = ref([]);

const isLoadingCities = ref(false);
const isLoadingMaps = ref(false);
const isLoadingMapDetails = ref(false);
const isLoadingMapPlots = ref(false);

const mapImageDimensions = ref({
    width: 0, height: 0, // Displayed dimensions of the image
    naturalWidth: 0, naturalHeight: 0 // Original dimensions of the image file
});
const mapDisplayRef = ref(null); // Ref for the div directly containing the image and plots
const mapImageRef = ref(null);   // Ref for the <img> element itself

// Add Plot Mode State
const addModeActive = ref(false);
const showAddPlotModal = ref(false);
const newPlotPreviewCoords = ref({ x: null, y: null }); // Click position on displayed image for marker
const newPlotFormData = useForm({
    map_id: null, plot_identifier: '', coord_x: null, coord_y: null, // These coords are natural image coords
    width: 50, height: 50, notes: '', is_active: true, // these width/height are natural image dims
});

// Edit Plot Mode State
const editingPlot = ref(null);
const showEditPlotModal = ref(false);
const editPlotFormData = useForm({
    map_id: null, plot_identifier: '', coord_x: null, coord_y: null,
    width: 0, height: 0, notes: '', is_active: true,
});

let resizeObserver = null;

// --- Methods ---
const updateDisplayDimensions = () => {
    if (mapImageRef.value) {
        mapImageDimensions.value.width = mapImageRef.value.offsetWidth;
        mapImageDimensions.value.height = mapImageRef.value.offsetHeight;
    } else { // Image not loaded or not present
        mapImageDimensions.value.width = 0;
        mapImageDimensions.value.height = 0;
    }
};

const fetchCities = async () => { /* ... same ... */
    isLoadingCities.value = true;
    try {
        const response = await axios.get(route('admin.cities.index', { all: 'true' }));
        cities.value = response.data;
    } catch (error) {
        console.error("Error fetching cities:", error);
    } finally {
        isLoadingCities.value = false;
    }
};
const fetchMapsForCity = async () => { /* ... same ... */
    if (!selectedCityId.value || selectedCityId.value === 'null') {
        mapsForCity.value = []; selectedMapId.value = null; selectedMap.value = null; selectedMapImageUrl.value = null; mapPlots.value = [];
        return;
    }
    isLoadingMaps.value = true;
    mapsForCity.value = []; selectedMapId.value = null; selectedMap.value = null; selectedMapImageUrl.value = null; mapPlots.value = [];
    try {
        const response = await axios.get(route('admin.maps.index', { city_id: selectedCityId.value }));
        mapsForCity.value = response.data;
    } catch (error) {
        console.error("Error fetching maps for city:", error);
    } finally {
        isLoadingMaps.value = false;
    }
};
const fetchMapDetailsAndPlots = async () => { /* ... same ... */
    if (!selectedMapId.value || selectedMapId.value === 'null') {
        selectedMap.value = null; selectedMapImageUrl.value = null; mapPlots.value = [];
        mapImageDimensions.value = { width: 0, height: 0, naturalWidth: 0, naturalHeight: 0 };
        return;
    }
    isLoadingMapDetails.value = true; isLoadingMapPlots.value = true;
    selectedMap.value = null; selectedMapImageUrl.value = null; mapPlots.value = [];
    mapImageDimensions.value = { width: 0, height: 0, naturalWidth: 0, naturalHeight: 0 };
    try {
        const mapDetailsResponse = await axios.get(route('admin.maps.details.api', selectedMapId.value));
        selectedMap.value = mapDetailsResponse.data;
        selectedMapImageUrl.value = mapDetailsResponse.data.image_path;
        if (mapDetailsResponse.data.width && mapDetailsResponse.data.height) { // Assuming map model might store natural dims
             mapImageDimensions.value.naturalWidth = mapDetailsResponse.data.width;
             mapImageDimensions.value.naturalHeight = mapDetailsResponse.data.height;
        }
        const plotsResponse = await axios.get(route('admin.map-plots.index', { map_id: selectedMapId.value }));
        mapPlots.value = plotsResponse.data.map_plots.data;
    } catch (error) {
        console.error("Error fetching map details or plots:", error);
        selectedMap.value = null; selectedMapImageUrl.value = null;
    } finally {
        isLoadingMapDetails.value = false; isLoadingMapPlots.value = false;
    }
};

const onMapImageLoad = (event) => {
    const img = event.target;
    mapImageDimensions.value.naturalWidth = img.naturalWidth;
    mapImageDimensions.value.naturalHeight = img.naturalHeight;

    nextTick(updateDisplayDimensions);

    if(addModeActive.value) {
        newPlotPreviewCoords.value = { x: null, y: null };
    }
};

const getPlotStyle = (plot) => {
    const { naturalWidth, naturalHeight } = mapImageDimensions.value;
    const { width: displayWidth, height: displayHeight } = mapImageDimensions.value;

    if (!naturalWidth || !naturalHeight || !displayWidth || !displayHeight) {
        return { display: 'none' }; // Not ready if any dimension is zero
    }

    const scaleX = displayWidth / naturalWidth;
    const scaleY = displayHeight / naturalHeight;

    if (isNaN(scaleX) || !isFinite(scaleX) || isNaN(scaleY) || !isFinite(scaleY)) {
        return { display: 'none' }; // Avoid NaN in styles
    }
    return {
        left: `${plot.coord_x * scaleX}px`, top: `${plot.coord_y * scaleY}px`,
        width: `${plot.width * scaleX}px`, height: `${plot.height * scaleY}px`,
        position: 'absolute', // Explicitly set here
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
        left: `${newPlotPreviewCoords.value.x}px`, // Preview coords are already display-relative
        top: `${newPlotPreviewCoords.value.y}px`,
        width: `${newPlotFormData.width * scaleX}px`, // Scale default/form width
        height: `${newPlotFormData.height * scaleY}px`, // Scale default/form height
        position: 'absolute',
        transform: 'translate(-50%, -50%)', // Center the preview on the click
    };
};


const toggleAddMode = () => { /* ... same ... */
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
    if (!addModeActive.value || !mapDisplayRef.value || showEditPlotModal.value) return; // Use mapDisplayRef
    const rect = mapDisplayRef.value.getBoundingClientRect(); // Use mapDisplayRef
    const clickX_on_displayed_image = event.clientX - rect.left;
    const clickY_on_displayed_image = event.clientY - rect.top;

    newPlotPreviewCoords.value = { x: clickX_on_displayed_image, y: clickY_on_displayed_image };

    let actualX = clickX_on_displayed_image;
    let actualY = clickY_on_displayed_image;

    if (mapImageDimensions.value.naturalWidth > 0 && mapImageDimensions.value.naturalHeight > 0 &&
        mapImageDimensions.value.width > 0 && mapImageDimensions.value.height > 0 && // Ensure display dims also > 0
        (mapImageDimensions.value.width !== mapImageDimensions.value.naturalWidth ||
         mapImageDimensions.value.height !== mapImageDimensions.value.naturalHeight)) {

        const scaleToNaturalX = mapImageDimensions.value.naturalWidth / mapImageDimensions.value.width;
        const scaleToNaturalY = mapImageDimensions.value.naturalHeight / mapImageDimensions.value.height;
        actualX = clickX_on_displayed_image * scaleToNaturalX;
        actualY = clickY_on_displayed_image * scaleToNaturalY;
    }

    newPlotFormData.coord_x = Math.round(actualX);
    newPlotFormData.coord_y = Math.round(actualY);
    newPlotFormData.map_id = selectedMapId.value;
    showAddPlotModal.value = true;
};
const submitNewPlot = () => { /* ... same ... */
    newPlotFormData.post(route('admin.map-plots.store'), {
        preserveScroll: true,
        onSuccess: () => { fetchMapDetailsAndPlots(); toggleAddMode(); },
        onError: (errors) => { console.error('Error saving new plot:', errors); }
    });
};
const cancelAddPlot = () => { /* ... same ... */
    showAddPlotModal.value = false; newPlotFormData.reset();
    addModeActive.value = false; newPlotPreviewCoords.value = { x: null, y: null };
};
const handleEditPlotClick = (plot) => { /* ... same ... */
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
const submitEditPlot = () => { /* ... same ... */
    if (!editingPlot.value) return;
    editPlotFormData.put(route('admin.map-plots.update', editingPlot.value.id), {
        preserveScroll: true,
        onSuccess: () => { fetchMapDetailsAndPlots(); cancelEditPlot(); },
        onError: (errors) => { console.error('Error updating plot:', errors); }
    });
};
const confirmDeletePlot = () => { /* ... same ... */
    if (!editingPlot.value || !confirm('Are you sure you want to delete this plot?')) return;
    deletePlot();
};
const deletePlot = () => {
    if (!editingPlot.value) return;

    const deleteUrl = route('admin.map-plots.destroy', editingPlot.value.id);
    console.log('Generated DELETE URL for router.delete:', deleteUrl); // Updated log message

    router.delete(deleteUrl, { // Changed to router.delete
        preserveScroll: true,
        onSuccess: () => {
            fetchMapDetailsAndPlots();
            cancelEditPlot();
        },
        onError: (errors) => {
            console.error('Error deleting plot via router.delete:', errors);
            alert('Could not delete plot. Check console for details.');
        },
    });
};
const cancelEditPlot = () => { /* ... same ... */
    showEditPlotModal.value = false; editingPlot.value = null; editPlotFormData.reset();
};

// Lifecycle Hooks
onMounted(() => {
    fetchCities();
    // Initial setup of ResizeObserver
    // Check if mapImageRef.value is available before observing.
    // The watch on selectedMapImageUrl will handle observing when image is loaded.
});

onBeforeUnmount(() => {
    if (resizeObserver) {
        if (mapImageRef.value) { // Check if element still exists
            resizeObserver.unobserve(mapImageRef.value);
        }
        resizeObserver.disconnect();
    }
});

watch(selectedMapImageUrl, (newUrl) => {
    nextTick(() => {
        if (mapImageRef.value) {
            if (resizeObserver) {
                resizeObserver.disconnect();
            }
            resizeObserver = new ResizeObserver(updateDisplayDimensions);
            resizeObserver.observe(mapImageRef.value);
            // Initial dimension update might be needed if @load doesn't fire reliably after src change
            // or if image is cached. However, onMapImageLoad should handle this.
        } else if (resizeObserver) {
            resizeObserver.disconnect();
        }
    });
}, { flush: 'post' });

</script>

<template>
    <Head title="Map Plot Editor" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Interactive Map Plot Editor</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <!-- City and Map Selectors (same as before) -->
                    <div class="mb-4">
                        <label for="city_selector_plot_editor" class="block text-sm font-medium text-gray-700">Select City:</label>
                        <select id="city_selector_plot_editor" v-model="selectedCityId" @change="fetchMapsForCity"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                :disabled="isLoadingCities">
                            <option :value="null" disabled>{{ isLoadingCities ? 'Loading cities...' : 'Select a city' }}</option>
                            <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                        </select>
                    </div>
                    <div class="mb-4" v-if="selectedCityId || isLoadingMaps">
                        <label for="map_selector_plot_editor" class="block text-sm font-medium text-gray-700">Select Map:</label>
                        <select id="map_selector_plot_editor" v-model="selectedMapId" @change="fetchMapDetailsAndPlots"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                :disabled="isLoadingMaps || !selectedCityId">
                            <option :value="null" disabled>{{ isLoadingMaps ? 'Loading maps...' : (mapsForCity.length === 0 && selectedCityId ? 'No maps for this city' : 'Select a map') }}</option>
                            <option v-for="mapItem in mapsForCity" :key="mapItem.id" :value="mapItem.id">{{ mapItem.name }}</option>
                        </select>
                    </div>
                     <div v-if="selectedCityId && !isLoadingMaps && mapsForCity.length === 0" class="text-gray-500 text-sm mb-4">
                        No maps found for the selected city.
                    </div>
                    <div class="my-4" v-if="selectedMapId">
                        <button @click="toggleAddMode"
                                class="px-4 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2"
                                :class="addModeActive ? 'bg-red-500 text-white hover:bg-red-600 focus:ring-red-500' : 'bg-blue-500 text-white hover:bg-blue-600 focus:ring-blue-500'"
                                :disabled="!selectedMapId || showEditPlotModal">
                            {{ addModeActive ? 'Cancel Add Plot' : 'Add New Plot' }}
                        </button>
                    </div>

                    <!-- Map Viewer -->
                    <div class="map-viewer-container mt-6" v-if="selectedMapImageUrl">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Map: {{ mapsForCity.find(m => m.id === selectedMapId)?.name }}</h3>
                         <!-- mapDisplayRef is the positioning context for plots -->
                        <!-- inline-block to shrink-wrap -->
                        <div ref="mapDisplayRef"
                             class="relative block w-full border border-gray-300"
                             :style="{
                                 height: mapImageDimensions.height ? mapImageDimensions.height + 'px' : 'auto',
                                 cursor: addModeActive ? 'crosshair' : 'default'
                             }"
                             @click="handleMapClick"
                             >
                            <img
                                ref="mapImageRef"
                                :src="selectedMapImageUrl"
                                alt="Selected Map"
                                @load="onMapImageLoad"
                                class="block w-full h-auto object-contain"
                            />

                            <div v-for="plot in mapPlots" :key="plot.id"
                                 class="map-plot-existing absolute border-2 flex items-center justify-center"
                                 :class="plot.is_active ? 'border-red-500 bg-red-500 bg-opacity-30 hover:bg-opacity-50' : 'border-gray-400 bg-gray-500 bg-opacity-30 hover:bg-opacity-50'"
                                 :style="getPlotStyle(plot)"
                                 @click.stop="handleEditPlotClick(plot)">
                                <span class="text-white text-xs font-bold p-0.5 bg-black bg-opacity-60 rounded-sm pointer-events-none">{{ plot.plot_identifier }}</span>
                            </div>

                            <div v-if="addModeActive && newPlotPreviewCoords.x !== null"
                                 class="new-plot-preview-marker absolute border-2 border-green-300 bg-green-500 bg-opacity-50 pointer-events-none"
                                 :style="getNewPlotPreviewStyle()">
                            </div>
                        </div>
                         <div v-if="isLoadingMapPlots" class="text-center py-4 text-gray-500">Loading map plots...</div>
                    </div>
                    <div v-else-if="selectedMapId && !selectedMapImageUrl && !isLoadingMapDetails" class="text-gray-500 text-sm mt-6">
                        Loading map image... or no image set for this map.
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals (Add and Edit) - Kept outside main layout flow for z-index -->
        <div v-if="showAddPlotModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
            <!-- ... Add Plot Modal content (same as before) ... -->
             <div class="modal-content bg-white p-6 rounded-lg shadow-xl w-full max-w-lg">
                <h3 class="text-xl font-semibold mb-5">Add New Map Plot</h3>
                <form @submit.prevent="submitNewPlot" class="space-y-4">
                    <div>
                        <label for="new_plot_identifier" class="form-label">Plot Identifier:</label>
                        <input id="new_plot_identifier" type="text" v-model="newPlotFormData.plot_identifier" required class="form-input"/>
                        <div v-if="newPlotFormData.errors.plot_identifier" class="form-error">{{ newPlotFormData.errors.plot_identifier }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="new_plot_width" class="form-label">Width (px):</label>
                            <input id="new_plot_width" type="number" v-model.number="newPlotFormData.width" required min="1" class="form-input"/>
                            <div v-if="newPlotFormData.errors.width" class="form-error">{{ newPlotFormData.errors.width }}</div>
                        </div>
                        <div>
                            <label for="new_plot_height" class="form-label">Height (px):</label>
                            <input id="new_plot_height" type="number" v-model.number="newPlotFormData.height" required min="1" class="form-input"/>
                            <div v-if="newPlotFormData.errors.height" class="form-error">{{ newPlotFormData.errors.height }}</div>
                        </div>
                    </div>
                    <div>
                        <label for="new_plot_notes" class="form-label">Notes:</label>
                        <textarea id="new_plot_notes" v-model="newPlotFormData.notes" class="form-textarea" rows="3"></textarea>
                        <div v-if="newPlotFormData.errors.notes" class="form-error">{{ newPlotFormData.errors.notes }}</div>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="newPlotFormData.is_active" class="form-checkbox"/>
                            <span class="ml-2 text-sm text-gray-700">Is Active</span>
                        </label>
                        <div v-if="newPlotFormData.errors.is_active" class="form-error">{{ newPlotFormData.errors.is_active }}</div>
                    </div>
                    <p class="text-sm text-gray-500">Coordinates based on natural image size: (X: {{ newPlotFormData.coord_x }}, Y: {{ newPlotFormData.coord_y }})</p>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="cancelAddPlot" class="btn-secondary">Cancel</button>
                        <button type="submit" :disabled="newPlotFormData.processing" class="btn-primary">Save Plot</button>
                    </div>
                </form>
            </div>
        </div>

         <div v-if="showEditPlotModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
            <!-- ... Edit Plot Modal content (same as before) ... -->
            <div class="modal-content bg-white p-6 rounded-lg shadow-xl w-full max-w-lg">
                <h3 class="text-xl font-semibold mb-5">Edit Map Plot: {{ editPlotFormData.plot_identifier }}</h3>
                <form @submit.prevent="submitEditPlot" class="space-y-4">
                     <div>
                        <label for="edit_plot_identifier" class="form-label">Plot Identifier:</label>
                        <input id="edit_plot_identifier" type="text" v-model="editPlotFormData.plot_identifier" required class="form-input"/>
                        <div v-if="editPlotFormData.errors.plot_identifier" class="form-error">{{ editPlotFormData.errors.plot_identifier }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_coord_x" class="form-label">X Coordinate (px):</label>
                            <input id="edit_coord_x" type="number" v-model.number="editPlotFormData.coord_x" required min="0" class="form-input"/>
                            <div v-if="editPlotFormData.errors.coord_x" class="form-error">{{ editPlotFormData.errors.coord_x }}</div>
                        </div>
                        <div>
                            <label for="edit_coord_y" class="form-label">Y Coordinate (px):</label>
                            <input id="edit_coord_y" type="number" v-model.number="editPlotFormData.coord_y" required min="0" class="form-input"/>
                            <div v-if="editPlotFormData.errors.coord_y" class="form-error">{{ editPlotFormData.errors.coord_y }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_plot_width" class="form-label">Width (px):</label>
                            <input id="edit_plot_width" type="number" v-model.number="editPlotFormData.width" required min="1" class="form-input"/>
                            <div v-if="editPlotFormData.errors.width" class="form-error">{{ editPlotFormData.errors.width }}</div>
                        </div>
                        <div>
                            <label for="edit_plot_height" class="form-label">Height (px):</label>
                            <input id="edit_plot_height" type="number" v-model.number="editPlotFormData.height" required min="1" class="form-input"/>
                            <div v-if="editPlotFormData.errors.height" class="form-error">{{ editPlotFormData.errors.height }}</div>
                        </div>
                    </div>
                    <div>
                        <label for="edit_plot_notes" class="form-label">Notes:</label>
                        <textarea id="edit_plot_notes" v-model="editPlotFormData.notes" class="form-textarea" rows="3"></textarea>
                        <div v-if="editPlotFormData.errors.notes" class="form-error">{{ editPlotFormData.errors.notes }}</div>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="editPlotFormData.is_active" class="form-checkbox"/>
                            <span class="ml-2 text-sm text-gray-700">Is Active</span>
                        </label>
                         <div v-if="editPlotFormData.errors.is_active" class="form-error">{{ editPlotFormData.errors.is_active }}</div>
                    </div>
                    <div class="flex justify-between items-center pt-4">
                        <button type="button" @click="confirmDeletePlot" :disabled="editPlotFormData.processing" class="btn-danger">Delete Plot</button>
                        <div class="space-x-3">
                            <button type="button" @click="cancelEditPlot" class="btn-secondary">Cancel</button>
                            <button type="submit" :disabled="editPlotFormData.processing" class="btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.map-viewer-container img { display: block; user-select: none; }
.map-plot-existing { cursor: pointer; }
/* .map-plot-existing:hover { Tailwind classes used directly for hover now } */
.modal-overlay { z-index: 50; }
.modal-content { z-index: 51; }

.form-label { @apply block text-sm font-medium text-gray-700 mb-1; }
.form-input, .form-select, .form-textarea { @apply mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2; }
.form-checkbox { @apply rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500; }
.form-error { @apply text-red-500 text-xs mt-1; }

.btn-primary { @apply px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-400; }
.btn-secondary { @apply px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500; }
.btn-danger { @apply px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:bg-gray-400; }
</style>
