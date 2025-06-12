<script setup>
   import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
   import OwnerForm from './Partials/OwnerForm.vue';
   import { Head } from '@inertiajs/vue3';

   defineProps({
       owner: Object,
       users: Array, // Users available for linking (OwnerController@edit provides this)
   });
   </script>

   <template>
       <Head :title="'Edit Owner: ' + owner.name" />
       <AuthenticatedLayout>
           <template #header>
               <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Owner Profile: {{ owner.name }}</h2>
           </template>
           <div class="py-12">
               <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                   <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                       <div class="p-6 md:p-8 bg-white border-b border-gray-200">
                           <OwnerForm :owner="owner" :users="users" :isEditMode="true" />
                       </div>
                   </div>
               </div>
           </div>
       </AuthenticatedLayout>
   </template>
