<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import BrandForm from './components/BrandForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import type { BrandFormData, BrandEditProps } from '@product/types';

const props = defineProps<BrandEditProps>();

const { show, close, redirect } = useModal();

const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => {
        if (!val) {
            close();
            redirect();
        }
    },
});

const form = useForm<BrandFormData>({
    name: props.brand.name,
    description: props.brand.description || '',
    logo: props.brand.logo || '',
    website: props.brand.website || '',
    outlet_id: props.brand.outlet_id,
    sort_order: props.brand.sort_order,
    is_active: Boolean(props.brand.is_active),
});

// Check if form is valid
const isFormInvalid = computed(() => {
    const nameValid = form.name && form.name.trim() !== '';
    return !nameValid;
});

const handleSubmit = () => {
    form.put(`/dashboard/brands/${props.brand.id}`, {
        onSuccess: () => {
            close();
            redirect();
        },
    });
};

const handleCancel = () => {
    close();
    redirect();
};
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="`Edit ${brand.name}`"
        description="Update brand information"
        mode="edit"
        size="lg"
        submit-text="Save Changes"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <BrandForm v-model="form" mode="edit" :outlets="props.outlets" />
    </ModalForm>
</template>
