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

const form = useForm<ProductAddOnFormData>({
    add_on_product_id: null,
    price_adjustment: 0,
    max_quantity: 1,
    sort_order: 0,
    is_required: false,
    is_active: true,
});

// Use shared validation composable
const { validateForm, validateAndSubmit, createIsFormInvalid } = useFormValidation(
    addOnSchema,
    ['add_on_product_id'] // Required fields
);

// Get form data for validation
const getFormData = () => ({
    add_on_product_id: form.add_on_product_id,
    price_adjustment: form.price_adjustment,
    max_quantity: form.max_quantity,
    sort_order: form.sort_order,
    is_required: form.is_required,
    is_active: form.is_active,
});

// Watch form changes to validate in real-time
watch(() => form.add_on_product_id, () => {
    if (form.add_on_product_id) validateForm(getFormData());
});

// Check if form is valid for submit button state
const isFormInvalid = createIsFormInvalid(getFormData);

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
