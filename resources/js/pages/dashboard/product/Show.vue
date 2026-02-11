<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { type VNode } from 'vue';
import {
    ArrowLeft,
    Pencil,
    Trash2,
    Package,
    Star,
    AlertTriangle,
} from 'lucide-vue-next';

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
import { Separator } from '@/components/ui/separator';
import { type BreadcrumbItem } from '@/types';
import type { ProductShowProps } from '../../../types';

defineOptions({
    layout: (h: (type: unknown, props: unknown, children: unknown) => VNode, page: VNode) =>
        h(AppLayout, { breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Products', href: '/dashboard/products' },
            { title: 'View Product', href: '#' },
        ] as BreadcrumbItem[] }, () => page),
});

const props = defineProps<ProductShowProps>();

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default';
        case 'inactive':
            return 'secondary';
        case 'draft':
            return 'outline';
        case 'out_of_stock':
            return 'destructive';
        default:
            return 'outline';
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const handleBack = () => {
    router.visit('/dashboard/products');
};

const handleEdit = () => {
    router.visit(`/dashboard/products/${props.product.id}/edit`);
};

const handleDelete = () => {
    if (confirm(`Are you sure you want to delete "${props.product.name}"?`)) {
        router.delete(`/dashboard/products/${props.product.id}`);
    }
};
</script>

<template>
    <Head :title="product.name" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <Button variant="ghost" @click="handleBack">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Products
            </Button>
            <div class="flex gap-2">
                <Button variant="outline" @click="handleEdit">
                    <Pencil class="mr-2 h-4 w-4" />
                    Edit
                </Button>
                <Button variant="destructive" @click="handleDelete">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete
                </Button>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-4">
                <Card>
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div>
                                <CardTitle class="text-2xl">{{ product.name }}</CardTitle>
                                <CardDescription v-if="product.sku">
                                    SKU: {{ product.sku }}
                                </CardDescription>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge :variant="getStatusVariant(product.status)">
                                    {{ product.status.replace('_', ' ') }}
                                </Badge>
                                <Star
                                    v-if="product.is_featured"
                                    class="h-5 w-5 fill-yellow-400 text-yellow-400"
                                />
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground">Description</h4>
                                <p class="mt-1">{{ product.description || 'No description' }}</p>
                            </div>

                            <Separator />

                            <!-- Images -->
                            <div v-if="product.images && product.images.length > 0">
                                <h4 class="text-sm font-medium text-muted-foreground mb-2">Images</h4>
                                <div class="grid grid-cols-4 gap-2">
                                    <div
                                        v-for="(image, index) in product.images"
                                        :key="index"
                                        class="aspect-square overflow-hidden rounded-lg bg-muted"
                                    >
                                        <img
                                            :src="image"
                                            :alt="`${product.name} image ${index + 1}`"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div v-else class="flex items-center justify-center h-32 bg-muted rounded-lg">
                                <Package class="h-12 w-12 text-muted-foreground" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Pricing -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Pricing</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Regular Price</span>
                            <span class="font-medium">{{ formatCurrency(product.price) }}</span>
                        </div>
                        <div v-if="product.sale_price" class="flex justify-between">
                            <span class="text-muted-foreground">Sale Price</span>
                            <span class="font-medium text-green-600">
                                {{ formatCurrency(product.sale_price) }}
                            </span>
                        </div>
                        <div v-if="product.purchase_price" class="flex justify-between">
                            <span class="text-muted-foreground">Purchase Price</span>
                            <span class="font-medium">{{ formatCurrency(product.purchase_price) }}</span>
                        </div>
                        <div v-if="product.discount_percentage" class="flex justify-between">
                            <span class="text-muted-foreground">Discount</span>
                            <Badge variant="secondary">{{ product.discount_percentage }}% OFF</Badge>
                        </div>
                    </CardContent>
                </Card>

                <!-- Inventory -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Inventory</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Stock</span>
                            <div class="flex items-center gap-2">
                                <span
                                    :class="{
                                        'text-red-600': product.stock === 0,
                                        'text-yellow-600': product.is_low_stock,
                                        'text-green-600': !product.is_low_stock && product.stock > 0,
                                    }"
                                    class="font-medium"
                                >
                                    {{ product.stock }}
                                </span>
                                <AlertTriangle
                                    v-if="product.is_low_stock"
                                    class="h-4 w-4 text-yellow-500"
                                />
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Low Stock Threshold</span>
                            <span class="font-medium">{{ product.low_stock_threshold }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Pre-order</span>
                            <Badge :variant="product.pre_order ? 'default' : 'secondary'">
                                {{ product.pre_order ? 'Yes' : 'No' }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <!-- Details -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div v-if="product.category" class="flex justify-between">
                            <span class="text-muted-foreground">Category</span>
                            <span class="font-medium">{{ product.category.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Created</span>
                            <span class="text-sm">
                                {{ new Date(product.created_at).toLocaleDateString() }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Updated</span>
                            <span class="text-sm">
                                {{ new Date(product.updated_at).toLocaleDateString() }}
                            </span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
