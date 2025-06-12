<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    buildingTypes: Object, // Laravel paginated object
});
</script>

<template>
    <Head title="Building Types" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Building Types</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4">
                            <Link :href="route('admin.building-types.create')"
                                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Building Type
                            </Link>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Icon Path</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="bt in buildingTypes.data" :key="bt.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ bt.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ bt.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ bt.icon_path }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('admin.building-types.edit', bt.id)" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</Link>
                                        <Link :href="route('admin.building-types.show', bt.id)" class="text-green-600 hover:text-green-900">View</Link>
                                    </td>
                                </tr>
                                <tr v-if="buildingTypes.data.length === 0">
                                     <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No building types found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                         <!-- Pagination Links -->
                        <div class="mt-6 flex justify-center space-x-1" v-if="buildingTypes.links && buildingTypes.links.length > 3">
                             <template v-for="(link, key) in buildingTypes.links" :key="key">
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
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
