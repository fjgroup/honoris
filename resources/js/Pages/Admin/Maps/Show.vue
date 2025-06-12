<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    map: Object,
});
</script>

<template>
    <Head :title="'Map Details: ' + map.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Map Details: {{ map.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8 bg-white border-b border-gray-200 space-y-4">
                        <div>
                            <strong class="font-semibold text-gray-700">ID:</strong>
                            <span class="text-gray-900 ml-2">{{ map.id }}</span>
                        </div>
                        <div>
                            <strong class="font-semibold text-gray-700">Name:</strong>
                            <span class="text-gray-900 ml-2">{{ map.name }}</span>
                        </div>
                        <div>
                            <strong class="font-semibold text-gray-700">City:</strong>
                            <span class="text-gray-900 ml-2">{{ map.city?.name || 'N/A' }}</span>
                        </div>
                        <div>
                            <strong class="font-semibold text-gray-700">Description:</strong>
                            <p class="mt-1 text-gray-900 whitespace-pre-wrap">{{ map.description || 'N/A' }}</p>
                        </div>
                        <div>
                            <strong class="font-semibold text-gray-700">Image Path (DB):</strong>
                            <span class="text-gray-900 ml-2 text-xs">{{ map.image_path }}</span>
                        </div>
                        <div v-if="map.image_path">
                            <strong class="font-semibold text-gray-700">Image Preview:</strong><br/>
                            <img :src="map.image_path"
                                 :alt="map.name"
                                 class="mt-2 max-w-md w-full max-h-[60vh] border p-1 rounded-md object-contain"/>
                            <p class="text-xs text-gray-500 mt-1">Note: Image path is: {{ map.image_path }}. Ensure it's a public URL (e.g., starts with /storage/ or http).</p>
                        </div>
                         <div>
                            <strong class="font-semibold text-gray-700">Natural Width (px):</strong>
                            <span class="text-gray-900 ml-2">{{ map.width || 'Not set' }}</span>
                        </div>
                        <div>
                            <strong class="font-semibold text-gray-700">Natural Height (px):</strong>
                            <span class="text-gray-900 ml-2">{{ map.height || 'Not set' }}</span>
                        </div>
                        <div>
                            <strong class="font-semibold text-gray-700">Created At:</strong>
                            <span class="text-gray-900 ml-2">{{ new Date(map.created_at).toLocaleString() }}</span>
                        </div>
                        <div>
                            <strong class="font-semibold text-gray-700">Updated At:</strong>
                            <span class="text-gray-900 ml-2">{{ new Date(map.updated_at).toLocaleString() }}</span>
                        </div>
                        <div class="mt-6 space-x-4">
                            <Link :href="route('admin.maps.edit', map.id)"
                                  class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring focus:ring-yellow-200 disabled:opacity-25 transition">
                                Edit Map
                            </Link>
                            <Link :href="route('admin.maps.index')"
                                  class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-400 focus:ring focus:ring-gray-100 disabled:opacity-25 transition">
                                Back to Maps List
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.whitespace-pre-wrap {
    white-space: pre-wrap; /* Ensures newlines in description are rendered */
}
</style>
