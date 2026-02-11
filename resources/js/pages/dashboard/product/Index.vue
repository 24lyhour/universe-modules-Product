<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, type VNode } from 'vue';
import {
    Plus,
    Eye,
    Pencil,
    Trash2,
    Package,
    PackageCheck,
    PackageX,
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    TableReusable,
    ModalConfirm,
    StatsCard,
    type TableColumn,
    type TableAction,
} from '@/components/shared';
import { type BreadcrumbItem } from '@/types';
import type { Product, ProductIndexProps } from '../../../types';

defineOptions({
    layout: (h: (type: unknown, props: unknown, children: unknown) => VNode, page: VNode) =>
        h(AppLayout, { breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Products', href: '/dashboard/products' },
        ] as BreadcrumbItem[] }, () => page),
});

const props = defineProps<ProductIndexProps>();

// Search and filters
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const outletFilter = ref(props.filters.outlet_id?.toString() || '');

// Filtered data
const filteredProducts = computed(() => {
    if (!searchQuery.value) {
        return props.products.data;
    }
    const query = searchQuery.value.toLowerCase();
    return props.products.data.filter(
        (item) =>
            item.name.toLowerCase().includes(query) ||
            item.sku?.toLowerCase().includes(query) ||
            item.description?.toLowerCase().includes(query)
    );
});

// Delete modal state
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedProduct = ref<Product | null>(null);

// Table columns
const columns: TableColumn<Product>[] = [
    { key: 'name', label: 'Product', width: '30%' },
    { key: 'sku', label: 'SKU' },
    { key: 'price', label: 'Price', align: 'right' },
    { key: 'stock', label: 'Stock', align: 'center' },
    { key: 'status', label: 'Status' },
    { key: 'is_featured', label: 'Featured', align: 'center' },
    { key: 'created_at', label: 'Created' },
];

// Table actions
const tableActions: TableAction<Product>[] = [
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
        label: 'Toggle Featured',
        icon: Star,
        onClick: (item) => toggleFeatured(item),
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
const handleCreate = () => {
    router.visit('/dashboard/products/create');
};

const handleShow = (item: Product) => {
    router.visit(`/dashboard/products/${item.id}`);
};

const handleEdit = (item: Product) => {
    router.visit(`/dashboard/products/${item.id}/edit`);
};

const openDeleteModal = (product: Product) => {
    selectedProduct.value = product;
    isDeleteModalOpen.value = true;
};

const handleDelete = () => {
    if (!selectedProduct.value) return;
    isDeleting.value = true;
    router.delete(`/dashboard/products/${selectedProduct.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedProduct.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const toggleFeatured = (product: Product) => {
    router.patch(`/dashboard/products/${product.id}/toggle-featured`);
};

const handlePageChange = (page: number) => {
    router.get('/dashboard/products', {
        page,
        search: searchQuery.value,
        status: statusFilter.value,
        outlet_id: outletFilter.value || undefined,
    }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/products', {
        per_page: perPage,
        search: searchQuery.value,
        status: statusFilter.value,
        outlet_id: outletFilter.value || undefined,
    }, { preserveState: true });
};

const handleStatusFilter = (status: string) => {
    statusFilter.value = status;
    router.get('/dashboard/products', {
        search: searchQuery.value,
        status: status,
        outlet_id: outletFilter.value || undefined,
    }, { preserveState: true });
};

const handleOutletFilter = (outletId: string) => {
    const actualId = outletId === 'all' ? '' : outletId;
    outletFilter.value = actualId;
    router.get('/dashboard/products', {
        search: searchQuery.value,
        status: statusFilter.value,
        outlet_id: actualId || undefined,
    }, { preserveState: true });
};

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
</script>

<template>
    <Head title="Products" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-4">
            <StatsCard
                title="Total Products"
                :value="stats.total"
                :icon="Package"
                icon-color="text-muted-foreground"
            />
            <StatsCard
                title="Active"
                :value="stats.active"
                :icon="PackageCheck"
                icon-color="text-green-500"
                value-color="text-green-600"
            />
            <StatsCard
                title="Out of Stock"
                :value="stats.out_of_stock"
                :icon="PackageX"
                icon-color="text-red-500"
                value-color="text-red-600"
            />
            <StatsCard
                title="Low Stock"
                :value="stats.low_stock"
                :icon="AlertTriangle"
                icon-color="text-yellow-500"
                value-color="text-yellow-600"
            />
        </div>

        <!-- Main Card with Table -->
        <Card>
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle>Products</CardTitle>
                        <CardDescription>Manage your products inventory</CardDescription>
                    </div>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Product
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <TableReusable
                    v-model:search-query="searchQuery"
                    :data="filteredProducts"
                    :columns="columns"
                    :actions="tableActions"
                    :pagination="products.meta"
                    search-placeholder="Search products..."
                    empty-message="No products found."
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                >
                    <!-- Toolbar slot for filters -->
                    <template #toolbar>
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Outlet Filter -->
                            <Select :model-value="outletFilter || 'all'" @update:model-value="handleOutletFilter">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="All Outlets" />
                                </SelectTrigger>
                                <SelectContent class="z-200">
                                    <SelectItem value="all">All Outlets</SelectItem>
                                    <SelectItem
                                        v-for="outlet in props.outlets"
                                        :key="outlet.id"
                                        :value="outlet.id.toString()"
                                    >
                                        {{ outlet.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <!-- Status Filters -->
                            <Button
                                :variant="statusFilter === '' ? 'default' : 'outline'"
                                size="sm"
                                @click="handleStatusFilter('')"
                            >
                                All
                            </Button>
                            <Button
                                :variant="statusFilter === 'active' ? 'default' : 'outline'"
                                size="sm"
                                @click="handleStatusFilter('active')"
                            >
                                Active
                            </Button>
                            <Button
                                :variant="statusFilter === 'draft' ? 'default' : 'outline'"
                                size="sm"
                                @click="handleStatusFilter('draft')"
                            >
                                Draft
                            </Button>
                            <Button
                                :variant="statusFilter === 'out_of_stock' ? 'default' : 'outline'"
                                size="sm"
                                @click="handleStatusFilter('out_of_stock')"
                            >
                                Out of Stock
                            </Button>
                        </div>
                    </template>

                    <!-- Custom cell for product name -->
                    <template #cell-name="{ item }">
                        <div class="flex items-center gap-3">
                            <div
                                v-if="item.images && item.images.length > 0"
                                class="h-10 w-10 overflow-hidden rounded-md bg-muted"
                            >
                                <img
                                    :src="item.images[0]"
                                    :alt="item.name"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <div
                                v-else
                                class="flex h-10 w-10 items-center justify-center rounded-md bg-muted"
                            >
                                <Package class="h-5 w-5 text-muted-foreground" />
                            </div>
                            <div>
                                <div class="font-medium">{{ item.name }}</div>
                                <div
                                    v-if="item.description"
                                    class="max-w-[200px] truncate text-sm text-muted-foreground"
                                >
                                    {{ item.description }}
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Custom cell for SKU -->
                    <template #cell-sku="{ item }">
                        <code v-if="item.sku" class="rounded bg-muted px-2 py-1 text-sm">
                            {{ item.sku }}
                        </code>
                        <span v-else class="text-muted-foreground">-</span>
                    </template>

                    <!-- Custom cell for price -->
                    <template #cell-price="{ item }">
                        <div class="text-right">
                            <div v-if="item.is_on_sale" class="flex flex-col items-end">
                                <span class="font-medium text-green-600">
                                    {{ formatCurrency(item.sale_price!) }}
                                </span>
                                <span class="text-sm text-muted-foreground line-through">
                                    {{ formatCurrency(item.price) }}
                                </span>
                            </div>
                            <span v-else class="font-medium">
                                {{ formatCurrency(item.price) }}
                            </span>
                        </div>
                    </template>

                    <!-- Custom cell for stock -->
                    <template #cell-stock="{ item }">
                        <div class="flex items-center justify-center gap-2">
                            <span
                                :class="{
                                    'text-red-600': item.stock === 0,
                                    'text-yellow-600': item.is_low_stock,
                                    'text-green-600': !item.is_low_stock && item.stock > 0,
                                }"
                            >
                                {{ item.stock }}
                            </span>
                            <AlertTriangle
                                v-if="item.is_low_stock"
                                class="h-4 w-4 text-yellow-500"
                            />
                        </div>
                    </template>

                    <!-- Custom cell for status badge -->
                    <template #cell-status="{ item }">
                        <Badge :variant="getStatusVariant(item.status)">
                            {{ item.status.replace('_', ' ') }}
                        </Badge>
                    </template>

                    <!-- Custom cell for featured -->
                    <template #cell-is_featured="{ item }">
                        <Star
                            v-if="item.is_featured"
                            class="h-5 w-5 fill-yellow-400 text-yellow-400"
                        />
                        <span v-else class="text-muted-foreground">-</span>
                    </template>

                    <!-- Custom cell for date -->
                    <template #cell-created_at="{ item }">
                        <span class="text-sm text-muted-foreground">
                            {{ new Date(item.created_at).toLocaleDateString() }}
                        </span>
                    </template>
                </TableReusable>
            </CardContent>
        </Card>
    </div>

    <!-- Delete Confirmation Modal -->
    <ModalConfirm
        v-model:open="isDeleteModalOpen"
        title="Delete Product"
        :description="`Are you sure you want to delete '${selectedProduct?.name}'? This action cannot be undone.`"
        confirm-text="Delete"
        variant="danger"
        :loading="isDeleting"
        @confirm="handleDelete"
    />
</template>
