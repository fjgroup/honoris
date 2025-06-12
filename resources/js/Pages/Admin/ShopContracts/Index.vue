<template>
    <AuthenticatedLayout>
        <Head title="Manage Shop Contracts" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Shop Contracts</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-4">
                            <Link :href="route('admin.shop-contracts.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create New Contract
                            </Link>
                        </div>

                        <div v-if="shopContracts.data.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plot (Map, City)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner (User)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Building Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="contract in shopContracts.data" :key="contract.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ contract.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ contract.map_plot?.plot_identifier }}
                                            <span class="text-xs text-gray-500">({{ contract.map_plot?.map?.name }}, {{ contract.map_plot?.map?.city?.name }})</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ contract.owner?.name }} <span v-if="contract.owner?.user" class="text-xs text-gray-500">({{ contract.owner.user.name }})</span></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ contract.building_type?.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ contract.assigned_to_user?.name || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(contract.start_date).toLocaleDateString() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(contract.end_date).toLocaleDateString() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="statusClass(contract.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ contract.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.shop-contracts.show', contract.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">View</Link>
                                            <Link :href="route('admin.shop-contracts.edit', contract.id)" class="text-yellow-600 hover:text-yellow-900">Edit</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- Pagination Links -->
                            <div class="mt-6 flex justify-center space-x-1" v-if="shopContracts.links && shopContracts.links.length > 3">
                                 <template v-for="(link, key) in shopContracts.links" :key="key">
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
                            No shop contracts found.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    shopContracts: Object,
});

const statusClass = (status) => {
    const classes = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-gray-100 text-gray-800',
        expired: 'bg-red-100 text-red-800',
        pending_renewal: 'bg-yellow-100 text-yellow-800',
    };
    return classes[status] || 'bg-gray-200 text-gray-700';
};
</script>

<style scoped>
/* Add specific styles if needed */
</style>
