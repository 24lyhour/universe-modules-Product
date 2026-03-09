<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Plus,
    Pencil,
    Trash2,
    Package,
    CircleDot,
    CircleDotDashed,
    ExternalLink,
    Settings,
    Database,
    Download,
    Upload,
    FileSpreadsheet,
    X,
    ToggleRight,
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
import type { ProductAddOnWithProduct, ProductAddOnIndexAllProps } from '../../../types';

const props = defineProps<ProductAddOnIndexAllProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: 'Add-ons', href: '/dashboard/products/addons' },
];

// Search and filters
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

// Selection
const selectedUuids = ref<(string | number)[]>([]);

// Delete modal state
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedAddOn = ref<ProductAddOnWithProduct | null>(null);

// Pagination
const pagination = computed(() => ({
    current_page: props.addOns.meta.current_page,
    last_page: props.addOns.meta.last_page,
    per_page: props.addOns.meta.per_page,
    total: props.addOns.meta.total,
}));

// Table columns
const columns: TableColumn<ProductAddOnWithProduct>[] = [
    { key: 'product', label: 'Parent Product', width: '20%' },
    { key: 'name', label: 'Add-on Name', width: '20%' },
    { key: 'price_adjustment', label: 'Price' },
    { key: 'max_quantity', label: 'Max Qty', align: 'center' },
    { key: 'is_required', label: 'Required' },
    { key: 'is_active', label: 'Status' },
    { key: 'created_at', label: 'Created' },
];

// Table actions
const tableActions: TableAction<ProductAddOnWithProduct>[] = [
    {
        label: 'Add Add-on',
        icon: Plus,
        onClick: (item) => router.visit(`/dashboard/products/${item.product_id}/addons/create`),
    },
    {
        label: 'Manage Add-ons',
        icon: Settings,
        onClick: (item) => router.visit(`/dashboard/products/${item.product_id}/addons`),
    },
    {
        label: 'View Product',
        icon: ExternalLink,
        onClick: (item) => router.visit(`/dashboard/products/${item.product_id}`),
    },
    {
        label: 'Edit',
        icon: Pencil,
        onClick: (item) => router.visit(`/dashboard/products/${item.product_id}/addons/${item.id}/edit`),
    },
    {
        label: 'Delete',
        icon: Trash2,
        onClick: (item) => openDeleteModal(item),
        variant: 'destructive',
        separator: true,
    },
];

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return !!(searchQuery.value || (statusFilter.value && statusFilter.value !== 'all'));
});

// Handlers
const openDeleteModal = (addOn: ProductAddOnWithProduct) => {
    selectedAddOn.value = addOn;
    isDeleteModalOpen.value = true;
};

const handleDelete = () => {
    if (!selectedAddOn.value) return;
    isDeleting.value = true;
    router.delete(
        `/dashboard/products/addons/${selectedAddOn.value.id}/delete`,
        {
            onSuccess: () => {
                isDeleteModalOpen.value = false;
                selectedAddOn.value = null;
                toast.success('Add-on moved to trash');
            },
            onFinish: () => {
                isDeleting.value = false;
            },
        }
    );
};

const handleStatusToggle = (item: ProductAddOnWithProduct, newValue: boolean) => {
    router.patch(`/dashboard/products/${item.product_id}/addons/${item.id}/toggle-status`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Status changed to ${newValue ? 'Active' : 'Inactive'}`);
        },
    });
};

const openBulkDeleteDialog = () => {
    router.delete('/dashboard/products/addons/bulk-delete', {
        data: { uuids: selectedUuids.value },
        onSuccess: () => {
            selectedUuids.value = [];
            toast.success('Selected add-ons moved to trash');
        },
    });
};

const handleTrash = () => {
    router.visit('/dashboard/products/addons/trash');
};

const handleImport = () => {
    router.visit('/dashboard/products/addons/import');
};

const handleExport = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.append('search', searchQuery.value);
    if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value);
    window.location.href = `/dashboard/products/addons/export?${params.toString()}`;
};

const handleTemplate = () => {
    window.location.href = '/dashboard/products/addons/template';
};

const handlePageChange = (page: number) => {
    router.get('/dashboard/products/addons', {
        page,
        per_page: pagination.value.per_page,
        search: searchQuery.value,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/products/addons', {
        page: 1,
        per_page: perPage,
        search: searchQuery.value,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (search: string) => {
    searchQuery.value = search;
    router.get('/dashboard/products/addons', {
        search,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleStatusFilter = (value: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    const status = String(value || 'all');
    statusFilter.value = status;
    router.get('/dashboard/products/addons', {
        search: searchQuery.value,
        status: status !== 'all' ? status : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleClearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = 'all';
    router.get('/dashboard/products/addons', {}, { preserveState: true, preserveScroll: true });
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const formatPriceAdjustment = (value: number) => {
    if (value === 0) return 'No adjustment';
    const prefix = value > 0 ? '+' : '';
    return prefix + formatCurrency(value);
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
        <Head title="All Product Add-ons" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Product Add-ons</h1>
                    <p class="text-muted-foreground">Manage all product add-ons across your catalog</p>
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
                    <ButtonGroup>
                        <Button variant="outline" @click="handleImport">
                            <Upload class="mr-2 h-4 w-4" />
                            Import
                        </Button>
                        <Button variant="outline" @click="handleExport">
                            <Download class="mr-2 h-4 w-4" />
                            Export
                        </Button>
                        <Button variant="outline" @click="handleTemplate">
                            <FileSpreadsheet class="mr-2 h-4 w-4" />
                            Template
                        </Button>
                    </ButtonGroup>
                    <Button as-child>
                        <Link href="/dashboard/products/addons/create">
                            <Plus class="mr-2 h-4 w-4" />
                            Create Add-on
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-5">
                <StatsCard title="Total" :value="stats.total" :icon="Package" />
                <StatsCard
                    title="Required"
                    :value="stats.required"
                    :icon="CircleDot"
                    variant="warning"
                />
                <StatsCard
                    title="Optional"
                    :value="stats.optional"
                    :icon="CircleDotDashed"
                    variant="info"
                />
                <StatsCard
                    title="Active"
                    :value="stats.active"
                    :icon="ToggleRight"
                    variant="success"
                />
                <StatsCard
                    title="In Trash"
                    :value="stats.trashed ?? 0"
                    :icon="Trash2"
                    variant="destructive"
                />
            </div>

            <!-- Empty State -->
            <div v-if="props.addOns.data.length === 0 && !hasActiveFilters" class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12 text-center">
                <Package class="h-12 w-12 text-muted-foreground/50 mb-4" />
                <h3 class="text-lg font-semibold">No add-ons yet</h3>
                <p class="text-muted-foreground mt-1 mb-4 max-w-md">
                    Create add-ons like extra sauce, gift wrapping, or extended warranty.
                </p>
                <Button as-child>
                    <Link href="/dashboard/products/addons/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Create Add-on
                    </Link>
                </Button>
            </div>

            <!-- Table -->
            <TableReusable
                v-else
                v-model:selected="selectedUuids"
                :data="props.addOns.data"
                :columns="columns"
                :actions="tableActions"
                :pagination="pagination"
                :searchable="true"
                :selectable="true"
                select-key="uuid"
                search-placeholder="Search by add-on name..."
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

                <!-- Custom cell for parent product -->
                <template #cell-product="{ item }">
                    <div class="flex flex-col">
                        <Link
                            :href="`/dashboard/products/${item.product_id}`"
                            class="font-medium hover:underline"
                        >
                            {{ item.product?.name || 'Unknown' }}
                        </Link>
                        <span class="text-xs text-muted-foreground">
                            SKU: {{ item.product?.sku || 'N/A' }}
                        </span>
                    </div>
                </template>

                <!-- Custom cell for add-on name -->
                <template #cell-name="{ item }">
                    <div class="flex flex-col">
                        <span class="font-medium">
                            {{ item.name }}
                        </span>
                        <span v-if="item.description" class="text-xs text-muted-foreground line-clamp-1">
                            {{ item.description }}
                        </span>
                    </div>
                </template>

                <!-- Custom cell for price adjustment -->
                <template #cell-price_adjustment="{ item }">
                    <Badge
                        :variant="item.price_adjustment === 0 ? 'secondary' : item.price_adjustment > 0 ? 'default' : 'outline'"
                    >
                        {{ formatPriceAdjustment(item.price_adjustment) }}
                    </Badge>
                </template>

                <!-- Custom cell for max quantity -->
                <template #cell-max_quantity="{ item }">
                    <span class="text-muted-foreground">{{ item.max_quantity }}</span>
                </template>

                <!-- Custom cell for required -->
                <template #cell-is_required="{ item }">
                    <Badge :variant="item.is_required ? 'destructive' : 'secondary'">
                        {{ item.is_required ? 'Required' : 'Optional' }}
                    </Badge>
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
            :description="`Are you sure you want to move '${selectedAddOn?.name}' to trash?`"
            confirm-text="Move to Trash"
            variant="danger"
            :loading="isDeleting"
            @confirm="handleDelete"
        />
    </AppLayout>
</template>
