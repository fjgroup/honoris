<script setup>
   import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
   import { Head, Link } from '@inertiajs/vue3';

   defineProps({
       owners: Object, // Paginated owners from OwnerController@index
   });
   </script>

   <template>
       <Head title="Manage Owners" />

       <AuthenticatedLayout>
           <template #header>
               <h2 class="text-xl font-semibold leading-tight text-gray-800">
                   Manage Owners
               </h2>
           </template>

           <div class="py-12">
               <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                   <div class="mb-4">
                       <Link
                           :href="route('admin.owners.create')"
                           class="rounded-md bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                       >
                           Create New Owner
                       </Link>
                   </div>
                   <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                       <div class="p-6 text-gray-900">
                           <div class="overflow-x-auto">
                               <table class="min-w-full divide-y divide-gray-200">
                                   <thead>
                                       <tr>
                                           <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                                           <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Display Name</th>
                                           <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Linked User</th>
                                           <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Language</th>
                                           <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Is Global</th>
                                           <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                       </tr>
                                   </thead>
                                   <tbody class="divide-y divide-gray-200 bg-white">
                                       <tr v-for="ownerItem in owners.data" :key="ownerItem.id">
                                           <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ownerItem.id }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ownerItem.name }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ownerItem.user?.name || 'N/A' }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ownerItem.language_preference }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ownerItem.is_for_all_users ? 'Yes' : 'No' }}</td>
                                           <td class="px-6 py-4 whitespace-nowrap text-sm">
                                               <Link :href="route('admin.owners.edit', ownerItem.id)" class="text-indigo-600 hover:text-indigo-900 mr-3 font-medium">Edit</Link>
                                               <Link :href="route('admin.owners.show', ownerItem.id)" class="text-blue-600 hover:text-blue-900 font-medium">View</Link>
                                           </td>
                                       </tr>
                                       <tr v-if="owners.data.length === 0">
                                           <td colspan="6" class="px-6 py-4 text-center text-gray-500">No owners found.</td>
                                       </tr>
                                   </tbody>
                               </table>
                           </div>
                           <!-- Pagination -->
                            <div v-if="owners.links && owners.links.length > 3" class="mt-6 flex justify-center space-x-1">
                                <Link
                                    v-for="(link, index) in owners.links"
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
