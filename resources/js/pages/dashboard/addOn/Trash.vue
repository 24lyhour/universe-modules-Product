<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    RotateCcw,
    Trash2,
    Package,
    Database,
    AlertTriangle,
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
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
    { title: 'Trash', href: '/dashboard/products/addons/trash' },
];

// Search
const searchQuery = ref(props.filters.search || '');

// Selection
const selectedUuids = ref<(string | number)[]>([]);

// Modal states
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedAddOn = ref<ProductAddOnWithProduct | null>(null);
const isEmptyTrashModalOpen = ref(false);
const isEmptyingTrash = ref(false);

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
    { key: 'is_required', label: 'Required' },
    { key: 'is_active', label: 'Status' },
    { key: 'deleted_at', label: 'Deleted At' },
];

// Table actions
const tableActions: TableAction<ProductAddOnWithProduct>[] = [
    {
        label: 'Restore',
        icon: RotateCcw,
        onClick: (item) => handleRestore(item),
    },
    {
        label: 'Delete Permanently',
        icon: Trash2,
        onClick: (item) => openDeleteModal(item),
        variant: 'destructive',
        separator: true,
    },
];

// Handlers
const openDeleteModal = (addOn: ProductAddOnWithProduct) => {
    selectedAddOn.value = addOn;
    isDeleteModalOpen.value = true;
};

const handleRestore = (addOn: ProductAddOnWithProduct) => {
    router.put(`/dashboard/products/addons/${addOn.uuid}/restore`, {}, {
        onSuccess: () => {
            toast.success('Add-on restored successfully');
        },
    });
};

const handleForceDelete = () => {
    if (!selectedAddOn.value) return;
    isDeleting.value = true;
    router.delete(`/dashboard/products/addons/${selectedAddOn.value.uuid}/force-delete`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedAddOn.value = null;
            toast.success('Add-on permanently deleted');
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const openBulkRestoreDialog = () => {
    router.put('/dashboard/products/addons/trash/bulk-restore', {
        uuids: selectedUuids.value,
    }, {
        onSuccess: () => {
            selectedUuids.value = [];
            toast.success('Selected add-ons restored successfully');
        },
    });
};

const openBulkDeleteDialog = () => {
    router.delete('/dashboard/products/addons/trash/bulk-force-delete', {
        data: { uuids: selectedUuids.value },
        onSuccess: () => {
            selectedUuids.value = [];
            toast.success('Selected add-ons permanently deleted');
        },
    });
};

const handleEmptyTrash = () => {
    isEmptyingTrash.value = true;
    router.delete('/dashboard/products/addons/trash/empty', {
        onSuccess: () => {
            isEmptyTrashModalOpen.value = false;
            toast.success('Trash emptied successfully');
        },
        onFinish: () => {
            isEmptyingTrash.value = false;
        },
    });
};

const handlePageChange = (page: number) => {
    router.get('/dashboard/products/addons/trash', {
        page,
        per_page: pagination.value.per_page,
        search: searchQuery.value,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/products/addons/trash', {
        page: 1,
        per_page: perPage,
        search: searchQuery.value,
    }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (search: string) => {
    searchQuery.value = search;
    router.get('/dashboard/products/addons/trash', {
        search,
    }, { preserveState: true, preserveScroll: true });
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
        <Head title="Trashed Add-ons" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Trashed Add-ons</h1>
                    <p class="text-muted-foreground">Manage deleted add-ons</p>
                </div>
                <div class="flex items-center gap-2">
                    <ButtonGroup>
                        <Button variant="outline" as-child>
                            <Link href="/dashboard/products/addons">
                                <Database class="mr-2 h-4 w-4" />
                                All
                            </Link>
                        </Button>
                        <Button variant="default">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Trash
                        </Button>
                    </ButtonGroup>
                    <Button
                        v-if="props.addOns.data.length > 0"
                        variant="destructive"
                        @click="isEmptyTrashModalOpen = true"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Empty Trash
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2">
                <StatsCard
                    title="In Trash"
                    :value="stats.trashed ?? 0"
                    :icon="Trash2"
                    variant="destructive"
                />
                <StatsCard
                    title="Total Active"
                    :value="stats.total"
                    :icon="Package"
                />
            </div>

            <!-- Empty State -->
            <div v-if="props.addOns.data.length === 0" class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12 text-center">
                <Trash2 class="h-12 w-12 text-muted-foreground/50 mb-4" />
                <h3 class="text-lg font-semibold">Trash is empty</h3>
                <p class="text-muted-foreground mt-1 mb-4 max-w-md">
                    Deleted add-ons will appear here.
                </p>
                <Button variant="outline" as-child>
                    <Link href="/dashboard/products/addons">
                        <Database class="mr-2 h-4 w-4" />
                        Back to Add-ons
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
                search-placeholder="Search trashed add-ons..."
                @page-change="handlePageChange"
                @per-page-change="handlePerPageChange"
                @search="handleSearch"
            >
                <!-- Bulk Actions -->
                <template #bulk-actions>
                    <Button variant="outline" size="sm" @click="openBulkRestoreDialog">
                        <RotateCcw class="mr-2 h-4 w-4" />
                        Restore Selected
                    </Button>
                    <Button variant="destructive" size="sm" @click="openBulkDeleteDialog">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete Permanently
                    </Button>
                </template>

                <!-- Custom cell for parent product -->
                <template #cell-product="{ item }">
                    <div class="flex flex-col">
                        <span class="font-medium">
                            {{ item.product?.name || 'Unknown' }}
                        </span>
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

                <!-- Custom cell for required -->
                <template #cell-is_required="{ item }">
                    <Badge :variant="item.is_required ? 'destructive' : 'secondary'">
                        {{ item.is_required ? 'Required' : 'Optional' }}
                    </Badge>
                </template>

                <!-- Custom cell for status -->
                <template #cell-is_active="{ item }">
                    <Badge :variant="item.is_active ? 'default' : 'secondary'">
                        {{ item.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                </template>

                <!-- Custom cell for deleted date -->
                <template #cell-deleted_at="{ item }">
                    <span class="text-sm text-muted-foreground">
                        {{ item.deleted_at ? formatDate(item.deleted_at) : '-' }}
                    </span>
                </template>
            </TableReusable>
        </div>

        <!-- Force Delete Confirmation Modal -->
        <ModalConfirm
            v-model:open="isDeleteModalOpen"
            title="Delete Permanently"
            :description="`Are you sure you want to permanently delete '${selectedAddOn?.name}'? This action cannot be undone.`"
            confirm-text="Delete Permanently"
            variant="danger"
            :loading="isDeleting"
            @confirm="handleForceDelete"
        >
            <template #default>
                <div class="flex items-start gap-3 rounded-lg border border-red-500/50 bg-red-500/10 p-3 mt-4">
                    <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-red-500" />
                    <div class="text-sm">
                        <p class="font-medium text-red-700 dark:text-red-400">
                            This action is irreversible
                        </p>
                        <p class="text-muted-foreground mt-1">
                            The add-on will be permanently removed from the database.
                        </p>
                    </div>
                </div>
            </template>
        </ModalConfirm>

        <!-- Empty Trash Confirmation Modal -->
        <ModalConfirm
            v-model:open="isEmptyTrashModalOpen"
            title="Empty Trash"
            description="Are you sure you want to permanently delete all trashed add-ons? This action cannot be undone."
            confirm-text="Empty Trash"
            variant="danger"
            :loading="isEmptyingTrash"
            @confirm="handleEmptyTrash"
        >
            <template #default>
                <div class="flex items-start gap-3 rounded-lg border border-red-500/50 bg-red-500/10 p-3 mt-4">
                    <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-red-500" />
                    <div class="text-sm">
                        <p class="font-medium text-red-700 dark:text-red-400">
                            This will delete {{ stats.trashed ?? 0 }} add-on(s)
                        </p>
                        <p class="text-muted-foreground mt-1">
                            All trashed add-ons will be permanently removed.
                        </p>
                    </div>
                </div>
            </template>
        </ModalConfirm>
    </AppLayout>
</template>
