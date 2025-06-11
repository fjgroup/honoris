<template>
    <AuthenticatedLayout>
        <Head :title="'Process Shop Request #' + shopRequest.id" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Process Shop Request #{{ shopRequest.id }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Display Original Request Details -->
                        <div class="mb-6 p-4 border border-gray-200 rounded-md">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Original Request:</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <p><strong class="font-medium">User:</strong> {{ shopRequest.user?.name }}</p>
                                <p><strong class="font-medium">User ID:</strong> {{ shopRequest.user_id }}</p>
                                <p><strong class="font-medium">City:</strong> {{ shopRequest.city?.name }}</p>
                                <p><strong class="font-medium">Building Type:</strong> {{ shopRequest.building_type?.name }}</p>
                                <p class="md:col-span-2"><strong class="font-medium">Original Notes:</strong> {{ shopRequest.notes || '-' }}</p>
                                <p><strong class="font-medium">Requested:</strong> {{ new Date(shopRequest.created_at).toLocaleString() }}</p>
                                <p><strong class="font-medium">Current Status:</strong> <span :class="statusClass(shopRequest.status)" class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full">{{ shopRequest.status }}</span></p>
                            </div>
                        </div>

                        <form @submit.prevent="submitUpdate">
                            <!-- Status Update -->
                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700">Update Status</label>
                                <select id="status" v-model="form.status"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="fulfilled">Fulfilled</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
                            </div>

                            <!-- Optional: Assign Contract ID -->
                            <div class="mb-4">
                                 <label for="assigned_contract_id" class="block text-sm font-medium text-gray-700">Assign Contract ID (Optional)</label>
                                <input type="number" id="assigned_contract_id" v-model.number="form.assigned_contract_id"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="Enter Contract ID if applicable">
                                <div v-if="form.errors.assigned_contract_id" class="text-red-500 text-xs mt-1">{{ form.errors.assigned_contract_id }}</div>
                            </div>

                            <!-- Optional: Admin can edit original user notes, city, building_type -->
                            <h4 class="text-md font-semibold text-gray-700 mt-6 mb-2 border-t pt-4">Modify Original Request Details (Optional):</h4>
                             <div class="mb-4">
                                <label for="edit_city_id" class="block text-sm font-medium text-gray-700">City</label>
                                <select id="edit_city_id" v-model="form.city_id"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option v-for="city in cities" :key="city.id" :value="city.id">
                                        {{ city.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.city_id" class="text-red-500 text-xs mt-1">{{ form.errors.city_id }}</div>
                            </div>
                             <div class="mb-4">
                                <label for="edit_building_type_id" class="block text-sm font-medium text-gray-700">Building Type</label>
                                <select id="edit_building_type_id" v-model="form.building_type_id"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option v-for="buildingType in buildingTypes" :key="buildingType.id" :value="buildingType.id">
                                        {{ buildingType.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.building_type_id" class="text-red-500 text-xs mt-1">{{ form.errors.building_type_id }}</div>
                            </div>
                            <div class="mb-4">
                                <label for="edit_notes" class="block text-sm font-medium text-gray-700">Notes (User's original notes)</label>
                                <textarea id="edit_notes" v-model="form.notes" rows="3"
                                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                <div v-if="form.errors.notes" class="text-red-500 text-xs mt-1">{{ form.errors.notes }}</div>
                            </div>


                            <!-- Submit Button -->
                            <div class="flex items-center justify-end mt-6">
                                 <Link :href="route('admin.shop-requests.index')" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                    Cancel
                                </Link>
                                <button type="submit"
                                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 disabled:opacity-25 transition"
                                        :disabled="form.processing">
                                    Update Request
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Or Admin specific layout
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    shopRequest: Object,
    cities: Array,
    buildingTypes: Array,
    // availableContracts: Array, // If passing contracts for assignment
});

const form = useForm({
    status: props.shopRequest.status,
    notes: props.shopRequest.notes, // Admin can edit original notes or add admin-specific notes field
    city_id: props.shopRequest.city_id,
    building_type_id: props.shopRequest.building_type_id,
    assigned_contract_id: props.shopRequest.assigned_contract_id || null, // Ensure it's null if not set
});

const submitUpdate = () => {
    form.put(route('admin.shop-requests.update', props.shopRequest.id), {
        // onSuccess: () => { /* Controller redirects */ },
        // onError is handled by useForm automatically
    });
};

const statusClass = (status) => {
    // Copied from Index page for consistency
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'approved': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        case 'fulfilled': return 'bg-blue-100 text-blue-800';
        case 'cancelled': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<style scoped>
/* Scoped styles if needed */
</style>
