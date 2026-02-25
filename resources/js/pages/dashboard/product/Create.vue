<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Package } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import ProductForm from '../../components/ProductForm.vue';
import { productSchema } from '../../../validation/productSchema';
import { useFormValidation } from '@/composables/useFormValidation';
import type { ProductFormData, ProductCreateProps } from '../../../types';
import type { BreadcrumbItem } from '@/types';
import product from '@/routes/product';

const props = defineProps<ProductCreateProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: 'Create', href: '/dashboard/products/create' },
];

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
                toast.success('Product created successfully');
                router.visit('/dashboard/products');
            },
        });
    });
};

const handleCancel = () => {
    router.visit('/dashboard/products');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Product" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                        <Package class="h-6 w-6 text-primary" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Create Product</h1>
                        <p class="text-muted-foreground">Add a new product to your inventory</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <Card>
                <CardHeader>
                    <CardTitle>Product Information</CardTitle>
                    <CardDescription>Fill in the details for the new product</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <ProductForm
                            v-model="form"
                            mode="create"
                            :outlets="props.outlets"
                            :products="props.products"
                            :categories="props.categories"
                        />

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <Button
                                type="button"
                                variant="outline"
                                @click="handleCancel"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="form.processing || isFormInvalid"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Product' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
