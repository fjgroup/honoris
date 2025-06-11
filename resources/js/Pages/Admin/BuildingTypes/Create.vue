<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BuildingTypeForm from './BuildingTypeForm.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Create Building Type" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New Building Type</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <BuildingTypeForm :isEditMode="false" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
