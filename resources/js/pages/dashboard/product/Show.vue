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
    Layers,
    Palette,
    Check,
    X,
    Plus,
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
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

const handleManageAttributes = () => {
    router.visit(`/dashboard/products/${props.product.id}/attributes/manage`);
};

const handleManageVariants = () => {
    router.visit(`/dashboard/products/${props.product.id}/variants`);
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
                                <Badge :variant="getStatusVariant(product.status ?? 'draft')">
                                    {{ (product.status ?? 'draft').replace('_', ' ') }}
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

                <!-- Attributes Section -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Palette class="h-5 w-5" />
                                <CardTitle class="text-lg">Attributes</CardTitle>
                            </div>
                            <Button
                                v-if="product.attributes && product.attributes.length > 0"
                                variant="outline"
                                size="sm"
                                @click="handleManageAttributes"
                            >
                                <Pencil class="mr-2 h-4 w-4" />
                                Edit
                            </Button>
                        </div>
                        <CardDescription>
                            {{ product.attributes?.length || 0 }} attribute(s) assigned
                        </CardDescription>
                    </CardHeader>
                    <CardContent v-if="product.attributes && product.attributes.length > 0">
                        <div class="space-y-4">
                            <div
                                v-for="attribute in product.attributes"
                                :key="attribute.id"
                                class="space-y-2"
                            >
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium">{{ attribute.name }}</h4>
                                    <div class="flex items-center gap-2">
                                        <Badge variant="outline">{{ attribute.type }}</Badge>
                                        <Badge :variant="attribute.is_active ? 'default' : 'secondary'">
                                            {{ attribute.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <template v-for="value in attribute.values" :key="value.id">
                                        <Badge
                                            v-if="attribute.type === 'color'"
                                            variant="outline"
                                            class="gap-2"
                                        >
                                            <span
                                                v-if="value.color_code"
                                                class="w-3 h-3 rounded-full border"
                                                :style="{ backgroundColor: value.color_code }"
                                            />
                                            {{ value.label || value.value }}
                                        </Badge>
                                        <Badge v-else variant="secondary">
                                            {{ value.label || value.value }}
                                        </Badge>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardContent v-else class="flex flex-col items-center justify-center py-8 text-center">
                        <Palette class="h-12 w-12 text-muted-foreground mb-3" />
                        <p class="text-muted-foreground mb-3">No attributes assigned to this product</p>
                        <Button variant="outline" size="sm" @click="handleManageAttributes">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Attributes
                        </Button>
                    </CardContent>
                </Card>

                <!-- Variants Section -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Layers class="h-5 w-5" />
                                <CardTitle class="text-lg">Variants</CardTitle>
                            </div>
                            <Button
                                v-if="product.variants && product.variants.length > 0"
                                variant="outline"
                                size="sm"
                                @click="handleManageVariants"
                            >
                                <Pencil class="mr-2 h-4 w-4" />
                                Edit
                            </Button>
                        </div>
                        <CardDescription>
                            {{ product.variants?.length || 0 }} variant(s) available
                        </CardDescription>
                    </CardHeader>
                    <CardContent v-if="product.variants && product.variants.length > 0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Variant</TableHead>
                                    <TableHead>SKU</TableHead>
                                    <TableHead>Price</TableHead>
                                    <TableHead>Stock</TableHead>
                                    <TableHead>Default</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="variant in product.variants"
                                    :key="variant.id"
                                >
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">
                                                {{ variant.name || 'Unnamed Variant' }}
                                            </span>
                                            <div
                                                v-if="variant.attribute_value_relations?.length"
                                                class="flex flex-wrap gap-1 mt-1"
                                            >
                                                <Badge
                                                    v-for="av in variant.attribute_value_relations"
                                                    :key="av.id"
                                                    variant="outline"
                                                    class="text-xs"
                                                >
                                                    <span
                                                        v-if="av.color_code"
                                                        class="w-2 h-2 rounded-full mr-1"
                                                        :style="{ backgroundColor: av.color_code }"
                                                    />
                                                    {{ av.attribute?.name }}: {{ av.label || av.value }}
                                                </Badge>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <code class="text-xs bg-muted px-1 py-0.5 rounded">
                                            {{ variant.sku || '-' }}
                                        </code>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span>{{ formatCurrency(variant.price ?? product.price) }}</span>
                                            <span
                                                v-if="variant.sale_price"
                                                class="text-xs text-green-600"
                                            >
                                                Sale: {{ formatCurrency(variant.sale_price) }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            :class="{
                                                'text-red-600': variant.stock === 0,
                                                'text-green-600': variant.stock > 0,
                                            }"
                                        >
                                            {{ variant.stock }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <Check
                                            v-if="variant.is_default"
                                            class="h-4 w-4 text-green-600"
                                        />
                                        <X v-else class="h-4 w-4 text-muted-foreground" />
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="variant.is_active ? 'default' : 'secondary'">
                                            {{ variant.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                    <CardContent v-else class="flex flex-col items-center justify-center py-8 text-center">
                        <Layers class="h-12 w-12 text-muted-foreground mb-3" />
                        <p class="text-muted-foreground mb-3">No variants created for this product</p>
                        <Button variant="outline" size="sm" @click="handleManageVariants">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Variants
                        </Button>
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
