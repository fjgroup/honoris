<script setup>
   import { useForm, router } from '@inertiajs/vue3';
   import { ref, watch } from 'vue';
   import InputError from '@/Components/InputError.vue';
   import InputLabel from '@/Components/InputLabel.vue';
   import PrimaryButton from '@/Components/PrimaryButton.vue';
   import TextInput from '@/Components/TextInput.vue';
   import TextareaInput from '@/Components/TextareaInput.vue'; // Assuming exists

   const props = defineProps({
       map: Object, // Optional: for editing
       cities: Array, // Required: for city_id dropdown
       isEditMode: {
           type: Boolean,
           default: false,
       }
   });

   const form = useForm({
       _method: props.isEditMode ? 'PUT' : 'POST', // Default method
       name: '',
       description: '',
       city_id: null,
       image_file: null, // For new image file object
   });

   const currentImageUrl = ref(null);
   const newImagePreviewUrl = ref(null);

   // Initialize form and image previews when props change or on initial load
   watch(() => props.map, (newMapData) => {
       form.defaults({ // Set new defaults for reset()
            _method: props.isEditMode ? 'PUT' : 'POST',
            name: newMapData?.name || '',
            description: newMapData?.description || '',
            city_id: newMapData?.city_id || null,
            image_file: null, // Always reset file input
       });
       form.reset(); // Apply new defaults
       currentImageUrl.value = newMapData?.image_path || null;
       newImagePreviewUrl.value = null; // Clear preview on prop change
       form.clearErrors();
   }, { deep: true, immediate: true });


   function handleFileChange(event) {
       const file = event.target.files[0];
       if (file) {
           form.image_file = file;
           const reader = new FileReader();
           reader.onload = (e) => {
               newImagePreviewUrl.value = e.target.result;
           };
           reader.readAsDataURL(file);
       } else {
           form.image_file = null;
           newImagePreviewUrl.value = null;
       }
   }

   const submit = () => {
       const submissionRoute = props.isEditMode
           ? route('admin.maps.update', props.map.id)
           : route('admin.maps.store');

       // Clone form data to modify for submission
       let dataToSubmit = { ...form.data() };

       if (form.image_file) {
           dataToSubmit.image_path = form.image_file; // Backend expects file as 'image_path'
       }
       delete dataToSubmit.image_file; // Remove original image_file key in all cases

       // For 'create' (isEditMode=false), if image_file was null, dataToSubmit.image_path will be undefined
       // The backend validation 'required|image' for 'image_path' on store will catch this.
       // For 'edit' (isEditMode=true), if image_file was null, image_path is not sent, backend keeps old image.

       // _method is already part of form.data() due to initialization
       // if (props.isEditMode) {
       //     dataToSubmit._method = 'PUT'; // This is correctly set by useForm initialization
       // }

       router.post(submissionRoute, dataToSubmit, { // router.post handles _method for PUT with files
           forceFormData: true, // Important for file uploads
           preserveScroll: true,
           onSuccess: () => {
               // form.reset('image_file') is not enough as it's not a direct field in form after rename
               // If it's a create, we might want to fully reset the form.
               if (!props.isEditMode) {
                   form.reset();
               } else {
                   form.image_file = null; // Clear the file input state in the form object
               }
               newImagePreviewUrl.value = null; // Clear preview

               // currentImageUrl will update via prop reload from Inertia if edit was successful
               // No need to manually set form.recentlySuccessful, useForm handles it
               form.clearErrors();
           },
           onError: (errors) => {
                console.error('Map submission errors:', errors);
                if(errors.image_path || errors.image_file) {
                    form.image_file = null; // Clear invalid file from form state
                    newImagePreviewUrl.value = null; // Clear its preview
                    const fileInput = document.getElementById('image_file_input');
                    if (fileInput) fileInput.value = ''; // Reset actual file input element
                }
           }
       });
   };
   </script>

   <template>
       <form @submit.prevent="submit" class="space-y-6">
           <div>
               <InputLabel for="name" value="Map Name" />
               <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
               <InputError class="mt-2" :message="form.errors.name" />
           </div>

           <div>
               <InputLabel for="city_id" value="City" />
               <select id="city_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.city_id" required>
                   <option :value="null" disabled>Select a city</option>
                   <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
               </select>
               <InputError class="mt-2" :message="form.errors.city_id" />
           </div>

           <div>
               <InputLabel for="description" value="Description" />
               <TextareaInput id="description" class="mt-1 block w-full" v-model="form.description" />
               <InputError class="mt-2" :message="form.errors.description" />
           </div>

           <div>
               <InputLabel for="image_file_input" value="Map Image (Image File)" />
               <div v-if="isEditMode && currentImageUrl && !newImagePreviewUrl" class="mt-2">
                   <p class="text-sm text-gray-500">Current Map Image:</p>
                   <img :src="currentImageUrl" alt="Current Map Image" class="max-h-48 w-auto border p-1 object-contain rounded-md" />
               </div>
               <div v-if="newImagePreviewUrl" class="mt-2">
                   <p class="text-sm text-gray-500">New Image Preview:</p>
                   <img :src="newImagePreviewUrl" alt="New Image Preview" class="max-h-48 w-auto border p-1 object-contain rounded-md" />
               </div>
               <input
                    id="image_file_input"
                    type="file"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    @change="handleFileChange"
                    accept="image/jpeg,image/png,image/jpg"
                />
               <InputError class="mt-2" :message="form.errors.image_path || form.errors.image_file" />
           </div>

           <div class="flex items-center gap-4">
               <PrimaryButton :disabled="form.processing">{{ isEditMode ? 'Update Map' : 'Create Map' }}</PrimaryButton>
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
    /* Add Tailwind classes directly or define custom styles if needed */
    .form-input, .form-select, .form-textarea {
        @apply mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2;
    }
    /* Standardize file input styling if possible, or use the provided example classes */
    </style>
