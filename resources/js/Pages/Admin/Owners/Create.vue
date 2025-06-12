<script setup>
   import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
   import OwnerForm from './Partials/OwnerForm.vue';
   import { Head } from '@inertiajs/vue3';

   defineProps({
       users: Array, // Users available to be linked (OwnerController@create provides this)
   });
   </script>

   <template>
       <Head title="Create Owner Profile" />
       <AuthenticatedLayout>
           <template #header>
               <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New Owner Profile</h2>
           </template>
           <div class="py-12">
               <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                   <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                       <div class="p-6 md:p-8 bg-white border-b border-gray-200">
                           <OwnerForm :users="users" :isEditMode="false" />
                       </div>
                   </div>
               </div>
           </div>
       </AuthenticatedLayout>
   </template>
