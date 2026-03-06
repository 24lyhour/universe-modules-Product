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
    Database,
    Download,
    X,
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
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
const statusFilter = ref(props.filters.is_active ?? 'all');

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

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return !!(
        searchQuery.value ||
        typeFilter.value ||
        (statusFilter.value !== 'all' && statusFilter.value !== '')
    );
});

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
            toast.success('Attribute moved to trash');
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const handleStatusToggle = (item: ProductAttribute, newValue: boolean) => {
    router.patch(`/dashboard/products/attributes/${item.id}/toggle-status`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Status changed to ${newValue ? 'Active' : 'Inactive'}`);
        },
    });
};

const openBulkDeleteDialog = () => {
    const params = new URLSearchParams();
    selectedUuids.value.forEach((uuid) => {
        params.append('uuids[]', String(uuid));
    });
    router.visit(`/dashboard/products/attributes/bulk-delete?${params.toString()}`);
};

const handleTrash = () => {
    router.visit('/dashboard/products/attributes/trash');
};

const handleExport = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.append('search', searchQuery.value);
    if (typeFilter.value) params.append('type', typeFilter.value);
    if (statusFilter.value && statusFilter.value !== 'all') params.append('is_active', String(statusFilter.value));
    window.location.href = `/dashboard/products/attributes/export?${params.toString()}`;
};

const handlePageChange = (page: number) => {
    router.get('/dashboard/products/attributes', {
        page,
        per_page: pagination.value.per_page,
        search: searchQuery.value,
        type: typeFilter.value || undefined,
        is_active: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/products/attributes', {
        page: 1,
        per_page: perPage,
        search: searchQuery.value,
        type: typeFilter.value || undefined,
        is_active: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (search: string) => {
    searchQuery.value = search;
    router.get('/dashboard/products/attributes', {
        search,
        type: typeFilter.value || undefined,
        is_active: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleTypeFilter = (type: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    const typeStr = String(type || 'all');
    typeFilter.value = typeStr === 'all' ? '' : typeStr;
    router.get('/dashboard/products/attributes', {
        search: searchQuery.value,
        type: typeStr === 'all' ? undefined : typeStr,
        is_active: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleStatusFilter = (value: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    const status = String(value ?? 'all');
    statusFilter.value = status;
    router.get('/dashboard/products/attributes', {
        search: searchQuery.value,
        type: typeFilter.value || undefined,
        is_active: status !== 'all' ? status : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleClearFilters = () => {
    searchQuery.value = '';
    typeFilter.value = '';
    statusFilter.value = 'all';
    router.get('/dashboard/products/attributes', {}, { preserveState: true, preserveScroll: true });
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
                    <Button variant="outline" @click="handleExport">
                        <Download class="mr-2 h-4 w-4" />
                        Export
                    </Button>
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
            <div class="grid gap-4 md:grid-cols-4">
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
                <StatsCard
                    title="In Trash"
                    :value="stats.trashed ?? 0"
                    :icon="Trash2"
                    variant="destructive"
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
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Type Filter -->
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

                        <!-- Status Filter -->
                        <Select :model-value="String(statusFilter)" @update:model-value="handleStatusFilter">
                            <SelectTrigger class="w-[150px]">
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="true">Active</SelectItem>
                                <SelectItem value="false">Inactive</SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Clear Filters Button -->
                        <Button
                            v-if="hasActiveFilters"
                            variant="ghost"
                            size="sm"
                            @click="handleClearFilters"
                            class="text-muted-foreground hover:text-foreground"
                        >
                            <X class="mr-1 h-4 w-4" />
                            Clear Filters
                        </Button>
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

                <!-- Custom cell for status with switch toggle -->
                <template #cell-is_active="{ item }">
                    <div class="flex items-center gap-2" @click.stop>
                        <Switch
                            :model-value="item.is_active"
                            @update:model-value="handleStatusToggle(item, $event)"
                        />
                        <span class="text-sm text-muted-foreground">
                            {{ item.is_active ? 'Active' : 'Inactive' }}
                        </span>
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
            title="Move to Trash"
            :description="`Are you sure you want to move '${selectedAttribute?.name}' to trash?`"
            confirm-text="Move to Trash"
            variant="danger"
            :loading="isDeleting"
            @confirm="handleDelete"
        />
    </AppLayout>
</template>
