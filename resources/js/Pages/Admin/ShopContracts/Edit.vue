<template>
    <AuthenticatedLayout>
        <Head :title="'Edit Shop Contract #' + shopContract.id" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Shop Contract #{{ shopContract.id }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submitEdit">
                            <!-- Map Plot -->
                            <div class="mb-4">
                                <label for="map_plot_id" class="form-label">Map Plot</label>
                                <select id="map_plot_id" v-model="form.map_plot_id" class="form-select" required>
                                    <option v-for="plot in mapPlots" :key="plot.id" :value="plot.id">
                                        {{ plot.identifier }}
                                    </option>
                                </select>
                                <div v-if="form.errors.map_plot_id" class="form-error">{{ form.errors.map_plot_id }}</div>
                                <p class="text-xs text-gray-500 mt-1">Current: {{ shopContract.map_plot?.plot_identifier }} ({{ shopContract.map_plot?.map?.name }}, {{ shopContract.map_plot?.map?.city?.name }})</p>
                            </div>

                            <!-- Owner -->
                            <div class="mb-4">
                                <label for="owner_id" class="form-label">Owner</label>
                                <select id="owner_id" v-model="form.owner_id" class="form-select" required>
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

                             <!-- Shop Request ID (Usually not changed, display for info) -->
                            <div class="mb-4" v-if="form.shop_request_id">
                                <label class="form-label">Linked Shop Request ID</label>
                                <p class="form-input-static">{{ form.shop_request_id }}</p>
                                <input type="hidden" v-model="form.shop_request_id" /> <!-- Keep it in the form if it should be part of update -->
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
                            <button type="button" @click="suggestRenewalDates" class="text-sm text-indigo-600 hover:text-indigo-800 mb-4">Suggest Renewal Dates</button>

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

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('admin.shop-contracts.index')" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                    Cancel
                                </Link>
                                <button type="submit" class="btn-primary" :disabled="form.processing">
                                    Update Contract
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

const props = defineProps({
    shopContract: Object,
    mapPlots: Array,
    owners: Array,
    buildingTypes: Array,
    users: Array,
});

const form = useForm({
    map_plot_id: props.shopContract.map_plot_id,
    owner_id: props.shopContract.owner_id,
    building_type_id: props.shopContract.building_type_id,
    assigned_to_user_id: props.shopContract.assigned_to_user_id,
    shop_request_id: props.shopContract.shop_request_id, // Typically not editable but good to keep if it's part of the form data
    start_date: props.shopContract.start_date.split('T')[0], // Format YYYY-MM-DD for date input
    end_date: props.shopContract.end_date.split('T')[0],     // Format YYYY-MM-DD
    status: props.shopContract.status,
});

const submitEdit = () => {
    form.put(route('admin.shop-contracts.update', props.shopContract.id));
};

const suggestRenewalDates = () => {
    if (!form.end_date) return; // Need a current end date

    const currentEndDate = new Date(form.end_date);

    // New start date: day after current end date, or next 4th if that's later
    let newStartDate = new Date(currentEndDate);
    newStartDate.setDate(currentEndDate.getDate() + 1); // Day after current end date

    let next4th = new Date(newStartDate.getFullYear(), newStartDate.getMonth(), 4);
    if (newStartDate.getDate() > 4) { // If current end_date + 1 is past the 4th of its month
        next4th.setMonth(next4th.getMonth() + 1);
        if (next4th.getMonth() > 11) { // Rollover year
            next4th.setFullYear(next4th.getFullYear() + 1);
            next4th.setMonth(0);
        }
    }
     // Choose the later of (currentEndDate+1) or the next 4th
    form.start_date = (newStartDate > next4th && newStartDate.getDate() <=4 ) ?
                       `${newStartDate.getFullYear()}-${String(newStartDate.getMonth() + 1).padStart(2, '0')}-${String(newStartDate.getDate()).padStart(2, '0')}` :
                       `${next4th.getFullYear()}-${String(next4th.getMonth() + 1).padStart(2, '0')}-04`;


    // New end date: 3rd of the month after new start_date's month
    let finalStartDate = new Date(form.start_date); // Use the chosen start date
    let newEndDateMonth = finalStartDate.getMonth() + 1;
    let newEndDateYear = finalStartDate.getFullYear();
    if (newEndDateMonth > 11) {
        newEndDateMonth = 0;
        newEndDateYear += 1;
    }
    form.end_date = `${newEndDateYear}-${String(newEndDateMonth + 1).padStart(2, '0')}-03`;
    form.status = 'pending_renewal'; // Suggest changing status
};

</script>

<style scoped>
.form-label {
    @apply block text-sm font-medium text-gray-700 mb-1;
}
.form-input, .form-select {
    @apply mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2;
}
.form-input-static {
    @apply mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 p-2;
}
.form-error {
    @apply text-red-500 text-xs mt-1;
}
.btn-primary {
    @apply px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150;
}
</style>
