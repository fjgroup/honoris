<template>
    <AuthenticatedLayout>
        <Head title="Manage Shop Requests" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Shop Requests</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Optional: Filters could go here -->

                        <div v-if="shopRequests.data.length > 0">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Building Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested On</th>
                                        <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="request in shopRequests.data" :key="request.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ request.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ request.user?.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ request.city?.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ request.building_type?.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="statusClass(request.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ request.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(request.created_at).toLocaleDateString() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('shop-requests.show', request.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">View</Link>
                                            <Link :href="route('shop-requests.edit', request.id)" class="text-yellow-600 hover:text-yellow-900">Edit/Process</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- Pagination Links -->
                            <div class="mt-6 flex justify-center space-x-1" v-if="shopRequests.links && shopRequests.links.length > 3">
                                 <template v-for="(link, key) in shopRequests.links" :key="key">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-4 py-2 text-sm rounded-md"
                                        :class="{
                                            'bg-indigo-600 text-white': link.active,
                                            'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50': !link.active,
                                        }"
                                        preserve-scroll
                                    />
                                    <span
                                        v-else
                                        v-html="link.label"
                                        class="px-4 py-2 text-sm rounded-md text-gray-400 cursor-default border border-gray-300"
                                        :class="{ 'bg-indigo-600 text-white': link.active }"
                                    ></span>
                                </template>
                            </div>
                        </div>
                        <div v-else class="text-gray-500">
                            No shop requests found.
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
    shopRequests: Object, // Paginated list of all requests
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
