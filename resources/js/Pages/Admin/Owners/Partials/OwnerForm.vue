<script setup>
   import { useForm } from '@inertiajs/vue3';
   import { watch } from 'vue';
   import InputError from '@/Components/InputError.vue';
   import InputLabel from '@/Components/InputLabel.vue';
   import PrimaryButton from '@/Components/PrimaryButton.vue';
   import TextInput from '@/Components/TextInput.vue';
   import TextareaInput from '@/Components/TextareaInput.vue'; // Assuming exists
   import Checkbox from '@/Components/Checkbox.vue'; // For boolean is_for_all_users

   const props = defineProps({
       owner: Object, // Optional: for editing
       users: Array,  // Required: for user_id dropdown (list of available users to be linked)
       isEditMode: {
           type: Boolean,
           default: false,
       }
   });

   const form = useForm({
       name: '',
       user_id: null,
       contact_info: '',
       language_preference: 'es', // Default to 'es'
       is_for_all_users: false,
       description: '',
   });

   // Watch for changes in owner prop to update form defaults and reset
   watch(() => props.owner, (newVal) => {
       form.defaults({
           name: newVal?.name || '',
           user_id: newVal?.user_id || null,
           contact_info: newVal?.contact_info || '',
           language_preference: newVal?.language_preference || 'es',
           is_for_all_users: newVal?.is_for_all_users || false,
           description: newVal?.description || '',
       });
       form.reset(); // Apply new defaults
       form.clearErrors(); // Clear any previous errors
   }, { deep: true, immediate: true }); // immediate to run on component mount

   const submit = () => {
       if (props.isEditMode && props.owner) {
           form.put(route('admin.owners.update', props.owner.id), {
               preserveScroll: true,
               onSuccess: () => {
                   // form.reset(); // Optional: reset after successful edit if desired
                   form.clearErrors();
               },
               // onError is handled by useForm automatically displaying errors
           });
       } else {
           form.post(route('admin.owners.store'), {
               preserveScroll: true,
               onSuccess: () => {
                   form.reset(); // Reset form on successful creation
                   form.clearErrors();
               }
           });
       }
   };
   </script>

   <template>
       <form @submit.prevent="submit" class="space-y-6">
           <div>
               <InputLabel for="name" value="Owner Display Name" />
               <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
               <InputError class="mt-2" :message="form.errors.name" />
           </div>

           <div>
               <InputLabel for="user_id" value="Link to User Account" />
               <select id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.user_id" required>
                   <option :value="null" disabled>Select a user</option>
                   <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} (ID: {{ user.id }})</option>
               </select>
               <InputError class="mt-2" :message="form.errors.user_id" />
           </div>

           <div>
               <InputLabel for="contact_info" value="Contact Info" />
               <TextInput id="contact_info" type="text" class="mt-1 block w-full" v-model="form.contact_info" />
               <InputError class="mt-2" :message="form.errors.contact_info" />
           </div>

           <div>
               <InputLabel for="language_preference" value="Language Preference" />
               <select id="language_preference" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.language_preference" required>
                   <option value="en">English</option>
                   <option value="es">Español</option>
               </select>
               <InputError class="mt-2" :message="form.errors.language_preference" />
           </div>

           <div class="block">
                <label class="flex items-center">
                    <Checkbox name="is_for_all_users" v-model:checked="form.is_for_all_users" />
                    <span class="ms-2 text-sm text-gray-600">Shops are for all users (Publicly Visible Contracts)</span>
                </label>
                <InputError class="mt-2" :message="form.errors.is_for_all_users" />
            </div>

           <div>
               <InputLabel for="description" value="Description" />
               <TextareaInput id="description" class="mt-1 block w-full" v-model="form.description" />
               <InputError class="mt-2" :message="form.errors.description" />
           </div>

           <div class="flex items-center gap-4">
               <PrimaryButton :disabled="form.processing">{{ isEditMode ? 'Update Owner' : 'Create Owner' }}</PrimaryButton>
               <Transition
                    enter-active-class="transition ease-in-out duration-150"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out duration-150"
                    leave-to-class="opacity-0">
                   <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
               </Transition>
           </div>
       </form>
   </template>

   <style scoped>
    /* Apply Tailwind classes directly or use @apply for custom component styles if needed */
    .form-input, .form-select, .form-textarea {
        @apply mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2;
    }
    /* Ensure Checkbox component is styled appropriately or add styles here */
   </style>
