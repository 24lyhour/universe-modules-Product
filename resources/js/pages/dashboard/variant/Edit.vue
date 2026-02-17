<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import type { Product, ProductAttribute, ProductVariant } from '../../../types';

interface Props {
    product: Product;
    variant: ProductVariant;
    attributes: ProductAttribute[];
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

const toggleAttributeValue = (valueId: number) => {
    const index = form.attribute_value_ids.indexOf(valueId);
    if (index > -1) {
        form.attribute_value_ids.splice(index, 1);
    } else {
        form.attribute_value_ids.push(valueId);
    }
};

const isValueSelected = (valueId: number) => {
    return form.attribute_value_ids.includes(valueId);
};

const activeAttributes = computed(() => {
    return props.attributes.filter(attr => attr.is_active && attr.values?.length);
});

const formatPriceAdjustment = (price: number) => {
    if (price === 0) return '';
    const sign = price > 0 ? '+' : '';
    return ` (${sign}$${price.toFixed(2)})`;
};
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

            <form @submit.prevent="handleSubmit" class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Info Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Variant Information</CardTitle>
                            <CardDescription>Basic details about the variant</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="sku">SKU</Label>
                                    <Input
                                        id="sku"
                                        v-model="form.sku"
                                        placeholder="e.g., PROD-001-S"
                                        :class="{ 'border-red-500': form.errors.sku }"
                                    />
                                    <p v-if="form.errors.sku" class="text-sm text-red-500">
                                        {{ form.errors.sku }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="name">Name</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="Auto-generated if empty"
                                    />
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="barcode">Barcode</Label>
                                    <Input
                                        id="barcode"
                                        v-model="form.barcode"
                                        placeholder="e.g., 1234567890123"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="weight">Weight (kg)</Label>
                                    <Input
                                        id="weight"
                                        v-model.number="form.weight"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Pricing Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Pricing</CardTitle>
                            <CardDescription>Leave empty to use product's base price</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="space-y-2">
                                    <Label for="price">Price</Label>
                                    <Input
                                        id="price"
                                        v-model.number="form.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="purchase_price">Purchase Price</Label>
                                    <Input
                                        id="purchase_price"
                                        v-model.number="form.purchase_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="sale_price">Sale Price</Label>
                                    <Input
                                        id="sale_price"
                                        v-model.number="form.sale_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Inventory Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Inventory</CardTitle>
                            <CardDescription>Stock and inventory settings</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="stock">Stock *</Label>
                                    <Input
                                        id="stock"
                                        v-model.number="form.stock"
                                        type="number"
                                        min="0"
                                        required
                                        :class="{ 'border-red-500': form.errors.stock }"
                                    />
                                    <p v-if="form.errors.stock" class="text-sm text-red-500">
                                        {{ form.errors.stock }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="low_stock_threshold">Low Stock Threshold</Label>
                                    <Input
                                        id="low_stock_threshold"
                                        v-model.number="form.low_stock_threshold"
                                        type="number"
                                        min="0"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Attributes Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Attribute Values *</CardTitle>
                            <CardDescription>Select attribute values for this variant</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="!activeAttributes.length" class="text-center py-8 text-muted-foreground">
                                No attributes available. Create attributes first.
                            </div>

                            <div v-else class="space-y-6">
                                <div v-for="attribute in activeAttributes" :key="attribute.id" class="space-y-3">
                                    <h4 class="font-medium">{{ attribute.name }}</h4>
                                    <div class="flex flex-wrap gap-3">
                                        <div
                                            v-for="value in attribute.values"
                                            :key="value.id"
                                            class="flex items-center space-x-2"
                                        >
                                            <Checkbox
                                                :id="`value-${value.id}`"
                                                :checked="isValueSelected(value.id)"
                                                @update:checked="toggleAttributeValue(value.id)"
                                            />
                                            <label
                                                :for="`value-${value.id}`"
                                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer"
                                            >
                                                <span class="flex items-center gap-2">
                                                    <span
                                                        v-if="attribute.type === 'color' && value.color_code"
                                                        class="h-4 w-4 rounded-full border"
                                                        :style="{ backgroundColor: value.color_code }"
                                                    />
                                                    {{ value.label || value.value }}
                                                    <span class="text-muted-foreground text-xs">
                                                        {{ formatPriceAdjustment(value.price_adjustment) }}
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p v-if="form.errors.attribute_value_ids" class="text-sm text-red-500 mt-2">
                                {{ form.errors.attribute_value_ids }}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Settings Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Settings</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="sort_order">Sort Order</Label>
                                <Input
                                    id="sort_order"
                                    v-model.number="form.sort_order"
                                    type="number"
                                    min="0"
                                />
                            </div>

                            <div class="flex items-center justify-between">
                                <Label for="is_default">Default Variant</Label>
                                <Switch
                                    id="is_default"
                                    :checked="form.is_default"
                                    @update:checked="form.is_default = $event"
                                />
                            </div>

                            <div class="flex items-center justify-between">
                                <Label for="is_active">Active</Label>
                                <Switch
                                    id="is_active"
                                    :checked="form.is_active"
                                    @update:checked="form.is_active = $event"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions Card -->
                    <Card>
                        <CardContent class="pt-6 space-y-3">
                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="form.processing || !form.attribute_value_ids.length"
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
                </div>
            </form>
        </div>
    </AppLayout>
</template>
