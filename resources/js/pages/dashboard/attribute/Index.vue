<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Plus,
    Eye,
    Pencil,
    Trash2,
    Tags,
    ToggleLeft,
    ToggleRight,
    Settings,
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
import type { ProductAttribute, ProductAttributeIndexProps } from '../../../types';

const props = defineProps<ProductAttributeIndexProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: 'Attributes', href: '/dashboard/products/attributes' },
];

// Search and filters
const searchQuery = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');

// Selection state
const selectedUuids = ref<(string | number)[]>([]);

// Delete modal state
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedAttribute = ref<ProductAttribute | null>(null);

// Pagination
const pagination = computed(() => ({
    current_page: props.attributes.meta.current_page,
    last_page: props.attributes.meta.last_page,
    per_page: props.attributes.meta.per_page,
    total: props.attributes.meta.total,
}));

// Table columns
const columns: TableColumn<ProductAttribute>[] = [
    { key: 'name', label: 'Name', width: '25%' },
    { key: 'type', label: 'Type' },
    { key: 'values_count', label: 'Values', align: 'center' },
    { key: 'sort_order', label: 'Order', align: 'center' },
    { key: 'is_active', label: 'Status' },
    { key: 'created_at', label: 'Created' },
];

// Table actions
const tableActions: TableAction<ProductAttribute>[] = [
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
        label: 'Toggle Status',
        icon: ToggleLeft,
        onClick: (item) => toggleStatus(item),
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
const handleShow = (item: ProductAttribute) => {
    router.visit(`/dashboard/products/attributes/${item.id}`);
};

const handleEdit = (item: ProductAttribute) => {
    router.visit(`/dashboard/products/attributes/${item.id}/edit`);
};

const openDeleteModal = (attribute: ProductAttribute) => {
    selectedAttribute.value = attribute;
    isDeleteModalOpen.value = true;
};

const handleDelete = () => {
    if (!selectedAttribute.value) return;
    isDeleting.value = true;
    router.delete(`/dashboard/products/attributes/${selectedAttribute.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedAttribute.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const toggleStatus = (attribute: ProductAttribute) => {
    router.patch(`/dashboard/products/attributes/${attribute.id}/toggle-status`);
};

const openBulkDeleteDialog = () => {
    const params = new URLSearchParams();
    selectedUuids.value.forEach((uuid) => {
        params.append('uuids[]', String(uuid));
    });
    router.visit(`/dashboard/products/attributes/bulk-delete?${params.toString()}`);
};

const handlePageChange = (page: number) => {
    router.get('/dashboard/products/attributes', {
        page,
        per_page: pagination.value.per_page,
        search: searchQuery.value,
        type: typeFilter.value,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/products/attributes', {
        page: 1,
        per_page: perPage,
        search: searchQuery.value,
        type: typeFilter.value,
    }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (search: string) => {
    searchQuery.value = search;
    router.get('/dashboard/products/attributes', {
        search,
        type: typeFilter.value,
    }, { preserveState: true, preserveScroll: true });
};

const handleTypeFilter = (type: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    const typeStr = String(type || 'all');
    typeFilter.value = typeStr === 'all' ? '' : typeStr;
    router.get('/dashboard/products/attributes', {
        search: searchQuery.value,
        type: typeStr === 'all' ? '' : typeStr,
    }, { preserveState: true, preserveScroll: true });
};

const getTypeVariant = (type: string) => {
    switch (type) {
        case 'select':
            return 'default';
        case 'color':
            return 'secondary';
        case 'button':
            return 'outline';
        default:
            return 'outline';
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Product Attributes" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Product Attributes</h1>
                    <p class="text-muted-foreground">Manage product variation attributes (Size, Color, etc.)</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link href="/dashboard/products/settings">
                            <Settings class="mr-2 h-4 w-4" />
                            Settings
                        </Link>
                    </Button>
                    <Button as-child>
                        <Link href="/dashboard/products/attributes/create">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Attribute
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <StatsCard
                    title="Total Attributes"
                    :value="stats.total"
                    :icon="Tags"
                />
                <StatsCard
                    title="Active"
                    :value="stats.active"
                    :icon="ToggleRight"
                    variant="success"
                />
                <StatsCard
                    title="Inactive"
                    :value="stats.inactive"
                    :icon="ToggleLeft"
                    variant="secondary"
                />
            </div>

            <!-- Table -->
            <TableReusable
                v-model:selected="selectedUuids"
                :data="props.attributes.data"
                :columns="columns"
                :actions="tableActions"
                :pagination="pagination"
                :searchable="true"
                :selectable="true"
                select-key="uuid"
                search-placeholder="Search attributes..."
                @page-change="handlePageChange"
                @per-page-change="handlePerPageChange"
                @search="handleSearch"
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
                    <div class="flex items-center gap-2">
                        <Select :model-value="typeFilter || 'all'" @update:model-value="handleTypeFilter">
                            <SelectTrigger class="w-[150px]">
                                <SelectValue placeholder="All Types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Types</SelectItem>
                                <SelectItem value="select">Select</SelectItem>
                                <SelectItem value="color">Color</SelectItem>
                                <SelectItem value="button">Button</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <!-- Custom cell for name -->
                <template #cell-name="{ item }">
                    <div class="flex flex-col gap-1">
                        <span class="font-medium">{{ item.name }}</span>
                        <span v-if="item.description" class="max-w-[200px] truncate text-xs text-muted-foreground">
                            {{ item.description }}
                        </span>
                    </div>
                </template>

                <!-- Custom cell for type badge -->
                <template #cell-type="{ item }">
                    <Badge :variant="getTypeVariant(item.type)">
                        {{ item.type }}
                    </Badge>
                </template>

                <!-- Custom cell for values count -->
                <template #cell-values_count="{ item }">
                    <Badge variant="secondary" class="tabular-nums">
                        {{ item.values_count || 0 }}
                    </Badge>
                </template>

                <!-- Custom cell for sort order -->
                <template #cell-sort_order="{ item }">
                    <span class="text-muted-foreground">{{ item.sort_order }}</span>
                </template>

                <!-- Custom cell for status -->
                <template #cell-is_active="{ item }">
                    <Badge :variant="item.is_active ? 'default' : 'secondary'">
                        {{ item.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
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
            title="Delete Attribute"
            :description="`Are you sure you want to delete '${selectedAttribute?.name}'? This will also delete all associated values.`"
            confirm-text="Delete"
            variant="danger"
            :loading="isDeleting"
            @confirm="handleDelete"
        />
    </AppLayout>
</template>
