<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { type VNode } from 'vue';
import {
    ArrowLeft,
    Pencil,
    Trash2,
    PackageSearch,
    Package,
    Building2,
    Calendar,
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
import type { ProductTypeShowProps } from '@product/types';

defineOptions({
    layout: (h: (type: unknown, props: unknown, children: unknown) => VNode, page: VNode) =>
        h(AppLayout, { breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Products', href: '/dashboard/products' },
            { title: 'Product Types', href: '/dashboard/product-types' },
            { title: 'View', href: '#' },
        ] as BreadcrumbItem[] }, () => page),
});

const props = defineProps<ProductTypeShowProps>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const handleBack = () => {
    router.visit('/dashboard/product-types');
};

const handleEdit = () => {
    router.visit(`/dashboard/product-types/${props.productType.id}/edit`);
};

const handleDelete = () => {
    if (confirm(`Are you sure you want to delete "${props.productType.name}"?`)) {
        router.delete(`/dashboard/product-types/${props.productType.id}`);
    }
};

const handleViewProducts = () => {
    router.visit(`/dashboard/products?product_type_id=${props.productType.id}`);
};
</script>

<template>
    <Head :title="productType.name" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <Button variant="ghost" @click="handleBack">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Product Types
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
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                    <PackageSearch class="h-6 w-6 text-primary" />
                                </div>
                                <div>
                                    <CardTitle class="text-2xl">{{ productType.name }}</CardTitle>
                                    <CardDescription v-if="productType.slug">
                                        Slug: {{ productType.slug }}
                                    </CardDescription>
                                </div>
                            </div>
                            <Badge :variant="productType.is_active ? 'default' : 'secondary'">
                                {{ productType.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground">Description</h4>
                                <p class="mt-1">{{ productType.description || 'No description provided' }}</p>
                            </div>

                            <Separator />

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">Sort Order</h4>
                                    <Badge variant="outline" class="tabular-nums">
                                        {{ productType.sort_order }}
                                    </Badge>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">UUID</h4>
                                    <code class="text-xs bg-muted px-2 py-1 rounded">
                                        {{ productType.uuid }}
                                    </code>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Products Section -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Package class="h-5 w-5" />
                                <CardTitle class="text-lg">Products</CardTitle>
                            </div>
                            <Button
                                v-if="(productType.products_count ?? 0) > 0"
                                variant="outline"
                                size="sm"
                                @click="handleViewProducts"
                            >
                                View All
                            </Button>
                        </div>
                        <CardDescription>
                            {{ productType.products_count ?? 0 }} product(s) in this type
                        </CardDescription>
                    </CardHeader>
                    <CardContent v-if="(productType.products_count ?? 0) > 0">
                        <div class="flex items-center justify-center py-4">
                            <Button variant="default" @click="handleViewProducts">
                                <Package class="mr-2 h-4 w-4" />
                                View {{ productType.products_count }} Products
                            </Button>
                        </div>
                    </CardContent>
                    <CardContent v-else class="flex flex-col items-center justify-center py-8 text-center">
                        <Package class="h-12 w-12 text-muted-foreground mb-3" />
                        <p class="text-muted-foreground">No products in this type yet</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Outlet Info -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Building2 class="h-5 w-5" />
                            <CardTitle class="text-lg">Outlet</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="productType.outlet" class="flex items-center gap-2">
                            <Badge
                                variant="secondary"
                                class="cursor-pointer transition-colors hover:bg-secondary/80"
                                @click="router.visit(`/dashboard/products?outlet_id=${productType.outlet.id}`)"
                            >
                                {{ productType.outlet.name }}
                            </Badge>
                        </div>
                        <span v-else class="text-muted-foreground">No outlet assigned</span>
                    </CardContent>
                </Card>

                <!-- Timestamps -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Calendar class="h-5 w-5" />
                            <CardTitle class="text-lg">Timestamps</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Created</span>
                            <span class="text-sm">{{ formatDate(productType.created_at) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Updated</span>
                            <span class="text-sm">{{ formatDate(productType.updated_at) }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
