<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import AddOnForm from '@product/Components/Dashboard/AddOnForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import type { ProductAddOnEditProps } from '../../../types';

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

const form = useForm({
    add_on_product_id: props.addOn.add_on_product_id,
    price_adjustment: props.addOn.price_adjustment,
    max_quantity: props.addOn.max_quantity,
    sort_order: props.addOn.sort_order,
    is_required: props.addOn.is_required,
    is_active: props.addOn.is_active,
});

const handleSubmit = () => {
    form.put(`/dashboard/products/${props.product.id}/addons/${props.addOn.id}`, {
        onSuccess: () => {
            toast.success('Add-on updated successfully.');
            setTimeout(() => {
                close();
                redirect();
            }, 100);
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
        title="Edit Add-on"
        :description="`Update add-on settings for ${product.name}`"
        mode="edit"
        size="lg"
        submit-text="Save Changes"
        :loading="form.processing"
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
