<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Plus,
    Pencil,
    Trash2,
    ArrowUpCircle,
    ArrowDownCircle,
    Shuffle,
    ChevronLeft,
    ToggleLeft,
    ToggleRight,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ModalConfirm, StatsCard } from '@/components/shared';
import { type BreadcrumbItem } from '@/types';
import type { ProductUpsell, ProductUpsellIndexProps } from '../../../types';

const props = defineProps<ProductUpsellIndexProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: props.product.name, href: `/dashboard/products/${props.product.id}` },
    { title: 'Upsells', href: `/dashboard/products/${props.product.id}/upsells` },
];

// Delete modal state
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedUpsell = ref<ProductUpsell | null>(null);

// Group upsells by type
const groupedUpsells = computed(() => ({
    upsell: props.upsells.filter((u) => u.type === 'upsell'),
    downsell: props.upsells.filter((u) => u.type === 'downsell'),
    cross_sell: props.upsells.filter((u) => u.type === 'cross_sell'),
}));

const openDeleteModal = (upsell: ProductUpsell) => {
    selectedUpsell.value = upsell;
    isDeleteModalOpen.value = true;
};

const handleDelete = () => {
    if (!selectedUpsell.value) return;
    isDeleting.value = true;
    router.delete(
        `/dashboard/products/${props.product.id}/upsells/${selectedUpsell.value.id}`,
        {
            onSuccess: () => {
                isDeleteModalOpen.value = false;
                selectedUpsell.value = null;
            },
            onFinish: () => {
                isDeleting.value = false;
            },
        }
    );
};

const toggleStatus = (upsell: ProductUpsell) => {
    router.patch(`/dashboard/products/${props.product.id}/upsells/${upsell.id}/toggle-status`);
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const getTypeIcon = (type: string) => {
    switch (type) {
        case 'upsell':
            return ArrowUpCircle;
        case 'downsell':
            return ArrowDownCircle;
        case 'cross_sell':
            return Shuffle;
        default:
            return ArrowUpCircle;
    }
};

const getTypeColor = (type: string) => {
    switch (type) {
        case 'upsell':
            return 'text-green-600 bg-green-50';
        case 'downsell':
            return 'text-orange-600 bg-orange-50';
        case 'cross_sell':
            return 'text-blue-600 bg-blue-50';
        default:
            return 'text-gray-600 bg-gray-50';
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Upsells - ${product.name}`" />

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
                            Upsells & Cross-sells
                        </h1>
                        <p class="text-muted-foreground">
                            Manage upsell, downsell, and cross-sell products for
                            <span class="font-medium">{{ product.name }}</span>
                        </p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="`/dashboard/products/${product.id}/upsells/create`">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Upsell
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-5">
                <StatsCard title="Total" :value="stats.total" :icon="Shuffle" />
                <StatsCard
                    title="Upsells"
                    :value="stats.upsells"
                    :icon="ArrowUpCircle"
                    variant="success"
                />
                <StatsCard
                    title="Downsells"
                    :value="stats.downsells"
                    :icon="ArrowDownCircle"
                    variant="warning"
                />
                <StatsCard
                    title="Cross-sells"
                    :value="stats.cross_sells"
                    :icon="Shuffle"
                    variant="info"
                />
                <StatsCard
                    title="Active"
                    :value="stats.active"
                    :icon="ToggleRight"
                    variant="default"
                />
            </div>

            <!-- Upsells by Type -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Upsells -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg p-2" :class="getTypeColor('upsell')">
                                <ArrowUpCircle class="h-5 w-5" />
                            </div>
                            <div>
                                <CardTitle class="text-lg">Upsells</CardTitle>
                                <CardDescription>Higher-priced alternatives</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="groupedUpsells.upsell.length === 0" class="py-8 text-center text-muted-foreground">
                            No upsells added yet
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="upsell in groupedUpsells.upsell"
                                :key="upsell.id"
                                class="flex items-center justify-between rounded-lg border p-3"
                                :class="{ 'opacity-50': !upsell.is_active }"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium">
                                        {{ upsell.upsell_product?.name }}
                                    </p>
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <span>{{ formatCurrency(upsell.upsell_product?.effective_price || 0) }}</span>
                                        <span v-if="upsell.discount_percentage" class="text-green-600">
                                            -{{ upsell.discount_percentage }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        @click="toggleStatus(upsell)"
                                    >
                                        <ToggleRight v-if="upsell.is_active" class="h-4 w-4 text-green-600" />
                                        <ToggleLeft v-else class="h-4 w-4 text-muted-foreground" />
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                        <Link :href="`/dashboard/products/${product.id}/upsells/${upsell.id}/edit`">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive"
                                        @click="openDeleteModal(upsell)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Downsells -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg p-2" :class="getTypeColor('downsell')">
                                <ArrowDownCircle class="h-5 w-5" />
                            </div>
                            <div>
                                <CardTitle class="text-lg">Downsells</CardTitle>
                                <CardDescription>Lower-priced alternatives</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="groupedUpsells.downsell.length === 0" class="py-8 text-center text-muted-foreground">
                            No downsells added yet
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="upsell in groupedUpsells.downsell"
                                :key="upsell.id"
                                class="flex items-center justify-between rounded-lg border p-3"
                                :class="{ 'opacity-50': !upsell.is_active }"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium">
                                        {{ upsell.upsell_product?.name }}
                                    </p>
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <span>{{ formatCurrency(upsell.upsell_product?.effective_price || 0) }}</span>
                                        <span v-if="upsell.discount_percentage" class="text-green-600">
                                            -{{ upsell.discount_percentage }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        @click="toggleStatus(upsell)"
                                    >
                                        <ToggleRight v-if="upsell.is_active" class="h-4 w-4 text-green-600" />
                                        <ToggleLeft v-else class="h-4 w-4 text-muted-foreground" />
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                        <Link :href="`/dashboard/products/${product.id}/upsells/${upsell.id}/edit`">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive"
                                        @click="openDeleteModal(upsell)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Cross-sells -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg p-2" :class="getTypeColor('cross_sell')">
                                <Shuffle class="h-5 w-5" />
                            </div>
                            <div>
                                <CardTitle class="text-lg">Cross-sells</CardTitle>
                                <CardDescription>Complementary products</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="groupedUpsells.cross_sell.length === 0" class="py-8 text-center text-muted-foreground">
                            No cross-sells added yet
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="upsell in groupedUpsells.cross_sell"
                                :key="upsell.id"
                                class="flex items-center justify-between rounded-lg border p-3"
                                :class="{ 'opacity-50': !upsell.is_active }"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium">
                                        {{ upsell.upsell_product?.name }}
                                    </p>
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <span>{{ formatCurrency(upsell.upsell_product?.effective_price || 0) }}</span>
                                        <span v-if="upsell.discount_percentage" class="text-green-600">
                                            -{{ upsell.discount_percentage }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        @click="toggleStatus(upsell)"
                                    >
                                        <ToggleRight v-if="upsell.is_active" class="h-4 w-4 text-green-600" />
                                        <ToggleLeft v-else class="h-4 w-4 text-muted-foreground" />
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                        <Link :href="`/dashboard/products/${product.id}/upsells/${upsell.id}/edit`">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive"
                                        @click="openDeleteModal(upsell)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ModalConfirm
            v-model:open="isDeleteModalOpen"
            title="Remove Upsell"
            :description="`Are you sure you want to remove '${selectedUpsell?.upsell_product?.name}' from ${selectedUpsell?.type_label}s?`"
            confirm-text="Remove"
            variant="danger"
            :loading="isDeleting"
            @confirm="handleDelete"
        />
    </AppLayout>
</template>
