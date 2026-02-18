<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Plus,
    Pencil,
    Trash2,
    ChevronLeft,
    ToggleLeft,
    ToggleRight,
    Package,
    CircleDot,
    CircleDotDashed,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ModalConfirm, StatsCard } from '@/components/shared';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import type { ProductAddOn, ProductAddOnIndexProps } from '../../../types';

const props = defineProps<ProductAddOnIndexProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: props.product.name, href: `/dashboard/products/${props.product.id}` },
    { title: 'Add-ons', href: `/dashboard/products/${props.product.id}/addons` },
];

// Delete modal state
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedAddOn = ref<ProductAddOn | null>(null);

const openDeleteModal = (addOn: ProductAddOn) => {
    selectedAddOn.value = addOn;
    isDeleteModalOpen.value = true;
};

const handleDelete = () => {
    if (!selectedAddOn.value) return;
    isDeleting.value = true;
    router.delete(
        `/dashboard/products/${props.product.id}/addons/${selectedAddOn.value.id}`,
        {
            onSuccess: () => {
                isDeleteModalOpen.value = false;
                selectedAddOn.value = null;
            },
            onFinish: () => {
                isDeleting.value = false;
            },
        }
    );
};

const toggleStatus = (addOn: ProductAddOn) => {
    router.patch(`/dashboard/products/${props.product.id}/addons/${addOn.id}/toggle-status`);
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
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Add-ons - ${product.name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="`/dashboard/products/${product.id}`">
                            <ChevronLeft class="h-5 w-5" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Product Add-ons
                        </h1>
                        <p class="text-muted-foreground">
                            Manage add-on products for
                            <span class="font-medium">{{ product.name }}</span>
                        </p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="`/dashboard/products/${product.id}/addons/create`">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Add-on
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
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
            </div>

            <!-- Add-ons Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Add-ons</CardTitle>
                    <CardDescription>
                        Products that can be added with {{ product.name }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="addOns.length === 0" class="py-12 text-center text-muted-foreground">
                        <Package class="mx-auto h-12 w-12 mb-4 opacity-50" />
                        <p>No add-ons configured yet</p>
                        <Button class="mt-4" as-child>
                            <Link :href="`/dashboard/products/${product.id}/addons/create`">
                                <Plus class="mr-2 h-4 w-4" />
                                Add First Add-on
                            </Link>
                        </Button>
                    </div>

                    <Table v-else>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Product</TableHead>
                                <TableHead>Price</TableHead>
                                <TableHead>Adjustment</TableHead>
                                <TableHead>Max Qty</TableHead>
                                <TableHead>Required</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="addOn in addOns"
                                :key="addOn.id"
                                :class="{ 'opacity-50': !addOn.is_active }"
                            >
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="font-medium">
                                            {{ addOn.add_on_product?.name }}
                                        </span>
                                        <span class="text-xs text-muted-foreground">
                                            SKU: {{ addOn.add_on_product?.sku || 'N/A' }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    {{ formatCurrency(addOn.add_on_product?.effective_price || 0) }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="addOn.price_adjustment === 0 ? 'secondary' : addOn.price_adjustment > 0 ? 'default' : 'outline'"
                                    >
                                        {{ formatPriceAdjustment(addOn.price_adjustment) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ addOn.max_quantity }}</TableCell>
                                <TableCell>
                                    <Badge :variant="addOn.is_required ? 'destructive' : 'secondary'">
                                        {{ addOn.is_required ? 'Required' : 'Optional' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="addOn.is_active ? 'default' : 'secondary'">
                                        {{ addOn.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8"
                                            @click="toggleStatus(addOn)"
                                        >
                                            <ToggleRight v-if="addOn.is_active" class="h-4 w-4 text-green-600" />
                                            <ToggleLeft v-else class="h-4 w-4 text-muted-foreground" />
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                            <Link :href="`/dashboard/products/${product.id}/addons/${addOn.id}/edit`">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-destructive"
                                            @click="openDeleteModal(addOn)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirmation Modal -->
        <ModalConfirm
            v-model:open="isDeleteModalOpen"
            title="Remove Add-on"
            :description="`Are you sure you want to remove '${selectedAddOn?.add_on_product?.name}' from add-ons?`"
            confirm-text="Remove"
            variant="danger"
            :loading="isDeleting"
            @confirm="handleDelete"
        />
    </AppLayout>
</template>
