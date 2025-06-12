<script setup>
   import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
   import { Head, useForm, Link } from '@inertiajs/vue3'; // Added Link
   import InputError from '@/Components/InputError.vue';
   import InputLabel from '@/Components/InputLabel.vue';
   import PrimaryButton from '@/Components/PrimaryButton.vue';
   import TextInput from '@/Components/TextInput.vue';

   const form = useForm({
       name: '',
       email: '',
       password: '',
       password_confirmation: '',
       role: 'user', // Default role
       rank: '',
       language_preference: 'es', // Default language
   });

   const submit = () => {
       form.post(route('admin.users.store'), {
           onFinish: () => form.reset('password', 'password_confirmation'),
       });
   };

   const roles = ['user', 'owner', 'captain', 'admin'];
   const languages = [
       {value: 'en', label: 'English'},
       {value: 'es', label: 'Español'}
    ];
   </script>
   <template>
       <Head title="Create User" />
       <AuthenticatedLayout>
           <template #header>
               <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create New User</h2>
           </template>
           <div class="py-12">
               <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                   <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                       <div class="p-6 md:p-8 bg-white border-b border-gray-200">
                           <form @submit.prevent="submit" class="space-y-6">
                               <div>
                                   <InputLabel for="name" value="Name" />
                                   <TextInput id="name" type="text" v-model="form.name" required autofocus autocomplete="name" class="mt-1 block w-full" />
                                   <InputError :message="form.errors.name" class="mt-2" />
                               </div>
                               <div>
                                   <InputLabel for="email" value="Email (Optional)" />
                                   <TextInput id="email" type="email" v-model="form.email" autocomplete="email" class="mt-1 block w-full" />
                                   <InputError :message="form.errors.email" class="mt-2" />
                               </div>
                               <div>
                                   <InputLabel for="password" value="Password" />
                                   <TextInput id="password" type="password" v-model="form.password" required autocomplete="new-password" class="mt-1 block w-full" />
                                   <InputError :message="form.errors.password" class="mt-2" />
                               </div>
                               <div>
                                   <InputLabel for="password_confirmation" value="Confirm Password" />
                                   <TextInput id="password_confirmation" type="password" v-model="form.password_confirmation" required autocomplete="new-password" class="mt-1 block w-full" />
                                   <InputError :message="form.errors.password_confirmation" class="mt-2" />
                               </div>
                               <div>
                                   <InputLabel for="role" value="Role" />
                                   <select id="role" v-model="form.role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                       <option v-for="roleOption in roles" :key="roleOption" :value="roleOption">
                                        {{ roleOption.charAt(0).toUpperCase() + roleOption.slice(1) }}
                                       </option>
                                   </select>
                                   <InputError :message="form.errors.role" class="mt-2" />
                               </div>
                               <div>
                                   <InputLabel for="rank" value="Rank (Optional)" />
                                   <TextInput id="rank" type="text" v-model="form.rank" class="mt-1 block w-full" />
                                   <InputError :message="form.errors.rank" class="mt-2" />
                               </div>
                               <div>
                                   <InputLabel for="language_preference" value="Language Preference" />
                                   <select id="language_preference" v-model="form.language_preference" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                       <option v-for="lang in languages" :key="lang.value" :value="lang.value">
                                        {{ lang.label }}
                                       </option>
                                   </select>
                                   <InputError :message="form.errors.language_preference" class="mt-2" />
                               </div>
                               <div class="flex items-center justify-end space-x-4 mt-4">
                                   <Link :href="route('admin.users.index')" class="text-sm text-gray-600 hover:text-gray-900">Cancel</Link>
                                   <PrimaryButton :disabled="form.processing">Create User</PrimaryButton>
                               </div>
                           </form>
                       </div>
                   </div>
               </div>
           </div>
       </AuthenticatedLayout>
   </template>
