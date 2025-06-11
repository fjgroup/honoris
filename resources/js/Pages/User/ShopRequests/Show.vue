<template>
    <AuthenticatedLayout>
        <Head :title="'Shop Request Details - #' + shopRequest.id" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Shop Request Details</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
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
                                <h3 class="text-sm font-medium text-gray-500">Requested City</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ shopRequest.city?.name }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Requested Building Type</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ shopRequest.building_type?.name }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <h3 class="text-sm font-medium text-gray-500">Notes</h3>
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
                                    <!-- Add more contract details here if needed, e.g., link to contract show page -->
                                    <br>Details: {{ shopRequest.assigned_contract.contract_details }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between items-center">
                            <Link :href="route('shop-requests.index')" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                &laquo; Back to My Requests
                            </Link>

                            <div v-if="canCancelRequest">
                                <button @click="confirmCancelRequest"
                                        class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 disabled:opacity-25 transition"
                                        :disabled="form.processing">
                                    Cancel Request
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Adjust if your layout path is different
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    shopRequest: Object,
    // It's good practice to pass auth.user or specific permissions if available
    // For simplicity, we'll rely on the controller's authorization for showing the page,
    // and the policy check for the delete action.
    // auth: Object,
});

// Form for the cancel action
const form = useForm({});

const canCancelRequest = computed(() => {
    // This relies on the policy on the backend primarily.
    // Client-side check is for UI convenience.
    // Add more conditions if needed, e.g. user role.
    return props.shopRequest.status === 'pending';
});

const confirmCancelRequest = () => {
    if (confirm('Are you sure you want to cancel this shop request? This action cannot be undone if it is still pending.')) {
        cancelRequest();
    }
};

const cancelRequest = () => {
    form.delete(route('shop-requests.destroy', props.shopRequest.id), {
        preserveScroll: true, // Keep user on the same scroll position if there's an error
        onSuccess: () => {
            // Controller redirects to shop-requests.index on successful deletion
            // router.visit(route('shop-requests.index')); // Or force redirect if needed
        },
        onError: (errors) => {
            console.error('Error cancelling request:', errors);
            // Handle error display if needed, though form.errors should populate
            alert('Could not cancel the request. Please try again or contact support.');
        }
    });
};

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
