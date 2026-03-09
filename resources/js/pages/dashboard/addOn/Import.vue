<script setup lang="ts">
import ImportPage, { type TableColumn } from '@/components/shared/ImportPage.vue';
import type { BreadcrumbItem } from '@/types';
import type { DuplicateOption } from '@/composables/useImport';

defineProps<{
    duplicateOptions: DuplicateOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Add-ons', href: '/dashboard/products/addons' },
    { title: 'Import', href: '/dashboard/products/addons/import' },
];

const previewColumns: TableColumn[] = [
    { key: 'name', label: 'Name', minWidth: '180px' },
    { key: 'product_sku', label: 'Product SKU', minWidth: '120px' },
    { key: 'price_adjustment', label: 'Price', minWidth: '100px' },
    { key: 'max_quantity', label: 'Max Qty', minWidth: '80px' },
    { key: 'status', label: 'Status', minWidth: '100px' },
];

const availableColumns = [
    'Name *',
    'Product SKU *',
    'Description',
    'Price Adjustment *',
    'Max Quantity',
    'Sort Order',
    'Is Required',
    'Is Active',
    'Add-on Product SKU',
];
</script>

<template>
    <ImportPage
        title="Import Add-ons"
        entity-name="add-on"
        entity-name-plural="Add-ons"
        :breadcrumbs="breadcrumbs"
        :duplicate-options="duplicateOptions"
        preview-url="/dashboard/products/addons/import/preview"
        import-url="/dashboard/products/addons/import"
        list-url="/dashboard/products/addons"
        template-url="/dashboard/products/addons/template"
        required-fields-text="Name, Product SKU, and Price Adjustment are required. Name + Product SKU combination is used for duplicate detection."
        :available-columns="availableColumns"
        :preview-columns="previewColumns"
    />
</template>
