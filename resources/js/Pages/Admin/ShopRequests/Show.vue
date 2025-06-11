<template>
    <AuthenticatedLayout>
        <Head :title="'Shop Request Details - #' + shopRequest.id" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Shop Request #{{ shopRequest.id }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Request ID</h3>
                                <p class="mt-1 text-sm text-gray-900">#{{ shopRequest.id }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Status</h3>
                                <p class="mt-1 text-sm">
                                     <span :class="statusClass(shopRequest.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ shopRequest.status }}
                                    </span>
                                </p>
                            </div>
                             <div>
                                <h3 class="text-sm font-medium text-gray-500">Requested By</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ shopRequest.user?.name || 'N/A' }} (ID: {{ shopRequest.user_id }})</p>
                                <!-- Optional: Link to user's admin profile page if exists -->
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Requested City</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ shopRequest.city?.name }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Requested Building Type</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ shopRequest.building_type?.name }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <h3 class="text-sm font-medium text-gray-500">User Notes</h3>
                                <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ shopRequest.notes || '-' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Requested On</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ new Date(shopRequest.created_at).toLocaleString() }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ new Date(shopRequest.updated_at).toLocaleString() }}</p>
                            </div>

                            <div v-if="shopRequest.assigned_contract" class="md:col-span-2">
                                <h3 class="text-sm font-medium text-gray-700 mt-2 pt-2 border-t">Assigned Contract</h3>
                                <p class="mt-1 text-sm text-gray-900">
                                    Contract ID: {{ shopRequest.assigned_contract.id }}
                                    <!-- Add more contract details here if needed -->
                                    <br>Details: {{ shopRequest.assigned_contract.contract_details }}
                                </p>
                                <!-- Optional: Link to contract show/edit page -->
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between items-center">
                            <Link :href="route('admin.shop-requests.index')" class="text-sm text-indigo-600 hover:text-indigo-900">
                                &laquo; Back to All Requests
                            </Link>
                            <Link :href="route('admin.shop-requests.edit', shopRequest.id)" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded text-xs uppercase tracking-widest">
                                Edit/Process Request
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Or Admin specific layout
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    shopRequest: Object,
});

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
