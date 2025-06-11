<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BuildingTypeForm from './BuildingTypeForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    buildingType: Object,
});
</script>

<template>
    <Head :title="'Edit Building Type: ' + buildingType.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Building Type: {{ buildingType.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <BuildingTypeForm :buildingType="buildingType" :isEditMode="true" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
