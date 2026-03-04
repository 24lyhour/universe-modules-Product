<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, type VNode } from 'vue';
import {
    Plus,
    Eye,
    Pencil,
    Trash2,
    PackageSearch,
    CheckCircle,
    XCircle,
    Database,
    AlertTriangle,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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
    ButtonGroup,
    type TableColumn,
    type TableAction,
} from '@/components/shared';
import type { ProductTypeIndexProps, ProductTypeItem } from '@product/types';

// Persistent layout required for momentum-modal
defineOptions({
    layout: (h: (type: unknown, props: unknown, children: unknown) => VNode, page: VNode) =>
        h(AppLayout, { breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Products', href: '/dashboard/products' },
            { title: 'Product Types', href: '/dashboard/product-types' },
        ]}, () => page),
});

const props = defineProps<ProductTypeIndexProps>();

// State
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.is_active !== undefined ? String(props.filters.is_active) : 'all');
const outletFilter = ref(props.filters.outlet_id?.toString() || 'all');
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedItem = ref<ProductTypeItem | null>(null);
const selectedUuids = ref<(string | number)[]>([]);

// Pagination computed (transform meta to top-level for TableReusable)
const pagination = computed(() => ({
    current_page: props.productTypes.meta.current_page,
    last_page: props.productTypes.meta.last_page,
    per_page: props.productTypes.meta.per_page,
    total: props.productTypes.meta.total,
}));

// Table columns
const columns: TableColumn<ProductTypeItem>[] = [
    { key: 'name', label: 'Name', width: '20%' },
    { key: 'products_count', label: 'Products', width: '10%', align: 'center' },
    { key: 'description', label: 'Description', width: '25%' },
    { key: 'outlet', label: 'Outlet', width: '15%' },
    { key: 'is_active', label: 'Status', width: '15%' },
    { key: 'sort_order', label: 'Order', width: '10%', align: 'center' },
];

// Table actions
const tableActions: TableAction<ProductTypeItem>[] = [
    {
        label: 'View',
        icon: Eye,
        onClick: (item) => router.visit(`/dashboard/product-types/${item.id}`),
    },
    {
        label: 'Edit',
        icon: Pencil,
        onClick: (item) => router.visit(`/dashboard/product-types/${item.id}/edit`),
    },
    {
        label: 'Delete',
        icon: Trash2,
        onClick: (item) => {
            selectedItem.value = item;
            isDeleteModalOpen.value = true;
        },
        variant: 'destructive',
        separator: true,
    },
];

// Delete modal computed
const deleteModalTitle = computed(() => {
    const count = selectedItem.value?.products_count ?? 0;
    if (count > 0) {
        return `Delete Product Type (${count} Products)`;
    }
    return 'Delete Product Type';
});

const deleteModalDescription = computed(() => {
    if (!selectedItem.value) return '';
    const count = selectedItem.value.products_count ?? 0;
    if (count > 0) {
        return `Warning: "${selectedItem.value.name}" has ${count} product(s) associated with it. Deleting this type will unassign these products from this type. This action will move it to trash.`;
    }
    return `Are you sure you want to delete "${selectedItem.value.name}"? This will move it to trash.`;
});

const hasProducts = computed(() => (selectedItem.value?.products_count ?? 0) > 0);

// Handlers
const handleCreate = () => router.visit('/dashboard/product-types/create');

const handleTrash = () => router.visit('/dashboard/product-types/trash');

const applyFilters = (overrides: { page?: number; per_page?: number } = {}) => {
    router.get('/dashboard/product-types', {
        search: searchQuery.value || undefined,
        is_active: statusFilter.value !== 'all' ? statusFilter.value : undefined,
        outlet_id: outletFilter.value !== 'all' ? outletFilter.value : undefined,
        ...overrides,
    }, { preserveState: true });
};

const handlePageChange = (page: number) => {
    applyFilters({ page, per_page: pagination.value.per_page });
};

const handlePerPageChange = (perPage: number) => {
    applyFilters({ page: 1, per_page: perPage });
};

const handleSearch = (search: string) => {
    searchQuery.value = search;
    applyFilters({ page: 1 });
};

const handleStatusFilter = (value: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    statusFilter.value = String(value || 'all');
    applyFilters({ page: 1 });
};

const handleOutletFilter = (value: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    outletFilter.value = String(value || 'all');
    applyFilters({ page: 1 });
};

const handleDelete = () => {
    if (!selectedItem.value) return;
    isDeleting.value = true;
    router.delete(`/dashboard/product-types/${selectedItem.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedItem.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const openBulkDeleteDialog = () => {
    const params = new URLSearchParams();
    selectedUuids.value.forEach((uuid) => {
        params.append('uuids[]', String(uuid));
    });
    router.visit(`/dashboard/product-types/bulk-delete?${params.toString()}`);
};

const handleRowClick = (item: ProductTypeItem) => {
    router.visit(`/dashboard/product-types/${item.id}`);
};
</script>

<template>
    <Head title="Product Types" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Product Types</h1>
                <p class="text-muted-foreground">Manage your product categories and types</p>
            </div>
            <div class="flex items-center gap-2">
                <ButtonGroup>
                    <Button variant="default">
                        <Database class="mr-2 h-4 w-4" />
                        All
                    </Button>
                    <Button variant="outline" @click="handleTrash">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Trash
                    </Button>
                </ButtonGroup>
                <Button @click="handleCreate">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Product Type
                </Button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <StatsCard
                title="Total Types"
                :value="stats.total"
                :icon="PackageSearch"
                icon-color="text-blue-500"
            />
            <StatsCard
                title="Active"
                :value="stats.active"
                :icon="CheckCircle"
                icon-color="text-green-500"
                value-color="text-green-600"
            />
            <StatsCard
                title="Inactive"
                :value="stats.inactive"
                :icon="XCircle"
                icon-color="text-gray-500"
                value-color="text-gray-600"
            />
        </div>

        <!-- Main Content -->
        <div>
            <CardHeader>
                <CardTitle>Product Types List</CardTitle>
                <CardDescription>Click on a row to view details</CardDescription>
            </CardHeader>
            <CardContent>
                <TableReusable
                    v-model:selected="selectedUuids"
                    v-model:search-query="searchQuery"
                    :data="props.productTypes.data"
                    :columns="columns"
                    :actions="tableActions"
                    :pagination="pagination"
                    :selectable="true"
                    select-key="uuid"
                    search-placeholder="Search product types..."
                    empty-message="No product types found."
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                    @search="handleSearch"
                    @row-click="handleRowClick"
                >
                    <!-- Bulk Actions -->
                    <template #bulk-actions>
                        <Button variant="destructive" size="sm" @click="openBulkDeleteDialog">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete Selected
                        </Button>
                    </template>

                    <!-- Toolbar slot for filters -->
                    <template #toolbar>
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Status Filter -->
                            <Select :model-value="statusFilter" @update:model-value="handleStatusFilter">
                                <SelectTrigger class="w-[150px]">
                                    <SelectValue placeholder="All Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="true">Active</SelectItem>
                                    <SelectItem value="false">Inactive</SelectItem>
                                </SelectContent>
                            </Select>

                            <!-- Outlet Filter -->
                            <Select v-if="props.outlets && props.outlets.length > 0" :model-value="outletFilter" @update:model-value="handleOutletFilter">
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
                        </div>
                    </template>

                    <!-- Custom cell slots -->
                    <template #cell-name="{ item }">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10">
                                <PackageSearch class="h-4 w-4 text-primary" />
                            </div>
                            <div>
                                <div class="font-medium">{{ item.name }}</div>
                                <div v-if="item.slug" class="text-xs text-muted-foreground">{{ item.slug }}</div>
                            </div>
                        </div>
                    </template>

                    <template #cell-products_count="{ item }">
                        <Badge
                            :variant="(item.products_count ?? 0) > 0 ? 'default' : 'outline'"
                            class="cursor-pointer tabular-nums transition-colors hover:bg-primary/80"
                            @click.stop="router.visit(`/dashboard/products?product_type_id=${item.id}`)"
                        >
                            {{ item.products_count ?? 0 }}
                        </Badge>
                    </template>

                    <template #cell-description="{ item }">
                        <span class="line-clamp-2 text-sm text-muted-foreground">
                            {{ item.description || '-' }}
                        </span>
                    </template>

                    <template #cell-outlet="{ item }">
                        <Badge
                            v-if="item.outlet"
                            variant="secondary"
                            class="cursor-pointer transition-colors hover:bg-secondary/80"
                            @click.stop="router.visit(`/dashboard/products?outlet_id=${item.outlet.id}`)"
                        >
                            {{ item.outlet.name }}
                        </Badge>
                        <span v-else class="text-muted-foreground">-</span>
                    </template>

                    <template #cell-is_active="{ item }">
                        <Badge :variant="item.is_active ? 'default' : 'secondary'">
                            {{ item.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </template>

                    <template #cell-sort_order="{ item }">
                        <Badge variant="outline" class="tabular-nums">{{ item.sort_order }}</Badge>
                    </template>
                </TableReusable>
            </CardContent>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ModalConfirm
        v-model:open="isDeleteModalOpen"
        :title="deleteModalTitle"
        :description="deleteModalDescription"
        confirm-text="Delete"
        variant="danger"
        :loading="isDeleting"
        @confirm="handleDelete"
    >
        <template v-if="hasProducts" #default>
            <div class="flex items-start gap-3 rounded-lg border border-yellow-500/50 bg-yellow-500/10 p-3 mt-4">
                <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-yellow-500" />
                <div class="text-sm">
                    <p class="font-medium text-yellow-700 dark:text-yellow-400">
                        This type has {{ selectedItem?.products_count }} products
                    </p>
                    <p class="text-muted-foreground mt-1">
                        Products will have their type unassigned but won't be deleted.
                    </p>
                </div>
            </div>
        </template>
    </ModalConfirm>
</template>
