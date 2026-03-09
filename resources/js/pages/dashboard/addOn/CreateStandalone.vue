<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import AddOnStandaloneForm from '../../../Components/Dashboard/AddOnStandaloneForm.vue';
import { addOnStandaloneSchema } from '@product/validation/addOnSchema';
import { useFormValidation } from '@/composables/useFormValidation';
import type { ProductSimple } from '../../../types';

interface Props {
    parentProducts: ProductSimple[];
}

const props = defineProps<Props>();

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

const form = useForm({
    product_id: null as number | null,
    name: '',
    description: '',
    image_url: '',
    price_adjustment: 0,
    max_quantity: 1,
    sort_order: 0,
    is_required: false,
    is_active: true,
});

const { validateForm, validateAndSubmit, createIsFormInvalid } = useFormValidation(
    addOnStandaloneSchema,
    ['product_id', 'name']
);

const getFormData = () => ({
    product_id: form.product_id,
    name: form.name,
    description: form.description || '',
    image_url: form.image_url || '',
    price_adjustment: form.price_adjustment,
    max_quantity: form.max_quantity,
    sort_order: form.sort_order,
    is_required: form.is_required,
    is_active: form.is_active,
});

// Validate on field changes
watch([() => form.product_id, () => form.name], () => {
    if (form.product_id || form.name) {
        validateForm(getFormData());
    }
});

const isFormInvalid = createIsFormInvalid(getFormData);

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.post('/dashboard/products/addons', {
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
        title="Create Add-on"
        description="Create a new product add-on"
        mode="create"
        size="lg"
        submit-text="Create Add-on"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <AddOnStandaloneForm
            v-model="form"
            :parent-products="parentProducts"
        />
    </ModalForm>
</template>
