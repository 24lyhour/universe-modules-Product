<script setup lang="ts">
import ImportPage, { type TableColumn } from '@/components/shared/ImportPage.vue';
import type { BreadcrumbItem } from '@/types';
import type { DuplicateOption } from '@/composables/useImport';

defineProps<{
    duplicateOptions: DuplicateOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: 'Import', href: '/dashboard/products/import' },
];

const previewColumns: TableColumn[] = [
    { key: 'name', label: 'Name', minWidth: '200px' },
    { key: 'sku', label: 'SKU', minWidth: '120px' },
    { key: 'price', label: 'Price', minWidth: '100px' },
    { key: 'stock', label: 'Stock', minWidth: '80px' },
    { key: 'status', label: 'Status', minWidth: '100px' },
];

const availableColumns = [
    'Name *',
    'SKU',
    'Description',
    'Price *',
    'Purchase Price',
    'Sale Price',
    'Stock',
    'Low Stock Threshold',
    'Status',
];
</script>

<template>
    <ImportPage
        title="Import Products"
        entity-name="product"
        entity-name-plural="Products"
        :breadcrumbs="breadcrumbs"
        :duplicate-options="duplicateOptions"
        preview-url="/dashboard/products/import/preview"
        import-url="/dashboard/products/import"
        list-url="/dashboard/products"
        template-url="/dashboard/products/template"
        required-fields-text="Name and Price are required. SKU is used for duplicate detection."
        :available-columns="availableColumns"
        :preview-columns="previewColumns"
    />
</template>
