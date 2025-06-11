<template>
    <AuthenticatedLayout>
        <Head title="Create New Shop Contract" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New Shop Contract</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div v-if="shopRequestDetails" class="mb-6 p-4 border border-blue-200 rounded-md bg-blue-50">
                            <h3 class="text-lg font-medium text-blue-800">Fulfilling Shop Request #{{ shopRequestDetails.id }}</h3>
                            <p><strong>User:</strong> {{ shopRequestDetails.user?.name }}</p>
                            <p><strong>Requested City:</strong> {{ shopRequestDetails.city?.name }}</p>
                            <p><strong>Requested Building Type:</strong> {{ shopRequestDetails.building_type?.name }}</p>
                        </div>

                        <form @submit.prevent="submitCreate">
                            <!-- Map Plot -->
                            <div class="mb-4">
                                <label for="map_plot_id" class="form-label">Map Plot</label>
                                <select id="map_plot_id" v-model="form.map_plot_id" class="form-select" required>
                                    <option :value="null" disabled>Select a map plot</option>
                                    <option v-for="plot in mapPlots" :key="plot.id" :value="plot.id">
                                        {{ plot.identifier }}
                                    </option>
                                </select>
                                <div v-if="form.errors.map_plot_id" class="form-error">{{ form.errors.map_plot_id }}</div>
                            </div>

                            <!-- Owner -->
                            <div class="mb-4">
                                <label for="owner_id" class="form-label">Owner</label>
                                <select id="owner_id" v-model="form.owner_id" class="form-select" required>
                                    <option :value="null" disabled>Select an owner</option>
                                    <option v-for="owner in owners" :key="owner.id" :value="owner.id">
                                        {{ owner.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.owner_id" class="form-error">{{ form.errors.owner_id }}</div>
                            </div>

                            <!-- Building Type -->
                            <div class="mb-4">
                                <label for="building_type_id" class="form-label">Building Type</label>
                                <select id="building_type_id" v-model="form.building_type_id" class="form-select" required>
                                    <option :value="null" disabled>Select a building type</option>
                                    <option v-for="bt in buildingTypes" :key="bt.id" :value="bt.id">
                                        {{ bt.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.building_type_id" class="form-error">{{ form.errors.building_type_id }}</div>
                            </div>

                            <!-- Assigned User (Optional) -->
                            <div class="mb-4">
                                <label for="assigned_to_user_id" class="form-label">Assign to User (Optional)</label>
                                <select id="assigned_to_user_id" v-model="form.assigned_to_user_id" class="form-select">
                                    <option :value="null">None</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.assigned_to_user_id" class="form-error">{{ form.errors.assigned_to_user_id }}</div>
                            </div>

                            <!-- Dates -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" id="start_date" v-model="form.start_date" class="form-input" required />
                                    <div v-if="form.errors.start_date" class="form-error">{{ form.errors.start_date }}</div>
                                </div>
                                <div>
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" id="end_date" v-model="form.end_date" class="form-input" required />
                                    <div v-if="form.errors.end_date" class="form-error">{{ form.errors.end_date }}</div>
                                </div>
                            </div>
                             <button type="button" @click="suggestDates" class="text-sm text-indigo-600 hover:text-indigo-800 mb-4">Suggest Standard Dates</button>


                            <!-- Status -->
                            <div class="mb-4">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" v-model="form.status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="expired">Expired</option>
                                    <option value="pending_renewal">Pending Renewal</option>
                                </select>
                                <div v-if="form.errors.status" class="form-error">{{ form.errors.status }}</div>
                            </div>

                            <input type="hidden" v-model="form.shop_request_id" />

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('admin.shop-contracts.index')" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                    Cancel
                                </Link>
                                <button type="submit" class="btn-primary" :disabled="form.processing">
                                    Create Contract
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';

const props = defineProps({
    mapPlots: Array,
    owners: Array,
    buildingTypes: Array,
    users: Array,
    shopRequestId: [String, Number, null],
    shopRequestDetails: [Object, null],
});

const form = useForm({
    map_plot_id: null,
    owner_id: null,
    building_type_id: null,
    assigned_to_user_id: null,
    shop_request_id: props.shopRequestId || null,
    start_date: '',
    end_date: '',
    status: 'active',
});

const submitCreate = () => {
    form.post(route('admin.shop-contracts.store'), {
        // onSuccess: () => form.reset(), // Controller redirects, so reset might not be needed here
    });
};

const suggestDates = () => {
    const today = new Date();
    let year = today.getFullYear();
    let month = today.getMonth(); // 0-11

    // Start date: Next 4th, or current 4th if today is before/on 4th and it's not past.
    let startDateMonth = month;
    let startDateYear = year;
    if (today.getDate() > 4) {
        startDateMonth += 1;
        if (startDateMonth > 11) {
            startDateMonth = 0;
            startDateYear += 1;
        }
    }
    form.start_date = `${startDateYear}-${String(startDateMonth + 1).padStart(2, '0')}-04`;

    // End date: 3rd of the month after start_date's month
    let endDateMonth = startDateMonth + 1;
    let endDateYear = startDateYear;
    if (endDateMonth > 11) {
        endDateMonth = 0;
        endDateYear += 1;
    }
    form.end_date = `${endDateYear}-${String(endDateMonth + 1).padStart(2, '0')}-03`;
};


onMounted(() => {
    if (props.shopRequestDetails) {
        form.assigned_to_user_id = props.shopRequestDetails.user_id; // Assign to the user who requested
        form.building_type_id = props.shopRequestDetails.building_type_id;
        // City is part of map_plot, so user needs to select an available plot in the requested city.
        // Owner is also an admin decision.
        suggestDates(); // Suggest dates if fulfilling a request
    }
});

// Watch for changes in shopRequestDetails if it could be loaded asynchronously (not typical for Inertia props)
// watch(() => props.shopRequestDetails, (newDetails) => {
//     if (newDetails) {
//         form.assigned_to_user_id = newDetails.user_id;
//         form.building_type_id = newDetails.building_type_id;
//     }
// }, { immediate: true }); // immediate might be needed if prop is available right away

</script>

<style scoped>
.form-label {
    @apply block text-sm font-medium text-gray-700 mb-1;
}
.form-input, .form-select {
    @apply mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2;
}
.form-error {
    @apply text-red-500 text-xs mt-1;
}
.btn-primary {
    @apply px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150;
}
</style>
