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
    low_stock_threshold: props.productSettings?.low_stock_threshold ?? 10,
    status: 'draft',
    is_featured: false,
    pre_order: false,
    images: [],
    category_id: null,
    outlet_id: null,
    upsale_id: null,
    down_sale_id: null,
});

const { validateForm, validateAndSubmit } = useFormValidation(productSchema, ['name', 'price', 'sale_price']);

/**
 * Generate SKU from product name using settings
 */
const generateSku = (name: string): string => {
    if (!name) return '';
    const prefix = props.productSettings?.sku_prefix || 'PRD';
    const separator = props.productSettings?.sku_separator || '-';
    const slug = name
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, '')
        .substring(0, 6);
    const random = Math.random().toString(36).substring(2, 6).toUpperCase();
    return `${prefix}${separator}${slug}${separator}${random}`;
};

// Auto-generate SKU when name changes (only if setting is enabled)
watch(
    () => form.name,
    (newName) => {
        const autoGenerate = props.productSettings?.auto_generate_sku ?? true;
        if (autoGenerate && newName && !form.sku) {
            form.sku = generateSku(newName);
        }
    }
);

/**
 * get form data
 */
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
    upsale_id: form.upsale_id,
    down_sale_id: form.down_sale_id,
});

/**
 * watch the value real time
 */
watch(
    () => [form.name, form.price, form.sale_price, form.stock],
    () => {
        if (form.name || form.price > 0 || (form.sale_price && form.sale_price > 0)) {
            validateForm(getFormData());
        }
    }
);

/**
 * Check the validation form required - name, price and sale_price are required
 */
const isFormInvalid = computed(() => {
    const nameValid = form.name && form.name.trim() !== '';
    const priceValid = form.price > 0;
    const salePriceValid = form.sale_price !== null && form.sale_price > 0;
    return !nameValid || !priceValid || !salePriceValid;
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
        <ProductForm
            v-model="form"
            mode="create"
            :outlets="props.outlets"
            :products="props.products"
            :categories="props.categories"
        />
    </ModalForm>
</template>
