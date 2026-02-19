<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import VariantForm from '../../components/VariantForm.vue';
import type { Product, ProductAttribute } from '../../../types';

interface ProductSettings {
    auto_generate_sku?: boolean;
    sku_prefix?: string;
    sku_separator?: string;
    low_stock_threshold?: number;
}

interface Props {
    product: Product;
    attributes: ProductAttribute[];
    productSettings?: ProductSettings;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: props.product.name, href: `/dashboard/products/${props.product.id}` },
    { title: 'Variants', href: `/dashboard/products/${props.product.id}/variants` },
    { title: 'Create', href: `/dashboard/products/${props.product.id}/variants/create` },
];

const form = useForm({
    sku: '',
    name: '',
    price: null as number | null,
    purchase_price: null as number | null,
    sale_price: null as number | null,
    stock: 0,
    low_stock_threshold: 5,
    barcode: '',
    weight: null as number | null,
    is_default: false,
    is_active: true,
    sort_order: 0,
    attribute_value_ids: [] as number[],
});

const handleSubmit = () => {
    form.post(`/dashboard/products/${props.product.id}/variants`, {
        preserveScroll: true,
    });
};

// Form is valid if name is provided
const isFormValid = computed(() => {
    return form.name.trim() !== '';
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Variant" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="`/dashboard/products/${product.id}/variants`">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Create Variant</h1>
                    <p class="text-muted-foreground">Add a new variant for: {{ product.name }}</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit">
                <VariantForm
                    v-model="form"
                    mode="create"
                    :attributes="attributes"
                    :product="product"
                    :product-settings="productSettings"
                >
                    <template #actions>
                        <Card>
                            <CardContent class="pt-6 space-y-3">
                                <Button
                                    type="submit"
                                    class="w-full"
                                    :disabled="form.processing || !isFormValid"
                                >
                                    {{ form.processing ? 'Creating...' : 'Create Variant' }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="w-full"
                                    as-child
                                >
                                    <Link :href="`/dashboard/products/${product.id}/variants`">Cancel</Link>
                                </Button>
                            </CardContent>
                        </Card>
                    </template>
                </VariantForm>
            </form>
        </div>
    </AppLayout>
</template>
