<template>
    <AuthenticatedLayout>
        <Head title="Request New Shop" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Request a New Shop</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submitRequest">
                            <!-- City Selection -->
                            <div class="mb-4">
                                <label for="city_id" class="block text-sm font-medium text-gray-700">City</label>
                                <select id="city_id" v-model="form.city_id"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                        required>
                                    <option value="null" disabled>Select a city</option>
                                    <option v-for="city in cities" :key="city.id" :value="city.id">
                                        {{ city.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.city_id" class="text-red-500 text-xs mt-1">{{ form.errors.city_id }}</div>
                            </div>

                            <!-- Building Type Selection -->
                            <div class="mb-4">
                                <label for="building_type_id" class="block text-sm font-medium text-gray-700">Building Type</label>
                                <select id="building_type_id" v-model="form.building_type_id"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                        required>
                                    <option value="null" disabled>Select a building type</option>
                                    <option v-for="buildingType in buildingTypes" :key="buildingType.id" :value="buildingType.id">
                                        {{ buildingType.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.building_type_id" class="text-red-500 text-xs mt-1">{{ form.errors.building_type_id }}</div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                                <textarea id="notes" v-model="form.notes" rows="4"
                                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                <div v-if="form.errors.notes" class="text-red-500 text-xs mt-1">{{ form.errors.notes }}</div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('shop-requests.index')" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                    Cancel
                                </Link>
                                <button type="submit"
                                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 disabled:opacity-25 transition"
                                        :disabled="form.processing">
                                    Submit Request
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Adjust if your layout path is different
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    cities: Array,
    buildingTypes: Array,
});

const form = useForm({
    city_id: null,
    building_type_id: null,
    notes: '',
});

const submitRequest = () => {
    form.post(route('shop-requests.store'), {
        onSuccess: () => {
            form.reset();
            // Inertia will automatically redirect to shop-requests.index due to controller response,
            // or one could force it here if needed: router.visit(route('shop-requests.index'))
        },
        // onError handled by useForm automatically populating form.errors
    });
};
</script>

<style scoped>
/* Scoped styles if needed */
</style>
