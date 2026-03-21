<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import BrandForm from './components/BrandForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import type { BrandFormData, BrandCreateProps } from '@product/types';

const props = defineProps<BrandCreateProps>();

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
    name: '',
    description: '',
    logo: '',
    website: '',
    outlet_id: null,
    sort_order: 0,
    is_active: true,
});

// Check if form is valid
const isFormInvalid = computed(() => {
    const nameValid = form.name && form.name.trim() !== '';
    return !nameValid;
});

const handleSubmit = () => {
    form.post('/dashboard/brands', {
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
        title="Create Brand"
        description="Add a new brand"
        mode="create"
        size="lg"
        submit-text="Create"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <BrandForm v-model="form" mode="create" :outlets="props.outlets" />
    </ModalForm>
</template>
