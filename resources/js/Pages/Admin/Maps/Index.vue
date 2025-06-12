<script setup>
   import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
   import { Head, Link } from '@inertiajs/vue3';

   defineProps({
       maps: Object, // Paginated maps from MapController@index
   });
   </script>

   <template>
       <Head title="Manage Maps" />

       <AuthenticatedLayout>
           <template #header>
               <h2 class="text-xl font-semibold leading-tight text-gray-800">
                   Manage Maps
               </h2>
           </template>

           <div class="py-12">
               <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                   <div class="mb-4">
                       <Link
                           :href="route('admin.maps.create')"
                           class="rounded-md bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                       >
                           Create New Map
                       </Link>
                   </div>
                   <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                       <div class="p-6 text-gray-900">
                           <div class="overflow-x-auto">
                               <table class="min-w-full divide-y divide-gray-200">
                                   <thead>
                                       <tr>
                                           <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                                           <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                           <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">City</th>
                                           <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                       </tr>
                                   </thead>
                                   <tbody class="divide-y divide-gray-200 bg-white">
                                       <tr v-for="mapItem in maps.data" :key="mapItem.id">
                                           <td class="px-6 py-4 whitespace-nowrap text-sm">{{ mapItem.id }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap text-sm">{{ mapItem.name }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap text-sm">{{ mapItem.city?.name || 'N/A' }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap text-sm">
                                               <Link :href="route('admin.maps.edit', mapItem.id)" class="text-indigo-600 hover:text-indigo-900 mr-3 font-medium">Edit</Link>
                                               <Link :href="route('admin.maps.show', mapItem.id)" class="text-blue-600 hover:text-blue-900 font-medium">View</Link>
                                           </td>
                                       </tr>
                                       <tr v-if="maps.data.length === 0">
                                           <td colspan="4" class="px-6 py-4 text-center text-gray-500">No maps found.</td>
                                       </tr>
                                   </tbody>
                               </table>
                           </div>
                           <!-- Basic Pagination -->
                           <div v-if="maps.links && maps.links.length > 3" class="mt-6 flex justify-center space-x-1">
                                <Link
                                    v-for="(link, index) in maps.links"
                                    :key="index"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-4 py-2 text-sm rounded-md"
                                    :class="{
                                        'bg-indigo-600 text-white': link.active,
                                        'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50': !link.active && link.url,
                                        'text-gray-400 cursor-not-allowed': !link.url
                                    }"
                                    :disabled="!link.url"
                                    preserve-scroll
                                />
                            </div>
                       </div>
                   </div>
               </div>
           </div>
       </AuthenticatedLayout>
   </template>
