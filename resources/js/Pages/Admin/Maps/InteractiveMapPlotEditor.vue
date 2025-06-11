<template>
    <div>
        <Head title="Interactive Map Plot Editor" />

        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-semibold mb-4">Interactive Map Plot Editor</h1>

            <!-- City Selector -->
            <div class="mb-4">
                <label for="city_selector" class="block text-sm font-medium text-gray-700">Select City:</label>
                <select id="city_selector" v-model="selectedCityId" @change="fetchMapsForCity"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        :disabled="isLoadingCities">
                    <option value="null" disabled>{{ isLoadingCities ? 'Loading cities...' : 'Select a city' }}</option>
                    <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                </select>
            </div>

            <!-- Map Selector -->
            <div class="mb-4" v-if="selectedCityId && mapsForCity.length > 0 || isLoadingMaps">
                <label for="map_selector" class="block text-sm font-medium text-gray-700">Select Map:</label>
                <select id="map_selector" v-model="selectedMapId" @change="fetchMapDetailsAndPlots"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        :disabled="isLoadingMaps || !selectedCityId">
                    <option value="null" disabled>{{ isLoadingMaps ? 'Loading maps...' : (mapsForCity.length === 0 && selectedCityId ? 'No maps for this city' : 'Select a map') }}</option>
                    <option v-for="mapItem in mapsForCity" :key="mapItem.id" :value="mapItem.id">{{ mapItem.name }}</option>
                </select>
            </div>
            <div v-else-if="selectedCityId && !isLoadingMaps && mapsForCity.length === 0" class="text-gray-500 text-sm">
                No maps found for the selected city.
            </div>

            <!-- Add Plot Button -->
            <div class="my-4" v-if="selectedMapId">
                <button @click="toggleAddMode"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:bg-gray-400"
                        :disabled="!selectedMapId || showEditPlotModal"> <!-- Disable if editing -->
                    {{ addModeActive ? 'Cancel Add Plot' : 'Add New Plot' }}
                </button>
            </div>

            <!-- Map Viewer -->
            <div class="map-viewer-container mt-6" v-if="selectedMapImageUrl">
                <h2 class="text-xl font-semibold mb-2">Map: {{ mapsForCity.find(m => m.id === selectedMapId)?.name }}</h2>
                <div class="map-image-container relative border border-gray-400"
                     ref="mapImageContainerRef"
                     @click="handleMapClick"
                     :style="{
                        width: mapImageDimensions.width ? mapImageDimensions.width + 'px' : 'auto',
                        height: mapImageDimensions.height ? mapImageDimensions.height + 'px' : 'auto',
                        cursor: addModeActive ? 'crosshair' : 'default'
                     }">
                    <img ref="mapImage" :src="selectedMapImageUrl" alt="Selected Map" @load="onMapImageLoad" class="block max-w-full max-h-[70vh]"/>

                    <!-- Existing Plots -->
                    <div v-for="plot in mapPlots" :key="plot.id"
                         class="map-plot-existing absolute border border-red-500 bg-red-500 bg-opacity-30 flex items-center justify-center"
                         :style="getPlotStyle(plot)"
                         @click.stop="handleEditPlotClick(plot)"> <!-- Use .stop to prevent map click -->
                        <span class="text-white text-xs font-bold p-1 bg-black bg-opacity-50 rounded-sm pointer-events-none">{{ plot.plot_identifier }}</span>
                    </div>

                    <!-- New Plot Preview Marker -->
                    <div v-if="addModeActive && newPlotPreviewCoords.x !== null"
                         class="new-plot-preview-marker absolute w-2 h-2 bg-green-500 rounded-full -translate-x-1/2 -translate-y-1/2 pointer-events-none"
                         :style="{ left: newPlotPreviewCoords.x + 'px', top: newPlotPreviewCoords.y + 'px' }">
                    </div>
                </div>
                 <div v-if="isLoadingMapPlots" class="text-center py-4">Loading map plots...</div>
            </div>
            <div v-else-if="selectedMapId && !selectedMapImageUrl && !isLoadingMaps" class="text-gray-500 text-sm mt-6">
                Loading map image...
            </div>
        </div>

        <!-- Add Plot Modal -->
        <div v-if="showAddPlotModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="modal-content bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Add New Map Plot</h3>
                <form @submit.prevent="submitNewPlot">
                    <!-- Form fields as defined before -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700">Plot Identifier:</label>
                        <input type="text" v-model="newPlotFormData.plot_identifier" required />
                        <div v-if="newPlotFormData.errors.plot_identifier" class="form-error">{{ newPlotFormData.errors.plot_identifier }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <label>Width:</label><input type="number" v-model.number="newPlotFormData.width" required min="1"/>
                            <div v-if="newPlotFormData.errors.width" class="form-error">{{ newPlotFormData.errors.width }}</div>
                        </div>
                        <div>
                            <label>Height:</label><input type="number" v-model.number="newPlotFormData.height" required min="1"/>
                            <div v-if="newPlotFormData.errors.height" class="form-error">{{ newPlotFormData.errors.height }}</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Notes:</label><textarea v-model="newPlotFormData.notes"></textarea>
                        <div v-if="newPlotFormData.errors.notes" class="form-error">{{ newPlotFormData.errors.notes }}</div>
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center"><input type="checkbox" v-model="newPlotFormData.is_active" class="mr-2"/>Is Active</label>
                        <div v-if="newPlotFormData.errors.is_active" class="form-error">{{ newPlotFormData.errors.is_active }}</div>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">Coords: (X: {{ newPlotFormData.coord_x }}, Y: {{ newPlotFormData.coord_y }})</p>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="cancelAddPlot" class="btn-secondary">Cancel</button>
                        <button type="submit" :disabled="newPlotFormData.processing" class="btn-primary">Save Plot</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Plot Modal -->
        <div v-if="showEditPlotModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="modal-content bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Edit Map Plot</h3>
                <form @submit.prevent="submitEditPlot">
                    <div class="mb-3">
                        <label>Plot Identifier:</label>
                        <input type="text" v-model="editPlotFormData.plot_identifier" required />
                        <div v-if="editPlotFormData.errors.plot_identifier" class="form-error">{{ editPlotFormData.errors.plot_identifier }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <label>X Coordinate:</label>
                            <input type="number" v-model.number="editPlotFormData.coord_x" required />
                            <div v-if="editPlotFormData.errors.coord_x" class="form-error">{{ editPlotFormData.errors.coord_x }}</div>
                        </div>
                        <div>
                            <label>Y Coordinate:</label>
                            <input type="number" v-model.number="editPlotFormData.coord_y" required />
                            <div v-if="editPlotFormData.errors.coord_y" class="form-error">{{ editPlotFormData.errors.coord_y }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <label>Width:</label>
                            <input type="number" v-model.number="editPlotFormData.width" required min="1"/>
                            <div v-if="editPlotFormData.errors.width" class="form-error">{{ editPlotFormData.errors.width }}</div>
                        </div>
                        <div>
                            <label>Height:</label>
                            <input type="number" v-model.number="editPlotFormData.height" required min="1"/>
                            <div v-if="editPlotFormData.errors.height" class="form-error">{{ editPlotFormData.errors.height }}</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Notes:</label>
                        <textarea v-model="editPlotFormData.notes"></textarea>
                        <div v-if="editPlotFormData.errors.notes" class="form-error">{{ editPlotFormData.errors.notes }}</div>
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center"><input type="checkbox" v-model="editPlotFormData.is_active" class="mr-2"/>Is Active</label>
                        <div v-if="editPlotFormData.errors.is_active" class="form-error">{{ editPlotFormData.errors.is_active }}</div>
                    </div>
                    <div class="flex justify-between items-center mt-6">
                        <button type="button" @click="confirmDeletePlot" :disabled="editPlotFormData.processing" class="delete-button px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:bg-gray-400">
                            Delete Plot
                        </button>
                        <div class="space-x-3">
                            <button type="button" @click="cancelEditPlot" class="btn-secondary">Cancel</button>
                            <button type="submit" :disabled="editPlotFormData.processing" class="btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'; // Removed watch as it's not used
import axios from 'axios';
import { Head, useForm } from '@inertiajs/vue3';

// --- Reactive State ---
const cities = ref([]);
const selectedCityId = ref(null);
const mapsForCity = ref([]);
const selectedMapId = ref(null);
const selectedMapImageUrl = ref(null);
const mapPlots = ref([]);

const isLoadingCities = ref(false);
const isLoadingMaps = ref(false);
const isLoadingMapDetails = ref(false);
const isLoadingMapPlots = ref(false);

const mapImageDimensions = ref({ width: 0, height: 0, naturalWidth: 0, naturalHeight: 0 });
const mapImageContainerRef = ref(null);

// Add Plot Mode State
const addModeActive = ref(false);
const showAddPlotModal = ref(false);
const newPlotPreviewCoords = ref({ x: null, y: null });
const newPlotFormData = useForm({
    map_id: null, plot_identifier: '', coord_x: null, coord_y: null,
    width: 50, height: 50, notes: '', is_active: true,
});

// Edit Plot Mode State
const editingPlot = ref(null);
const showEditPlotModal = ref(false);
const editPlotFormData = useForm({
    map_id: null, plot_identifier: '', coord_x: null, coord_y: null,
    width: 0, height: 0, notes: '', is_active: true,
});


// --- Methods ---
const fetchCities = async () => { /* ... (same as before) ... */
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

const fetchMapsForCity = async () => { /* ... (same as before) ... */
    if (!selectedCityId.value || selectedCityId.value === 'null') {
        mapsForCity.value = []; selectedMapId.value = null; selectedMapImageUrl.value = null; mapPlots.value = [];
        return;
    }
    isLoadingMaps.value = true;
    mapsForCity.value = []; selectedMapId.value = null; selectedMapImageUrl.value = null; mapPlots.value = [];
    try {
        const response = await axios.get(route('admin.maps.index', { city_id: selectedCityId.value }));
        mapsForCity.value = response.data;
    } catch (error) {
        console.error("Error fetching maps for city:", error);
    } finally {
        isLoadingMaps.value = false;
    }
};

const fetchMapDetailsAndPlots = async () => { /* ... (same as before, ensure mapPlots.value is correctly assigned) ... */
    if (!selectedMapId.value || selectedMapId.value === 'null') {
        selectedMapImageUrl.value = null; mapPlots.value = [];
        mapImageDimensions.value = { width: 0, height: 0, naturalWidth: 0, naturalHeight: 0 };
        return;
    }
    isLoadingMapDetails.value = true; isLoadingMapPlots.value = true;
    selectedMapImageUrl.value = null; mapPlots.value = [];
    try {
        const mapDetailsResponse = await axios.get(route('admin.maps.show', selectedMapId.value));
        selectedMapImageUrl.value = mapDetailsResponse.data.image_path;

        const plotsResponse = await axios.get(route('admin.map-plots.index', { map_id: selectedMapId.value }));
        mapPlots.value = plotsResponse.data.map_plots.data; // Ensure this path is correct
    } catch (error) {
        console.error("Error fetching map details or plots:", error);
        selectedMapImageUrl.value = null;
    } finally {
        isLoadingMapDetails.value = false; isLoadingMapPlots.value = false;
    }
};

const onMapImageLoad = (event) => { /* ... (same as before) ... */
    const img = event.target;
    mapImageDimensions.value = {
        width: img.offsetWidth, height: img.offsetHeight,
        naturalWidth: img.naturalWidth, naturalHeight: img.naturalHeight,
    };
    if(addModeActive.value) {
        newPlotPreviewCoords.value = { x: null, y: null };
    }
    if(showEditPlotModal.value) { // If editing, may need to re-evaluate styles if map reloads/resizes
        // This might be complex if plots are also resizable via handles.
        // For now, assume plot dimensions in form are what user wants.
    }
};

const getPlotStyle = (plot) => { /* ... (same scaling logic as before) ... */
    if (mapImageDimensions.value.naturalWidth > 0 && mapImageDimensions.value.naturalHeight > 0 &&
        mapImageDimensions.value.width > 0 && mapImageDimensions.value.height > 0) {
        const scaleX = mapImageDimensions.value.width / mapImageDimensions.value.naturalWidth;
        const scaleY = mapImageDimensions.value.height / mapImageDimensions.value.naturalHeight;

        return {
            left: `${plot.coord_x * scaleX}px`,
            top: `${plot.coord_y * scaleY}px`,
            width: `${plot.width * scaleX}px`,
            height: `${plot.height * scaleY}px`,
        };
    }
    return { // Fallback or if no scaling needed
        left: `${plot.coord_x}px`, top: `${plot.coord_y}px`,
        width: `${plot.width}px`, height: `${plot.height}px`,
    };
};

// --- Add Plot Methods ---
const toggleAddMode = () => { /* ... (same as before) ... */
    addModeActive.value = !addModeActive.value;
    if (!addModeActive.value) {
        showAddPlotModal.value = false;
        newPlotPreviewCoords.value = { x: null, y: null };
        newPlotFormData.reset();
    } else {
        // Ensure not in edit mode when entering add mode
        cancelEditPlot();
    }
};

const handleMapClick = (event) => { /* ... (same scaling logic as before) ... */
    if (!addModeActive.value || !mapImageContainerRef.value || showEditPlotModal.value) return;

    const rect = mapImageContainerRef.value.getBoundingClientRect();
    const clickX_on_displayed_image = event.clientX - rect.left;
    const clickY_on_displayed_image = event.clientY - rect.top;

    let actualX = clickX_on_displayed_image;
    let actualY = clickY_on_displayed_image;

    if (mapImageDimensions.value.naturalWidth > 0 && mapImageDimensions.value.naturalHeight > 0 &&
        (mapImageDimensions.value.width !== mapImageDimensions.value.naturalWidth ||
         mapImageDimensions.value.height !== mapImageDimensions.value.naturalHeight)) {
        const scaleX = mapImageDimensions.value.naturalWidth / mapImageDimensions.value.width;
        const scaleY = mapImageDimensions.value.naturalHeight / mapImageDimensions.value.height;
        actualX = clickX_on_displayed_image * scaleX;
        actualY = clickY_on_displayed_image * scaleY;
    }

    newPlotPreviewCoords.value = { x: clickX_on_displayed_image, y: clickY_on_displayed_image };
    newPlotFormData.coord_x = Math.round(actualX);
    newPlotFormData.coord_y = Math.round(actualY);
    newPlotFormData.map_id = selectedMapId.value;
    showAddPlotModal.value = true;
};

const submitNewPlot = () => { /* ... (same as before) ... */
    newPlotFormData.post(route('admin.map-plots.store'), {
        preserveScroll: true,
        onSuccess: () => {
            fetchMapDetailsAndPlots();
            toggleAddMode();
        },
        onError: (errors) => { console.error('Error saving plot:', errors); }
    });
};

const cancelAddPlot = () => { /* ... (same as before) ... */
    showAddPlotModal.value = false;
    newPlotFormData.reset();
    addModeActive.value = false;
    newPlotPreviewCoords.value = { x: null, y: null };
};

// --- Edit/Delete Plot Methods ---
const handleEditPlotClick = (plot) => {
    if (addModeActive.value) return; // Don't edit if in add mode

    editingPlot.value = plot;
    editPlotFormData.reset(); // Clear previous form data/errors

    // Populate form with plot data - ensure these are the raw, unscaled values
    editPlotFormData.map_id = plot.map_id;
    editPlotFormData.plot_identifier = plot.plot_identifier;
    editPlotFormData.coord_x = plot.coord_x; // These should be natural map coordinates
    editPlotFormData.coord_y = plot.coord_y;
    editPlotFormData.width = plot.width;     // Natural width
    editPlotFormData.height = plot.height;   // Natural height
    editPlotFormData.notes = plot.notes;
    editPlotFormData.is_active = plot.is_active;

    showEditPlotModal.value = true;
};

const submitEditPlot = () => {
    if (!editingPlot.value) return;
    editPlotFormData.put(route('admin.map-plots.update', editingPlot.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            fetchMapDetailsAndPlots();
            cancelEditPlot();
        },
        onError: (errors) => { console.error('Error updating plot:', errors); }
    });
};

const confirmDeletePlot = () => {
    if (!editingPlot.value) return;
    if (confirm('Are you sure you want to delete this plot? This action cannot be undone.')) {
        deletePlot();
    }
};

const deletePlot = () => {
    if (!editingPlot.value) return;
    const plotIdToDelete = editingPlot.value.id;
    // Use a form instance for delete to handle processing state, even if it's editPlotFormData
    editPlotFormData.delete(route('admin.map-plots.destroy', plotIdToDelete), {
        preserveScroll: true,
        onSuccess: () => {
            fetchMapDetailsAndPlots();
            cancelEditPlot();
        },
        onError: (errors) => {
            console.error('Error deleting plot:', errors);
            // Handle specific errors, e.g. if server returns 403 or other issues
            alert('Could not delete plot. Please check console for errors.');
        }
    });
};

const cancelEditPlot = () => {
    showEditPlotModal.value = false;
    editingPlot.value = null;
    editPlotFormData.reset();
};

// --- Lifecycle Hooks ---
onMounted(() => {
    fetchCities();
});

</script>

<style scoped>
.map-viewer-container img {
    display: block;
    user-select: none;
}
.map-plot-existing {
    cursor: pointer;
    /* Add hover effect */
}
.map-plot-existing:hover {
    border-color: #3b82f6; /* blue-500 */
    background-color: rgba(59, 130, 246, 0.4); /* blue-500 with opacity */
}
.modal-overlay {
    z-index: 1000;
}
.modal-content {
    z-index: 1001;
}
.new-plot-preview-marker {
    pointer-events: none;
}

/* Basic general input/label/button styling (can be Tailwind components or global styles) */
input[type="text"], input[type="number"], textarea {
    @apply mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2;
}
label {
    @apply block text-sm font-medium text-gray-700 mb-1;
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
.delete-button { /* Specific styling for delete button if different from btn-secondary */
    /* Example: */
    /* background-color: #ef4444; /* red-500 */
    /* color: white; */
}
.delete-button:hover {
    /* background-color: #dc2626; /* red-600 */
}
</style>
