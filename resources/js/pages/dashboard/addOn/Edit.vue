<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import AddOnForm from '@product/Components/Dashboard/AddOnForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import { addOnSchema } from '@product/validation/addOnSchema';
import { useFormValidation } from '@/composables/useFormValidation';
import type { ProductAddOnEditProps, ProductAddOnFormData } from '../../../types';

const props = defineProps<ProductAddOnEditProps>();

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
    add_on_product_id: props.addOn.add_on_product_id,
    name: props.addOn.name ?? '',
    description: props.addOn.description ?? '',
    image_url: props.addOn.image_url ?? '',
    price_adjustment: props.addOn.price_adjustment,
    max_quantity: props.addOn.max_quantity,
    sort_order: props.addOn.sort_order,
    is_required: props.addOn.is_required,
    is_active: props.addOn.is_active,
});

const { validateForm, validateAndSubmit, createIsFormInvalid } = useFormValidation(
    addOnSchema,
    ['price_adjustment', 'max_quantity']
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

watch([() => form.name, () => form.price_adjustment], () => {
    validateForm(getFormData());
});

const isFormInvalid = createIsFormInvalid(getFormData);

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.put(`/dashboard/products/${props.product.id}/addons/${props.addOn.id}`, {
            onSuccess: () => {
                toast.success('Add-on updated successfully.');
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
        title="Edit Add-on"
        :description="`Update add-on settings for ${product.name}`"
        mode="edit"
        size="lg"
        submit-text="Save Changes"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <AddOnForm
            v-model="form"
            mode="edit"
            :add-on="props.addOn"
        />
    </ModalForm>
</template>
