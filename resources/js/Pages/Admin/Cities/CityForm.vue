<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue'; // Assuming you have or will create this

const props = defineProps({
    city: Object, // Optional: pass existing city for editing
    isEditMode: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    name: props.city?.name || '',
    description: props.city?.description || '',
});

const submit = () => {
    if (props.isEditMode && props.city) {
        form.put(route('admin.cities.update', props.city.id), {
            preserveScroll: true,
            // onSuccess: () => form.reset(), // Optional: reset form on success
        });
    } else {
        form.post(route('admin.cities.store'), {
            preserveScroll: true,
            // onSuccess: () => form.reset(), // Optional: reset form on success
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
                autocomplete="name"
            />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div>
            <InputLabel for="description" value="Description" />
            <TextareaInput
                id="description"
                class="mt-1 block w-full"
                v-model="form.description"
                autocomplete="description"
            />
            <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing">{{ isEditMode ? 'Update' : 'Create' }}</PrimaryButton>

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
