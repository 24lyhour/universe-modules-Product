<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import ProductTypeForm from './components/ProductTypeForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import type { ProductTypeFormData, ProductTypeCreateProps } from '@product/types';

const props = defineProps<ProductTypeCreateProps>();

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

const form = useForm<ProductTypeFormData>({
    name: '',
    description: '',
    sort_order: 0,
    is_active: true,
});

// Check if form is valid
const isFormInvalid = computed(() => {
    const nameValid = form.name && form.name.trim() !== '';
    return !nameValid;
});

const handleSubmit = () => {
    form.post('/dashboard/product-types', {
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
        title="Create Product Type"
        description="Add a new product type"
        mode="create"
        size="lg"
        submit-text="Create"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <ProductTypeForm v-model="form" mode="create" />
    </ModalForm>
</template>
