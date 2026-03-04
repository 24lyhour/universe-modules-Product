<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import ProductTypeForm from './components/ProductTypeForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import type { ProductTypeFormData, ProductTypeEditProps } from '@product/types';

const props = defineProps<ProductTypeEditProps>();

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
    name: props.productType.name,
    description: props.productType.description || '',
    outlet_id: props.productType.outlet_id,
    sort_order: props.productType.sort_order,
    is_active: props.productType.is_active,
});

// Check if form is valid
const isFormInvalid = computed(() => {
    const nameValid = form.name && form.name.trim() !== '';
    const outletValid = form.outlet_id !== null;
    return !nameValid || !outletValid;
});

const handleSubmit = () => {
    form.put(`/dashboard/product-types/${props.productType.id}`, {
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
        :title="`Edit ${productType.name}`"
        description="Update product type information"
        mode="edit"
        size="lg"
        submit-text="Save Changes"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <ProductTypeForm v-model="form" mode="edit" :outlets="props.outlets" />
    </ModalForm>
</template>
