<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import ProductForm from '../../components/ProductForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
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

const handleSubmit = () => {
    form.post(product.products.store.url(), {
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
        title="Create Product"
        description="Add a new product to your inventory"
        mode="create"
        size="2xl"
        submit-text="Create Product"
        :loading="form.processing"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <ProductForm v-model="form" mode="create" :outlets="props.outlets" />
    </ModalForm>
</template>
