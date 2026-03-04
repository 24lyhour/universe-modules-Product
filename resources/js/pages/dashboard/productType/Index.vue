<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, type VNode } from 'vue';
import { Plus, Pencil, Trash2, Package, CheckCircle, XCircle } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    TableReusable,
    ModalConfirm,
    StatsCard,
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
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedItem = ref<ProductTypeItem | null>(null);

// Pagination computed (transform meta to top-level for TableReusable)
const pagination = computed(() => ({
    current_page: props.productTypes.meta.current_page,
    last_page: props.productTypes.meta.last_page,
    per_page: props.productTypes.meta.per_page,
    total: props.productTypes.meta.total,
}));

// Client-side filtering
const filteredData = computed(() => {
    if (!searchQuery.value) return props.productTypes.data;
    const query = searchQuery.value.toLowerCase();
    return props.productTypes.data.filter(item =>
        item.name.toLowerCase().includes(query) ||
        (item.description?.toLowerCase().includes(query) ?? false)
    );
});

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

// Handlers
const handleCreate = () => router.visit('/dashboard/product-types/create');

const handlePageChange = (page: number) => {
    router.get('/dashboard/product-types', { page, search: searchQuery.value }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/product-types', { per_page: perPage, search: searchQuery.value }, { preserveState: true });
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
</script>

<template>
    <Head title="Product Types" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <StatsCard
                title="Total Types"
                :value="stats.total"
                :icon="Package"
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
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle>Product Types</CardTitle>
                        <CardDescription>Manage your product categories and types</CardDescription>
                    </div>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Product Type
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <TableReusable
                    v-model:search-query="searchQuery"
                    :data="filteredData"
                    :columns="columns"
                    :actions="tableActions"
                    :pagination="pagination"
                    search-placeholder="Search product types..."
                    empty-message="No product types found."
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                >
                    <!-- Custom cell slots -->
                    <template #cell-name="{ item }">
                        <div class="font-medium">{{ item.name }}</div>
                        <div v-if="item.slug" class="text-xs text-muted-foreground">{{ item.slug }}</div>
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
                        <span v-if="item.outlet">{{ item.outlet.name }}</span>
                        <span v-else class="text-muted-foreground">-</span>
                    </template>

                    <template #cell-is_active="{ item }">
                        <Badge :variant="item.is_active ? 'default' : 'secondary'">
                            {{ item.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </template>

                    <template #cell-sort_order="{ item }">
                        <span class="text-muted-foreground">{{ item.sort_order }}</span>
                    </template>
                </TableReusable>
            </CardContent>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ModalConfirm
        v-model:open="isDeleteModalOpen"
        title="Delete Product Type"
        :description="`Are you sure you want to delete '${selectedItem?.name}'? This action cannot be undone.`"
        confirm-text="Delete"
        variant="danger"
        :loading="isDeleting"
        @confirm="handleDelete"
    />
</template>
