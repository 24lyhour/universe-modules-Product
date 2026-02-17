<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Plus,
    Eye,
    Pencil,
    Trash2,
    Package,
    ArrowLeft,
    Star,
    AlertTriangle,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    TableReusable,
    ModalConfirm,
    StatsCard,
    type TableColumn,
    type TableAction,
} from '@/components/shared';
import { type BreadcrumbItem } from '@/types';
import type { ProductVariantIndexProps, ProductVariant } from '../../../types';

const props = defineProps<ProductVariantIndexProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: props.product.name, href: `/dashboard/products/${props.product.id}` },
    { title: 'Variants', href: `/dashboard/products/${props.product.id}/variants` },
];

// Delete modal state
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedVariant = ref<ProductVariant | null>(null);

// Pagination
const pagination = computed(() => ({
    current_page: props.variants.meta.current_page,
    last_page: props.variants.meta.last_page,
    per_page: props.variants.meta.per_page,
    total: props.variants.meta.total,
}));

// Stats
const stats = computed(() => {
    const variants = props.variants.data;
    const totalStock = variants.reduce((sum, v) => sum + v.stock, 0);
    const activeCount = variants.filter(v => v.is_active).length;
    const lowStockCount = variants.filter(v => v.is_low_stock).length;
    return {
        total: props.variants.meta.total,
        totalStock,
        active: activeCount,
        lowStock: lowStockCount,
    };
});

// Table columns
const columns: TableColumn<ProductVariant>[] = [
    { key: 'name', label: 'Variant', width: '30%' },
    { key: 'sku', label: 'SKU' },
    { key: 'price', label: 'Price', align: 'right' },
    { key: 'stock', label: 'Stock', align: 'center' },
    { key: 'is_active', label: 'Status' },
];

// Table actions
const tableActions: TableAction<ProductVariant>[] = [
    {
        label: 'View',
        icon: Eye,
        onClick: (item) => handleShow(item),
    },
    {
        label: 'Edit',
        icon: Pencil,
        onClick: (item) => handleEdit(item),
    },
    {
        label: 'Delete',
        icon: Trash2,
        onClick: (item) => openDeleteModal(item),
        variant: 'destructive',
        separator: true,
    },
];

// Handlers
const handleShow = (item: ProductVariant) => {
    router.visit(`/dashboard/products/${props.product.id}/variants/${item.id}`);
};

const handleEdit = (item: ProductVariant) => {
    router.visit(`/dashboard/products/${props.product.id}/variants/${item.id}/edit`);
};

const openDeleteModal = (variant: ProductVariant) => {
    selectedVariant.value = variant;
    isDeleteModalOpen.value = true;
};

const handleDelete = () => {
    if (!selectedVariant.value) return;
    isDeleting.value = true;
    router.delete(`/dashboard/products/${props.product.id}/variants/${selectedVariant.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedVariant.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const handlePageChange = (page: number) => {
    router.get(`/dashboard/products/${props.product.id}/variants`, {
        page,
        per_page: pagination.value.per_page,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get(`/dashboard/products/${props.product.id}/variants`, {
        page: 1,
        per_page: perPage,
    }, { preserveState: true, preserveScroll: true });
};

const formatPrice = (price: number | null) => {
    if (price === null) return '-';
    return `$${price.toFixed(2)}`;
};

const getStockVariant = (variant: ProductVariant) => {
    if (variant.stock === 0) return 'destructive';
    if (variant.is_low_stock) return 'secondary';
    return 'outline';
};

const getAttributeDisplay = (variant: ProductVariant) => {
    if (!variant.attribute_values) return [];
    return Object.entries(variant.attribute_values).map(([key, value]) => ({
        name: key,
        value: value,
    }));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${product.name} - Variants`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="`/dashboard/products/${product.id}`">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Product Variants</h1>
                        <p class="text-muted-foreground">Manage variants for: {{ product.name }}</p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="`/dashboard/products/${product.id}/variants/create`">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Variant
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <StatsCard
                    title="Total Variants"
                    :value="stats.total"
                    :icon="Package"
                />
                <StatsCard
                    title="Total Stock"
                    :value="stats.totalStock"
                    :icon="Package"
                    variant="success"
                />
                <StatsCard
                    title="Active"
                    :value="stats.active"
                    :icon="Star"
                />
                <StatsCard
                    title="Low Stock"
                    :value="stats.lowStock"
                    :icon="AlertTriangle"
                    variant="warning"
                />
            </div>

            <!-- Table -->
            <TableReusable
                :data="props.variants.data"
                :columns="columns"
                :actions="tableActions"
                :pagination="pagination"
                @page-change="handlePageChange"
                @per-page-change="handlePerPageChange"
            >
                <!-- Custom cell for name/variant -->
                <template #cell-name="{ item }">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ item.name || 'Unnamed' }}</span>
                            <Badge v-if="item.is_default" variant="secondary" class="text-xs">
                                Default
                            </Badge>
                        </div>
                        <div v-if="getAttributeDisplay(item).length" class="flex flex-wrap gap-1">
                            <Badge
                                v-for="attr in getAttributeDisplay(item)"
                                :key="attr.name"
                                variant="outline"
                                class="text-xs"
                            >
                                {{ attr.name }}: {{ attr.value }}
                            </Badge>
                        </div>
                    </div>
                </template>

                <!-- Custom cell for SKU -->
                <template #cell-sku="{ item }">
                    <code v-if="item.sku" class="text-xs bg-muted px-2 py-1 rounded">
                        {{ item.sku }}
                    </code>
                    <span v-else class="text-muted-foreground">-</span>
                </template>

                <!-- Custom cell for price -->
                <template #cell-price="{ item }">
                    <div class="text-right">
                        <div v-if="item.sale_price" class="flex flex-col">
                            <span class="line-through text-muted-foreground text-xs">
                                {{ formatPrice(item.price) }}
                            </span>
                            <span class="text-green-600 font-medium">
                                {{ formatPrice(item.sale_price) }}
                            </span>
                        </div>
                        <span v-else class="font-medium">
                            {{ formatPrice(item.price || item.effective_price || null) }}
                        </span>
                    </div>
                </template>

                <!-- Custom cell for stock -->
                <template #cell-stock="{ item }">
                    <Badge :variant="getStockVariant(item)" class="tabular-nums">
                        {{ item.stock }}
                    </Badge>
                </template>

                <!-- Custom cell for status -->
                <template #cell-is_active="{ item }">
                    <Badge :variant="item.is_active ? 'default' : 'secondary'">
                        {{ item.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                </template>
            </TableReusable>
        </div>

        <!-- Delete Confirmation Modal -->
        <ModalConfirm
            v-model:open="isDeleteModalOpen"
            title="Delete Variant"
            :description="`Are you sure you want to delete variant '${selectedVariant?.name || selectedVariant?.sku || 'this variant'}'?`"
            confirm-text="Delete"
            variant="danger"
            :loading="isDeleting"
            @confirm="handleDelete"
        />
    </AppLayout>
</template>
