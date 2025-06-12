<script setup>
import { useForm, router } from '@inertiajs/vue3'; // Import router
import { ref, watch } from 'vue'; // Import ref and watch
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue'; // Assuming this exists


const props = defineProps({
    buildingType: Object, // Optional: pass existing buildingType for editing
    isEditMode: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    name: props.buildingType?.name || '',
    description: props.buildingType?.description || '',
    icon_file: null, // Field for the new file object
    // _method: props.isEditMode ? 'put' : 'post', // Handled by router.post's data object
});

const currentIconUrl = ref(props.buildingType?.icon_path || null);
const newIconPreviewUrl = ref(null);

// Watch for changes in buildingType prop if the component instance is reused
watch(() => props.buildingType, (newVal) => {
    form.defaults({ // Set defaults for reset()
        name: newVal?.name || '',
        description: newVal?.description || '',
        icon_file: null,
    });
    form.reset(); // Reset form fields to new prop values
    currentIconUrl.value = newVal?.icon_path || null;
    newIconPreviewUrl.value = null;
    form.clearErrors(); // Clear errors when the form data changes
}, { deep: true, immediate: true }); // immediate: true to run on initial load with prop


function handleFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        form.icon_file = file; // This is the actual File object
        const reader = new FileReader();
        reader.onload = (e) => {
            newIconPreviewUrl.value = e.target.result; // For preview
        };
        reader.readAsDataURL(file);
    } else {
        form.icon_file = null;
        newIconPreviewUrl.value = null;
    }
}

const submit = () => {
    const submissionRoute = props.isEditMode
        ? route('admin.building-types.update', props.buildingType.id)
        : route('admin.building-types.store');

    // Create a new FormData object for submission, as useForm might not handle files
    // within complex objects the way router.post expects for multipart/form-data.
    // However, Inertia's router.post with useForm should handle File objects correctly.
    // The key is that the backend should expect 'icon_path' for the file.

    let dataToSubmit = { ...form.data() }; // Spread current form data

    if (form.icon_file) {
        // Rename icon_file to icon_path for backend, if necessary,
        // or ensure backend handles 'icon_file'.
        // The controller expects 'icon_path' for the uploaded file.
        dataToSubmit.icon_path = form.icon_file;
        delete dataToSubmit.icon_file; // Remove original icon_file key
    } else {
        // If no new file, remove icon_file from submission data
        delete dataToSubmit.icon_file;
        // For PUT requests, if icon_path is not sent, the backend should not update it.
        // If you want to explicitly remove an icon, you'd send icon_path: null,
        // but current rules (nullable|image) might not allow null directly.
        // This setup implies not sending icon_path means "no change to icon".
    }


    if (props.isEditMode) {
        // Inertia's router.post with a _method: 'PUT' will send a POST request
        // with the _method field to tell Laravel to treat it as PUT.
        dataToSubmit._method = 'PUT';

        // For file uploads with PUT, it's often better to use router.post
        // as true multipart/form-data PUT requests can be tricky.
        // Inertia handles this by making it a POST request with a _method field.
         router.post(submissionRoute, dataToSubmit, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset('icon_file'); // Clear the file input field in the form object
                newIconPreviewUrl.value = null; // Clear preview
                if (props.isEditMode) { // Update currentIconUrl if edit was successful
                    // After successful update, Inertia should reload props,
                    // so props.buildingType.icon_path would be the new URL.
                    // The watch effect should update currentIconUrl.
                }
                form.clearErrors();
            },
            onError: (errors) => {
                console.error('Submission errors:', errors);
                 if (errors.icon_path && form.icon_file) {
                    // If there's an error with icon_path, and a file was selected,
                    // it's good to clear the file input in the form state
                    // so user doesn't try to resubmit the same errored file without re-selecting.
                    form.icon_file = null;
                    newIconPreviewUrl.value = null;
                    // Maybe also reset the actual file input element:
                    // const fileInput = document.getElementById('icon_file_input');
                    // if (fileInput) fileInput.value = '';
                }
            },
        });
    } else {
        // For store (POST)
        form.post(submissionRoute, { // useForm's post method
             data: dataToSubmit, // Not needed, form.post uses form data
             preserveScroll: true,
             onSuccess: () => {
                form.reset(); // Reset all fields for create form
                newIconPreviewUrl.value = null;
                currentIconUrl.value = null; // Clear current icon if any was shown (e.g. if create form is reused)
                form.clearErrors();
            },
            onError: (errors) => {
                console.error('Submission errors:', errors);
            }
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <InputLabel for="name" value="Name" />
            <TextInput
                id="name"
                type="text"
                class="mt-1 block w-full"
                v-model="form.name"
                required
                autofocus
            />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div>
            <InputLabel for="description" value="Description" />
            <TextareaInput
                id="description"
                class="mt-1 block w-full"
                v-model="form.description"
            />
            <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <div>
            <InputLabel for="icon_file_input" value="Icon (Image File)" />
            <!-- Current Icon Preview (Edit Mode) -->
            <div v-if="isEditMode && currentIconUrl && !newIconPreviewUrl" class="mt-2">
                <p class="text-sm text-gray-500">Current Icon:</p>
                <img :src="currentIconUrl" alt="Current Icon" class="h-16 w-16 object-contain border p-1 rounded-md" />
            </div>
            <!-- New Icon Preview -->
            <div v-if="newIconPreviewUrl" class="mt-2">
                <p class="text-sm text-gray-500">New Icon Preview:</p>
                <img :src="newIconPreviewUrl" alt="New Icon Preview" class="h-16 w-16 object-contain border p-1 rounded-md" />
            </div>

            <input
                id="icon_file_input"
                type="file"
                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                @change="handleFileChange"
                accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml"
            />
            <InputError class="mt-2" :message="form.errors.icon_path || form.errors.icon_file" />
            <!-- Check for both possible error keys -->
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing">{{ isEditMode ? 'Update Building Type' : 'Create Building Type' }}</PrimaryButton>

            <Transition
                enter-active-class="transition ease-in-out"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out"
                leave-to-class="opacity-0"
            >
                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
            </Transition>
        </div>
    </form>
</template>

<style scoped>
/* Add any specific scoped styles if needed */
</style>
