<template>
    <AuthenticatedLayout>
        <Head title="My Shop Requests" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Shop Requests</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-4">
                            <Link :href="route('shop-requests.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Request New Shop
                            </Link>
                        </div>

                        <div v-if="shopRequests.data.length > 0">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Building Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested On</th>
                                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">View</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="request in shopRequests.data" :key="request.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ request.city?.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ request.building_type?.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="statusClass(request.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ request.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(request.created_at).toLocaleDateString() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('shop-requests.show', request.id)" class="text-indigo-600 hover:text-indigo-900">View</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- Pagination -->
                            <div class="mt-4 flex justify-between">
                                <Link v-if="shopRequests.prev_page_url" :href="shopRequests.prev_page_url" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Previous</Link>
                                <span v-else></span>
                                <Link v-if="shopRequests.next_page_url" :href="shopRequests.next_page_url" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Next</Link>
                            </div>
                        </div>
                        <div v-else class="text-gray-500">
                            You have not made any shop requests yet.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Adjust if your layout path is different
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    shopRequests: Object, // Paginated list
});

const statusClass = (status) => {
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
