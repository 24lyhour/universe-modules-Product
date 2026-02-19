<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import VariantForm from '../../components/VariantForm.vue';
import type { Product, ProductAttribute, ProductVariant } from '../../../types';

interface ProductSettings {
    auto_generate_sku?: boolean;
    sku_prefix?: string;
    sku_separator?: string;
    low_stock_threshold?: number;
}

interface Props {
    product: Product;
    variant: ProductVariant;
    attributes: ProductAttribute[];
    productSettings?: ProductSettings;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: props.product.name, href: `/dashboard/products/${props.product.id}` },
    { title: 'Variants', href: `/dashboard/products/${props.product.id}/variants` },
    { title: props.variant.name || props.variant.sku || 'Edit', href: `/dashboard/products/${props.product.id}/variants/${props.variant.id}/edit` },
];

// Get existing attribute value IDs from the variant
const existingValueIds = computed(() => {
    if (!props.variant.attribute_value_relations) return [];
    return props.variant.attribute_value_relations.map(v => v.id);
});

const form = useForm({
    sku: props.variant.sku || '',
    name: props.variant.name || '',
    price: props.variant.price,
    purchase_price: props.variant.purchase_price,
    sale_price: props.variant.sale_price,
    stock: props.variant.stock,
    low_stock_threshold: props.variant.low_stock_threshold,
    barcode: props.variant.barcode || '',
    weight: props.variant.weight,
    is_default: props.variant.is_default,
    is_active: props.variant.is_active,
    sort_order: props.variant.sort_order,
    attribute_value_ids: existingValueIds.value,
});

const handleSubmit = () => {
    form.put(`/dashboard/products/${props.product.id}/variants/${props.variant.id}`, {
        preserveScroll: true,
    });
};

// Form is valid if name is provided
const isFormValid = computed(() => {
    return form.name.trim() !== '';
});

// Show attributes section open if there are existing values
const hasExistingAttributes = existingValueIds.value.length > 0;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit ${variant.name || variant.sku || 'Variant'}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="`/dashboard/products/${product.id}/variants`">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Variant</h1>
                    <p class="text-muted-foreground">Update variant for: {{ product.name }}</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit">
                <VariantForm
                    v-model="form"
                    mode="edit"
                    :attributes="attributes"
                    :show-attributes-open="hasExistingAttributes"
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
                                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
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
