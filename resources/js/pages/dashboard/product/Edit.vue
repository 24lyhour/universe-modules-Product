<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import ProductForm from '../../components/ProductForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import type { ProductFormData, ProductEditProps } from '../../../types';
import product from '@/routes/product';

const props = defineProps<ProductEditProps>();

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
    name: props.product.name,
    description: props.product.description || '',
    sku: props.product.sku || '',
    product_type: props.product.product_type,
    price: props.product.price,
    purchase_price: props.product.purchase_price,
    sale_price: props.product.sale_price,
    stock: props.product.stock,
    low_stock_threshold: props.product.low_stock_threshold,
    status: props.product.status,
    is_featured: props.product.is_featured,
    pre_order: props.product.pre_order,
    images: props.product.images || [],
    category_id: props.product.category_id,
    outlet_id: props.product.outlet_id,
});

const handleSubmit = () => {
    form.put(product.products.update.url({ product: props.product.id }), {
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
        title="Edit Product"
        description="Update product information"
        mode="edit"
        size="2xl"
        submit-text="Save Changes"
        :loading="form.processing"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <ProductForm v-model="form" mode="edit" :outlets="props.outlets" />
    </ModalForm>
</template>
