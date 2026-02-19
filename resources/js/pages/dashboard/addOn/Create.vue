<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import AddOnForm from '@product/Components/Dashboard/AddOnForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import { addOnSchema } from '@product/validation/addOnSchema';
import { useFormValidation } from '@/composables/useFormValidation';
import type { ProductAddOnFormData, ProductAddOnCreateProps } from '../../../types';

const props = defineProps<ProductAddOnCreateProps>();

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

/**
 * value form data 
 */
const form = useForm<ProductAddOnFormData>({
    add_on_product_id: null,
    name: '',
    description: '',
    image_url: '',
    price_adjustment: 0,
    max_quantity: 1,
    sort_order: 0,
    is_required: false,
    is_active: true,
});

/**
 * validation form filed
 */
const { validateForm, validateAndSubmit, createIsFormInvalid } = useFormValidation(
    addOnSchema,
    ['add_on_product_id', 'price_adjustment', 'max_quantity'] 
);

const getFormData = () => ({
    add_on_product_id: form.add_on_product_id,
    name: form.name,
    description: form.description,
    image_url: form.image_url,
    price_adjustment: form.price_adjustment,
    max_quantity: form.max_quantity,
    sort_order: form.sort_order,
    is_required: form.is_required,
    is_active: form.is_active,
});

watch(() => form.add_on_product_id, () => {
    if (form.add_on_product_id) validateForm(getFormData());
});

const isFormInvalid = createIsFormInvalid(getFormData);

/**
 * submit handle 
 */
const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.post(`/dashboard/products/${props.product.id}/addons`, {
            onSuccess: () => {
                toast.success('Add-on created successfully.');
                setTimeout(() => {
                    close();
                    redirect();
                }, 100);
            },
        });
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
        title="Add Add-on"
        :description="`Add an add-on product for ${product.name}`"
        mode="create"
        size="lg"
        submit-text="Add Add-on"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <AddOnForm
            v-model="form"
            mode="create"
            :available-products="props.availableProducts"
        />
    </ModalForm>
</template>
