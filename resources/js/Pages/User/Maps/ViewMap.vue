<template>
    <AuthenticatedLayout>
        <Head title="View Shops on Map" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">View Shops on Map</h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <!-- City and Map Selectors -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="city_selector_user" class="block text-sm font-medium text-gray-700">Select City:</label>
                            <select id="city_selector_user" v-model="selectedCityId" @change="fetchMapsForCity"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    :disabled="isLoadingCities">
                                <option :value="null" disabled>{{ isLoadingCities ? 'Loading...' : 'Select City' }}</option>
                                <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="map_selector_user" class="block text-sm font-medium text-gray-700">Select Map:</label>
                            <select id="map_selector_user" v-model="selectedMapId" @change="fetchMapData"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    :disabled="isLoadingMaps || !selectedCityId">
                                <option :value="null" disabled>{{ isLoadingMaps ? 'Loading...' : (mapsForCity.length === 0 && selectedCityId ? 'No maps for this city' : 'Select Map') }}</option>
                                <option v-for="mapItem in mapsForCity" :key="mapItem.id" :value="mapItem.id">{{ mapItem.name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Map Display Area -->
                    <div v-if="isLoadingMapDetails" class="text-center py-10">Loading map details...</div>
                    <div v-if="selectedMap && selectedMap.image_path" class="map-display-area relative" ref="mapDisplayRef">
                        <img :src="selectedMap.image_path" alt="Map" @load="onMapImageLoad" class="max-w-full h-auto mx-auto block" ref="mapImageRef"/>

                        <div v-for="contract in visibleContracts" :key="contract.id"
                             class="absolute transform -translate-x-1/2 -translate-y-1/2"
                             :style="getPlotMarkerStyle(contract)"
                             @mouseover="showContractDetails(contract, $event)"
                             @mouseleave="hideContractDetails"
                             @click="showContractDetails(contract, $event)"> <!-- Click for touch devices -->
                            <img v-if="contract.building_type?.icon_path" :src="contract.building_type.icon_path" :alt="contract.building_type.name" class="w-8 h-8 object-contain" :title="contract.building_type.name"/>
                            <div v-else class="w-6 h-6 bg-blue-500 rounded-full border-2 border-white shadow-md" :title="contract.building_type.name"></div>
                        </div>

                        <!-- Hover/Click Details Popup -->
                        <div v-if="hoveredContractDetails"
                             class="absolute bg-white p-3 shadow-lg rounded-md border border-gray-300 text-sm z-10"
                             :style="{ top: popupPosition.top + 'px', left: popupPosition.left + 'px' }">
                            <h4 class="font-semibold text-gray-800">{{ hoveredContractDetails.building_type?.name }}</h4>
                            <p class="text-gray-600">Owner Lang: {{ hoveredContractDetails.owner?.language_preference }}</p>
                            <p v-if="hoveredContractDetails.owner?.is_for_all_users" class="text-xs text-green-600">Publicly Listed</p>
                            <!-- Add more details as needed -->
                        </div>
                    </div>
                    <div v-if="isLoadingContracts" class="text-center py-10">Loading contract data...</div>
                     <div v-if="selectedMapId && !selectedMap && !isLoadingMapDetails" class="text-center py-10 text-gray-500">
                        Select a map to view stores.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';

const page = usePage();
const authUser = page.props.auth.user;

// Reactive State
const cities = ref([]);
const selectedCityId = ref(null);
const mapsForCity = ref([]);
const selectedMapId = ref(null);
const selectedMap = ref(null); // Holds { image_path, width, height (natural from DB if stored) }
const visibleContracts = ref([]); // Active shop contracts

const mapImageNaturalDimensions = ref({ width: 0, height: 0 });
const mapDisplayDimensions = ref({ width: 0, height: 0 }); // Actual on-screen size
const mapDisplayRef = ref(null); // Ref for the map container div
const mapImageRef = ref(null); // Ref for the map image itself

const isLoadingCities = ref(false);
const isLoadingMaps = ref(false);
const isLoadingMapDetails = ref(false);
const isLoadingContracts = ref(false);

const hoveredContractDetails = ref(null);
const popupPosition = ref({ top: 0, left: 0 });


// Methods
const fetchCities = async () => {
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

const fetchMapsForCity = async () => {
    if (!selectedCityId.value) {
        mapsForCity.value = []; selectedMapId.value = null; selectedMap.value = null; visibleContracts.value = [];
        return;
    }
    isLoadingMaps.value = true;
    mapsForCity.value = []; selectedMapId.value = null; selectedMap.value = null; visibleContracts.value = [];
    try {
        const response = await axios.get(route('admin.maps.index', { city_id: selectedCityId.value }));
        mapsForCity.value = response.data;
    } catch (error) {
        console.error("Error fetching maps for city:", error);
    } finally {
        isLoadingMaps.value = false;
    }
};

const fetchMapData = async () => {
    if (!selectedMapId.value) {
        selectedMap.value = null; visibleContracts.value = [];
        return;
    }
    isLoadingMapDetails.value = true; isLoadingContracts.value = true;
    selectedMap.value = null; visibleContracts.value = []; // Clear previous
    try {
        // Fetch map details
        const mapDetailsResponse = await axios.get(route('admin.maps.show', selectedMapId.value));
        selectedMap.value = mapDetailsResponse.data; // Expects { id, name, image_path, width, height (natural) }

        // Fetch active contracts for this map
        // Assuming a new route: /api/maps/{mapId}/active-contracts
        const contractsResponse = await axios.get(route('api.maps.active-contracts', { map: selectedMapId.value }));
        visibleContracts.value = contractsResponse.data;

    } catch (error) {
        console.error("Error fetching map data or contracts:", error);
        selectedMap.value = null; // Clear on error
    } finally {
        isLoadingMapDetails.value = false;
        isLoadingContracts.value = false;
    }
};

const onMapImageLoad = async (event) => {
    const img = event.target;
    mapImageNaturalDimensions.value = { width: img.naturalWidth, height: img.naturalHeight };

    // Wait for next tick to ensure DOM is updated if image causes container resize
    await nextTick();
    if (mapDisplayRef.value) { // Or mapImageRef.value if img is directly styled for size
         mapDisplayDimensions.value = {
            width: img.offsetWidth,
            height: img.offsetHeight
        };
    }
};

const getPlotMarkerStyle = (contract) => {
    if (!contract.map_plot || !mapImageNaturalDimensions.value.width || !mapDisplayDimensions.value.width) {
        return { display: 'none' }; // Not ready to calculate
    }

    const plot = contract.map_plot;
    const scaleX = mapDisplayDimensions.value.width / mapImageNaturalDimensions.value.width;
    const scaleY = mapDisplayDimensions.value.height / mapImageNaturalDimensions.value.height;

    // Calculate center of the plot for marker positioning
    const centerX = (plot.coord_x + plot.width / 2) * scaleX;
    const centerY = (plot.coord_y + plot.height / 2) * scaleY;

    return {
        left: `${centerX}px`,
        top: `${centerY}px`,
        // Optional: scale marker size too, or keep fixed
        // width: `${plot.width * scaleX}px`, // This would make marker cover the plot
        // height: `${plot.height * scaleY}px`,
    };
};

const showContractDetails = (contract, event) => {
    hoveredContractDetails.value = contract;
    // Position popup near cursor/element, adjust as needed
    if (mapDisplayRef.value && event) {
        const mapRect = mapDisplayRef.value.getBoundingClientRect();
        // Position relative to the map container
        popupPosition.value = {
            top: event.clientY - mapRect.top + 20, // Offset Y
            left: event.clientX - mapRect.left + 20, // Offset X
        };
    }
};

const hideContractDetails = () => {
    hoveredContractDetails.value = null;
};

// Lifecycle Hooks
onMounted(() => {
    fetchCities();
});

// Watch for selectedMapId changes to potentially reset display dimensions or contracts if map changes before image loads
watch(selectedMapId, (newId, oldId) => {
    if (newId !== oldId) {
        mapDisplayDimensions.value = { width: 0, height: 0 }; // Reset until new image loads
        mapImageNaturalDimensions.value = { width: 0, height: 0 };
        visibleContracts.value = []; // Clear contracts until new ones are fetched
        hoveredContractDetails.value = null;
    }
});

</script>

<style scoped>
.map-display-area {
    /* Ensure this container establishes a positioning context if not already done by 'relative' class */
}
.map-display-area img {
    user-select: none;
}
/* Add more styles for markers, popups etc. */
</style>
