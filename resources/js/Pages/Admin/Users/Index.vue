<script setup>
   import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
   import { Head, Link } from '@inertiajs/vue3';

   defineProps({ users: Object });
   </script>
   <template>
       <Head title="Manage Users" />
       <AuthenticatedLayout>
           <template #header>
               <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Users</h2>
           </template>
           <div class="py-12">
               <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                   <div class="mb-4">
                       <Link :href="route('admin.users.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                           Create New User
                       </Link>
                   </div>
                   <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                       <div class="p-6 text-gray-900 overflow-x-auto">
                           <table class="min-w-full divide-y divide-gray-200">
                               <thead class="bg-gray-50">
                                   <tr>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Language</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verified</th>
                                       <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                   </tr>
                               </thead>
                               <tbody class="bg-white divide-y divide-gray-200">
                                   <tr v-for="userItem in users.data" :key="userItem.id">
                                       <td class="px-6 py-4 whitespace-nowrap text-sm">{{ userItem.id }}</td>
                                       <td class="px-6 py-4 whitespace-nowrap text-sm">{{ userItem.name }}</td>
                                       <td class="px-6 py-4 whitespace-nowrap text-sm">{{ userItem.email }}</td>
                                       <td class="px-6 py-4 whitespace-nowrap text-sm">{{ userItem.role }}</td>
                                       <td class="px-6 py-4 whitespace-nowrap text-sm">{{ userItem.rank }}</td>
                                       <td class="px-6 py-4 whitespace-nowrap text-sm">{{ userItem.language_preference }}</td>
                                       <td class="px-6 py-4 whitespace-nowrap text-sm">
                                           <span :class="userItem.email_verified_at ? 'text-green-600' : 'text-red-600'">
                                               {{ userItem.email_verified_at ? 'Yes' : 'No' }}
                                           </span>
                                       </td>
                                       <td class="px-6 py-4 whitespace-nowrap text-sm">
                                           <!-- Edit/Delete links can be added later when those methods are enabled -->
                                           <!-- <Link :href="route('admin.users.edit', userItem.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link> -->
                                       </td>
                                   </tr>
                                   <tr v-if="users.data.length === 0">
                                       <td colspan="8" class='text-center py-4 text-gray-500'>No users found.</td>
                                   </tr>
                               </tbody>
                           </table>
                           <!-- Pagination -->
                           <div v-if="users.links && users.links.length > 3" class="mt-6 flex justify-center space-x-1">
                                <template v-for="(link, key) in users.links" :key="key">
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
