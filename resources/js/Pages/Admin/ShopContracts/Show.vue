<template>
    <AuthenticatedLayout>
        <Head :title="'Shop Contract #' + shopContract.id" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Shop Contract Details #{{ shopContract.id }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <h3 class="detail-label">Contract ID</h3>
                                <p class="detail-value">#{{ shopContract.id }}</p>
                            </div>
                            <div>
                                <h3 class="detail-label">Status</h3>
                                <p class="detail-value">
                                     <span :class="statusClass(shopContract.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ shopContract.status }}
                                    </span>
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <h3 class="detail-label">Map Plot</h3>
                                <p class="detail-value">
                                    {{ shopContract.map_plot?.plot_identifier }}
                                    (Map: {{ shopContract.map_plot?.map?.name }}, City: {{ shopContract.map_plot?.map?.city?.name }})
                                    <Link v-if="shopContract.map_plot?.map" :href="route('admin.maps.show', shopContract.map_plot.map.id)" class="text-indigo-500 text-xs ml-2">(View Map)</Link>
                                </p>
                            </div>

                            <div>
                                <h3 class="detail-label">Owner</h3>
                                <p class="detail-value">{{ shopContract.owner?.name }} <span v-if="shopContract.owner?.user"> (User: {{ shopContract.owner.user.name }})</span></p>
                            </div>
                            <div>
                                <h3 class="detail-label">Building Type</h3>
                                <p class="detail-value">{{ shopContract.building_type?.name }}</p>
                            </div>

                            <div>
                                <h3 class="detail-label">Assigned To User</h3>
                                <p class="detail-value">{{ shopContract.assigned_to_user?.name || 'N/A' }}</p>
                            </div>
                             <div>
                                <h3 class="detail-label">Linked Shop Request</h3>
                                <p class="detail-value" v-if="shopContract.shop_request">
                                    Request #{{ shopContract.shop_request.id }}
                                    <Link :href="route('admin.shop-requests.show', shopContract.shop_request.id)" class="text-indigo-500 text-xs ml-2">(View Request)</Link>
                                </p>
                                <p v-else class="detail-value">-</p>
                            </div>

                            <div>
                                <h3 class="detail-label">Start Date</h3>
                                <p class="detail-value">{{ new Date(shopContract.start_date).toLocaleDateString() }}</p>
                            </div>
                            <div>
                                <h3 class="detail-label">End Date</h3>
                                <p class="detail-value">{{ new Date(shopContract.end_date).toLocaleDateString() }}</p>
                            </div>

                            <div>
                                <h3 class="detail-label">Created At</h3>
                                <p class="detail-value">{{ new Date(shopContract.created_at).toLocaleString() }}</p>
                            </div>
                            <div>
                                <h3 class="detail-label">Last Updated At</h3>
                                <p class="detail-value">{{ new Date(shopContract.updated_at).toLocaleString() }}</p>
                            </div>
                            <div>
                                <h3 class="detail-label">Last Updated By</h3>
                                <p class="detail-value">{{ shopContract.last_updated_by_user?.name || 'System/Unknown' }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between items-center">
                            <Link :href="route('admin.shop-contracts.index')" class="text-sm text-indigo-600 hover:text-indigo-900">
                                &laquo; Back to All Contracts
                            </Link>
                            <Link :href="route('admin.shop-contracts.edit', shopContract.id)" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded text-xs uppercase tracking-widest">
                                Edit Contract
                            </Link>
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
    shopContract: Object, // Includes eager-loaded relations
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
.detail-label {
    @apply text-sm font-medium text-gray-500;
}
.detail-value {
    @apply mt-1 text-sm text-gray-900;
}
</style>
