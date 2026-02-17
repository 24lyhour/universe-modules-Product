<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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
    Layers,
    Tags,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
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

const props = defineProps<ProductIndexProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
];

// Search and filters
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const outletFilter = ref(props.filters.outlet_id?.toString() || '');

// Delete modal state
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedProduct = ref<Product | null>(null);

// Pagination
const pagination = computed(() => ({
    current_page: props.products.meta.current_page,
    last_page: props.products.meta.last_page,
    per_page: props.products.meta.per_page,
    total: props.products.meta.total,
}));

// Table columns
const columns: TableColumn<Product>[] = [
    { key: 'name', label: 'Product', width: '25%' },
    { key: 'sku', label: 'SKU' },
    { key: 'price', label: 'Price', align: 'right' },
    { key: 'stock', label: 'Stock', align: 'center' },
    { key: 'variants', label: 'Variants', align: 'center' },
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
        per_page: pagination.value.per_page,
        search: searchQuery.value,
        status: statusFilter.value,
        outlet_id: outletFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/products', {
        page: 1,
        per_page: perPage,
        search: searchQuery.value,
        status: statusFilter.value,
        outlet_id: outletFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (search: string) => {
    searchQuery.value = search;
    router.get('/dashboard/products', {
        search,
        status: statusFilter.value,
        outlet_id: outletFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleStatusFilter = (status: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    const statusStr = String(status || 'all');
    statusFilter.value = statusStr === 'all' ? '' : statusStr;
    router.get('/dashboard/products', {
        search: searchQuery.value,
        status: statusStr === 'all' ? '' : statusStr,
        outlet_id: outletFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleOutletFilter = (outletId: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    const outletStr = String(outletId || 'all');
    const actualId = outletStr === 'all' ? '' : outletStr;
    outletFilter.value = actualId;
    router.get('/dashboard/products', {
        search: searchQuery.value,
        status: statusFilter.value,
        outlet_id: actualId || undefined,
    }, { preserveState: true, preserveScroll: true });
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

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Transform data for table
const tableData = computed(() => {
    return props.products.data.map((product) => ({
        ...product,
        created_at_formatted: formatDate(product.created_at),
    }));
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Products" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Products</h1>
                    <p class="text-muted-foreground">Manage your products inventory</p>
                </div>
                <Button as-child>
                    <Link href="/dashboard/products/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Product
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-5">
                <StatsCard
                    title="Total Products"
                    :value="stats.total"
                    :icon="Package"
                />
                <StatsCard
                    title="Active"
                    :value="stats.active"
                    :icon="PackageCheck"
                    variant="success"
                />
                <StatsCard
                    title="Out of Stock"
                    :value="stats.out_of_stock"
                    :icon="PackageX"
                    variant="destructive"
                />
                <StatsCard
                    title="Low Stock"
                    :value="stats.low_stock"
                    :icon="AlertTriangle"
                    variant="warning"
                />
                <StatsCard
                    title="Featured"
                    :value="stats.featured || 0"
                    :icon="Star"
                    variant="info"
                />
            </div>

            <!-- Table -->
            <TableReusable
                :data="tableData"
                :columns="columns"
                :actions="tableActions"
                :pagination="pagination"
                :searchable="true"
                search-placeholder="Search products by name or SKU..."
                @page-change="handlePageChange"
                @per-page-change="handlePerPageChange"
                @search="handleSearch"
            >
                <!-- Toolbar slot for filters -->
                <template #toolbar>
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Outlet Filter -->
                        <Select :model-value="outletFilter || 'all'" @update:model-value="handleOutletFilter">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="All Outlets" />
                            </SelectTrigger>
                            <SelectContent>
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

                        <!-- Status Filter -->
                        <Select :model-value="statusFilter || 'all'" @update:model-value="handleStatusFilter">
                            <SelectTrigger class="w-[150px]">
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="draft">Draft</SelectItem>
                                <SelectItem value="out_of_stock">Out of Stock</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <!-- Custom cell for product name -->
                <template #cell-name="{ item }">
                    <div class="flex items-center gap-3">
                        <div
                            v-if="item.images && item.images.length > 0"
                            class="h-10 w-10 overflow-hidden rounded-lg bg-muted"
                        >
                            <img
                                :src="item.images[0]"
                                :alt="item.name"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted"
                        >
                            <Package class="h-5 w-5 text-muted-foreground" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="font-medium">{{ item.name }}</span>
                            <span
                                v-if="item.description"
                                class="max-w-[200px] truncate text-xs text-muted-foreground"
                            >
                                {{ item.description }}
                            </span>
                        </div>
                    </div>
                </template>

                <!-- Custom cell for SKU -->
                <template #cell-sku="{ item }">
                    <code v-if="item.sku" class="rounded bg-muted px-2 py-1 text-xs font-mono">
                        {{ item.sku }}
                    </code>
                    <span v-else class="text-muted-foreground">-</span>
                </template>

                <!-- Custom cell for price -->
                <template #cell-price="{ item }">
                    <div class="text-right">
                        <div v-if="item.is_on_sale" class="flex flex-col items-end gap-0.5">
                            <span class="font-medium text-green-600">
                                {{ formatCurrency(item.sale_price!) }}
                            </span>
                            <span class="text-xs text-muted-foreground line-through">
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
                    <div class="flex items-center justify-center gap-1.5">
                        <Badge
                            :variant="item.stock === 0 ? 'destructive' : item.is_low_stock ? 'outline' : 'secondary'"
                            class="tabular-nums"
                        >
                            {{ item.stock }}
                        </Badge>
                        <AlertTriangle
                            v-if="item.is_low_stock && item.stock > 0"
                            class="h-4 w-4 text-yellow-500"
                        />
                    </div>
                </template>

                <!-- Custom cell for variants -->
                <template #cell-variants="{ item }">
                    <div class="flex items-center justify-center gap-2">
                        <div v-if="item.variants_count > 0" class="flex items-center gap-1.5">
                            <Badge variant="secondary" class="tabular-nums">
                                <Layers class="mr-1 h-3 w-3" />
                                {{ item.variants_count }}
                            </Badge>
                            <Link
                                :href="`/dashboard/products/${item.id}/variants`"
                                class="text-xs text-primary hover:underline"
                            >
                                Manage
                            </Link>
                        </div>
                        <div v-else class="flex items-center gap-1.5">
                            <span class="text-muted-foreground">-</span>
                            <Link
                                :href="`/dashboard/products/${item.id}/variants/create`"
                                class="text-xs text-primary hover:underline"
                            >
                                Add
                            </Link>
                        </div>
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
                    <div class="flex justify-center">
                        <Star
                            v-if="item.is_featured"
                            class="h-5 w-5 fill-yellow-400 text-yellow-400"
                        />
                        <span v-else class="text-muted-foreground">-</span>
                    </div>
                </template>

                <!-- Custom cell for date -->
                <template #cell-created_at="{ item }">
                    <span class="text-sm text-muted-foreground">
                        {{ formatDate(item.created_at) }}
                    </span>
                </template>
            </TableReusable>
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
    </AppLayout>
</template>
