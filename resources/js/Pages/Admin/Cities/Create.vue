<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CityForm from './CityForm.vue'; // Import the reusable form
import { Head } from '@inertiajs/vue3';

// Define any props if needed, though for create it might be minimal
// Example: defineProps({ someProp: Object });
</script>

<template>
    <Head title="Create City" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New City</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <CityForm :isEditMode="false" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
