<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Pencil, Trash2, ArrowLeft, Plus, Check, X } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ModalConfirm } from '@/components/shared';
import { type BreadcrumbItem } from '@/types';
import type { ProductAttributeShowProps } from '../../../types';

const props = defineProps<ProductAttributeShowProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: 'Attributes', href: '/dashboard/products/attributes' },
    { title: props.attribute.name, href: `/dashboard/products/attributes/${props.attribute.id}` },
];

// Delete modal state
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);

const handleDelete = () => {
    isDeleting.value = true;
    router.delete(`/dashboard/products/attributes/${props.attribute.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatPrice = (price: number) => {
    if (price === 0) return '-';
    const sign = price > 0 ? '+' : '';
    return `${sign}$${price.toFixed(2)}`;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="attribute.name" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/dashboard/products/attributes">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ attribute.name }}</h1>
                        <p class="text-muted-foreground">
                            {{ attribute.description || 'No description' }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/dashboard/products/attributes/${attribute.id}/edit`">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="isDeleteModalOpen = true">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Attribute Values -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle>Attribute Values</CardTitle>
                                    <CardDescription>
                                        {{ attribute.values?.length || 0 }} values defined
                                    </CardDescription>
                                </div>
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/dashboard/products/attributes/${attribute.id}/edit`">
                                        <Plus class="mr-2 h-4 w-4" />
                                        Add Value
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="!attribute.values?.length" class="text-center py-8 text-muted-foreground">
                                No values defined yet.
                            </div>
                            <div v-else class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="h-10 px-4 text-left font-medium text-muted-foreground">Value</th>
                                            <th class="h-10 px-4 text-left font-medium text-muted-foreground">Label</th>
                                            <th v-if="attribute.type === 'color'" class="h-10 px-4 text-left font-medium text-muted-foreground">Color</th>
                                            <th class="h-10 px-4 text-right font-medium text-muted-foreground">Price Adj.</th>
                                            <th class="h-10 px-4 text-center font-medium text-muted-foreground">Order</th>
                                            <th class="h-10 px-4 text-center font-medium text-muted-foreground">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="value in attribute.values" :key="value.id" class="border-b last:border-0">
                                            <td class="p-4 font-medium">{{ value.value }}</td>
                                            <td class="p-4">{{ value.label || '-' }}</td>
                                            <td v-if="attribute.type === 'color'" class="p-4">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        v-if="value.color_code"
                                                        class="h-6 w-6 rounded border"
                                                        :style="{ backgroundColor: value.color_code }"
                                                    />
                                                    <span class="text-sm text-muted-foreground">
                                                        {{ value.color_code || '-' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="p-4 text-right font-mono">
                                                {{ formatPrice(value.price_adjustment) }}
                                            </td>
                                            <td class="p-4 text-center">
                                                {{ value.sort_order }}
                                            </td>
                                            <td class="p-4 text-center">
                                                <Badge :variant="value.is_active ? 'default' : 'secondary'">
                                                    <Check v-if="value.is_active" class="mr-1 h-3 w-3" />
                                                    <X v-else class="mr-1 h-3 w-3" />
                                                    {{ value.is_active ? 'Active' : 'Inactive' }}
                                                </Badge>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Details Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Type</span>
                                <Badge :variant="getTypeVariant(attribute.type)">
                                    {{ attribute.type }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Status</span>
                                <Badge :variant="attribute.is_active ? 'default' : 'secondary'">
                                    {{ attribute.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Sort Order</span>
                                <span class="font-medium">{{ attribute.sort_order }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Slug</span>
                                <code class="text-xs bg-muted px-2 py-1 rounded">{{ attribute.slug }}</code>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Timestamps Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Timestamps</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <span class="text-sm text-muted-foreground">Created</span>
                                <p class="text-sm">{{ formatDate(attribute.created_at) }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-muted-foreground">Updated</span>
                                <p class="text-sm">{{ formatDate(attribute.updated_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ModalConfirm
            v-model:open="isDeleteModalOpen"
            title="Delete Attribute"
            :description="`Are you sure you want to delete '${attribute.name}'? This will also delete all associated values and may affect product variants.`"
            confirm-text="Delete"
            variant="danger"
            :loading="isDeleting"
            @confirm="handleDelete"
        />
    </AppLayout>
</template>
