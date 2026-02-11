<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import ProductForm from '../../components/ProductForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, watch } from 'vue';
import { productSchema } from '../../../validation/productSchema';
import { useFormValidation } from '@/composables/useFormValidation';
import type { ProductFormData, ProductCreateProps } from '../../../types';
import product from '@/routes/product';

const props = defineProps<ProductCreateProps>();

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

const form = useForm<ProductFormData>({
    name: '',
    description: '',
    sku: '',
    product_type: null,
    price: 0,
    purchase_price: null,
    sale_price: null,
    stock: 0,
    low_stock_threshold: 10,
    status: 'draft',
    is_featured: false,
    pre_order: false,
    images: [],
    category_id: null,
    outlet_id: null,
});

// Use shared validation composable
const { validateForm, validateAndSubmit } = useFormValidation(productSchema, ['name']);

// Get form data for validation
const getFormData = () => ({
    name: form.name,
    description: form.description || null,
    sku: form.sku || null,
    product_type: form.product_type || null,
    price: form.price,
    purchase_price: form.purchase_price,
    sale_price: form.sale_price,
    stock: form.stock,
    low_stock_threshold: form.low_stock_threshold,
    status: form.status,
    is_featured: form.is_featured,
    pre_order: form.pre_order,
    images: form.images || null,
    category_id: form.category_id,
    outlet_id: form.outlet_id,
});

// Watch form changes to validate in real-time
watch(
    () => [form.name, form.price, form.stock],
    () => {
        if (form.name || form.price > 0 || form.stock > 0) {
            validateForm(getFormData());
        }
    }
);

// Check if form is valid for submit button state (custom for Product)
const isFormInvalid = computed(() => {
    return !form.name || form.name.trim() === '' || form.price < 0 || form.stock < 0;
});

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.post(product.products.store.url(), {
            onSuccess: () => {
                close();
                redirect();
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
        title="Create Product"
        description="Add a new product to your inventory"
        mode="create"
        size="2xl"
        submit-text="Create Product"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <ProductForm v-model="form" mode="create" :outlets="props.outlets" />
    </ModalForm>
</template>
