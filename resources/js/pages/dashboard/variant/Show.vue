<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Pencil, Trash2, ArrowLeft, Package, Star, Check, X } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ModalConfirm } from '@/components/shared';
import { type BreadcrumbItem } from '@/types';
import type { Product, ProductVariant } from '../../../types';

interface Props {
    product: Product;
    variant: ProductVariant;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: props.product.name, href: `/dashboard/products/${props.product.id}` },
    { title: 'Variants', href: `/dashboard/products/${props.product.id}/variants` },
    { title: props.variant.name || props.variant.sku || 'View', href: `/dashboard/products/${props.product.id}/variants/${props.variant.id}` },
];

// Delete modal state
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);

const handleDelete = () => {
    isDeleting.value = true;
    router.delete(`/dashboard/products/${props.product.id}/variants/${props.variant.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const formatPrice = (price: number | null) => {
    if (price === null) return '-';
    return `$${price.toFixed(2)}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStockStatus = () => {
    if (props.variant.stock === 0) return { label: 'Out of Stock', variant: 'destructive' };
    if (props.variant.is_low_stock) return { label: 'Low Stock', variant: 'secondary' };
    return { label: 'In Stock', variant: 'default' };
};

const getAttributeValues = () => {
    if (!props.variant.attribute_value_relations) return [];
    return props.variant.attribute_value_relations;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="variant.name || variant.sku || 'Variant Details'" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="`/dashboard/products/${product.id}/variants`">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">
                                {{ variant.name || 'Unnamed Variant' }}
                            </h1>
                            <Badge v-if="variant.is_default" variant="secondary">
                                <Star class="mr-1 h-3 w-3" />
                                Default
                            </Badge>
                        </div>
                        <p class="text-muted-foreground">Product: {{ product.name }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/dashboard/products/${product.id}/variants/${variant.id}/edit`">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="isDeleteModalOpen = true">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Pricing Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Pricing</CardTitle>
                            <CardDescription>Variant pricing information</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="space-y-1">
                                    <span class="text-sm text-muted-foreground">Price</span>
                                    <p class="text-2xl font-bold">{{ formatPrice(variant.price) }}</p>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-sm text-muted-foreground">Purchase Price</span>
                                    <p class="text-lg">{{ formatPrice(variant.purchase_price) }}</p>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-sm text-muted-foreground">Sale Price</span>
                                    <p class="text-lg" :class="{ 'text-green-600': variant.sale_price }">
                                        {{ formatPrice(variant.sale_price) }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="variant.display_price" class="mt-4 pt-4 border-t">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Effective Display Price</span>
                                    <span class="text-xl font-semibold text-primary">
                                        {{ formatPrice(variant.display_price) }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Inventory Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Inventory</CardTitle>
                            <CardDescription>Stock and inventory details</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="space-y-1">
                                    <span class="text-sm text-muted-foreground">Current Stock</span>
                                    <div class="flex items-center gap-2">
                                        <p class="text-2xl font-bold">{{ variant.stock }}</p>
                                        <Badge :variant="getStockStatus().variant as 'default' | 'destructive' | 'secondary'">
                                            {{ getStockStatus().label }}
                                        </Badge>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-sm text-muted-foreground">Low Stock Threshold</span>
                                    <p class="text-lg">{{ variant.low_stock_threshold }}</p>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-sm text-muted-foreground">Weight</span>
                                    <p class="text-lg">{{ variant.weight ? `${variant.weight} kg` : '-' }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Attribute Values Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Attribute Values</CardTitle>
                            <CardDescription>Selected options for this variant</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="!getAttributeValues().length" class="text-center py-8 text-muted-foreground">
                                No attribute values assigned.
                            </div>
                            <div v-else class="flex flex-wrap gap-3">
                                <div
                                    v-for="value in getAttributeValues()"
                                    :key="value.id"
                                    class="flex items-center gap-2 px-3 py-2 bg-muted rounded-lg"
                                >
                                    <span
                                        v-if="value.color_code"
                                        class="h-5 w-5 rounded-full border"
                                        :style="{ backgroundColor: value.color_code }"
                                    />
                                    <div>
                                        <span class="text-xs text-muted-foreground">
                                            {{ value.attribute?.name }}
                                        </span>
                                        <p class="font-medium">{{ value.label || value.value }}</p>
                                    </div>
                                    <span v-if="value.price_adjustment !== 0" class="text-xs text-muted-foreground ml-2">
                                        {{ value.price_adjustment > 0 ? '+' : '' }}${{ value.price_adjustment.toFixed(2) }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Details Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">SKU</span>
                                <code v-if="variant.sku" class="text-xs bg-muted px-2 py-1 rounded">
                                    {{ variant.sku }}
                                </code>
                                <span v-else class="text-muted-foreground">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Barcode</span>
                                <code v-if="variant.barcode" class="text-xs bg-muted px-2 py-1 rounded">
                                    {{ variant.barcode }}
                                </code>
                                <span v-else class="text-muted-foreground">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Sort Order</span>
                                <span class="font-medium">{{ variant.sort_order }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Status Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Status</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Active</span>
                                <Badge :variant="variant.is_active ? 'default' : 'secondary'">
                                    <Check v-if="variant.is_active" class="mr-1 h-3 w-3" />
                                    <X v-else class="mr-1 h-3 w-3" />
                                    {{ variant.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Default</span>
                                <Badge :variant="variant.is_default ? 'default' : 'secondary'">
                                    <Check v-if="variant.is_default" class="mr-1 h-3 w-3" />
                                    <X v-else class="mr-1 h-3 w-3" />
                                    {{ variant.is_default ? 'Yes' : 'No' }}
                                </Badge>
                            </div>
                            <div v-if="variant.is_on_sale" class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">On Sale</span>
                                <Badge variant="default" class="bg-green-600">
                                    <Check class="mr-1 h-3 w-3" />
                                    On Sale
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Timestamps Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Timestamps</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <span class="text-sm text-muted-foreground">Created</span>
                                <p class="text-sm">{{ formatDate(variant.created_at) }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-muted-foreground">Updated</span>
                                <p class="text-sm">{{ formatDate(variant.updated_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ModalConfirm
            v-model:open="isDeleteModalOpen"
            title="Delete Variant"
            :description="`Are you sure you want to delete this variant? This action cannot be undone.`"
            confirm-text="Delete"
            variant="danger"
            :loading="isDeleting"
            @confirm="handleDelete"
        />
    </AppLayout>
</template>
