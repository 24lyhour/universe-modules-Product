<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { type VNode } from 'vue';
import {
    ArrowLeft,
    Pencil,
    Trash2,
    Tag,
    Package,
    Building2,
    Calendar,
    Globe,
    ExternalLink,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { type BreadcrumbItem } from '@/types';
import type { BrandShowProps } from '@product/types';

defineOptions({
    layout: (h: (type: unknown, props: unknown, children: unknown) => VNode, page: VNode) =>
        h(AppLayout, { breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Products', href: '/dashboard/products' },
            { title: 'Brands', href: '/dashboard/brands' },
            { title: 'View', href: '#' },
        ] as BreadcrumbItem[] }, () => page),
});

const props = defineProps<BrandShowProps>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const handleBack = () => {
    router.visit('/dashboard/brands');
};

const handleEdit = () => {
    router.visit(`/dashboard/brands/${props.brand.id}/edit`);
};

const handleDelete = () => {
    if (confirm(`Are you sure you want to delete "${props.brand.name}"?`)) {
        router.delete(`/dashboard/brands/${props.brand.id}`);
    }
};

const handleViewProducts = () => {
    router.visit(`/dashboard/products?brand_id=${props.brand.id}`);
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <Head :title="brand.name" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <Button variant="ghost" @click="handleBack">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Brands
            </Button>
            <div class="flex gap-2">
                <Button variant="outline" @click="handleEdit">
                    <Pencil class="mr-2 h-4 w-4" />
                    Edit
                </Button>
                <Button variant="destructive" @click="handleDelete">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete
                </Button>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-4">
                <Card>
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <Avatar class="h-16 w-16">
                                    <AvatarImage v-if="brand.logo" :src="brand.logo" :alt="brand.name" />
                                    <AvatarFallback class="bg-primary/10 text-primary text-lg">
                                        {{ getInitials(brand.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <CardTitle class="text-2xl">{{ brand.name }}</CardTitle>
                                    <CardDescription v-if="brand.slug">
                                        Slug: {{ brand.slug }}
                                    </CardDescription>
                                </div>
                            </div>
                            <Badge :variant="brand.is_active ? 'default' : 'secondary'">
                                {{ brand.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground">Description</h4>
                                <div
                                    v-if="brand.description"
                                    class="mt-1 prose prose-sm max-w-none dark:prose-invert"
                                    v-html="brand.description"
                                />
                                <p v-else class="mt-1 text-muted-foreground">No description provided</p>
                            </div>

                            <Separator />

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">Sort Order</h4>
                                    <Badge variant="outline" class="tabular-nums">
                                        {{ brand.sort_order }}
                                    </Badge>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">UUID</h4>
                                    <code class="text-xs bg-muted px-2 py-1 rounded">
                                        {{ brand.uuid }}
                                    </code>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Products Section -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Package class="h-5 w-5" />
                                <CardTitle class="text-lg">Products</CardTitle>
                            </div>
                            <Button
                                v-if="(brand.products_count ?? 0) > 0"
                                variant="outline"
                                size="sm"
                                @click="handleViewProducts"
                            >
                                View All
                            </Button>
                        </div>
                        <CardDescription>
                            {{ brand.products_count ?? 0 }} product(s) with this brand
                        </CardDescription>
                    </CardHeader>
                    <CardContent v-if="(brand.products_count ?? 0) > 0">
                        <div class="flex items-center justify-center py-4">
                            <Button variant="default" @click="handleViewProducts">
                                <Package class="mr-2 h-4 w-4" />
                                View {{ brand.products_count }} Products
                            </Button>
                        </div>
                    </CardContent>
                    <CardContent v-else class="flex flex-col items-center justify-center py-8 text-center">
                        <Package class="h-12 w-12 text-muted-foreground mb-3" />
                        <p class="text-muted-foreground">No products with this brand yet</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Website -->
                <Card v-if="brand.website">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Globe class="h-5 w-5" />
                            <CardTitle class="text-lg">Website</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <a
                            :href="brand.website"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-2 text-primary hover:underline"
                        >
                            <span class="truncate">{{ brand.website.replace(/^https?:\/\//, '') }}</span>
                            <ExternalLink class="h-4 w-4 shrink-0" />
                        </a>
                    </CardContent>
                </Card>

                <!-- Outlet Info -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Building2 class="h-5 w-5" />
                            <CardTitle class="text-lg">Outlet</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="brand.outlet" class="flex items-center gap-2">
                            <Badge
                                variant="secondary"
                                class="cursor-pointer transition-colors hover:bg-secondary/80"
                                @click="router.visit(`/dashboard/products?outlet_id=${brand.outlet.id}`)"
                            >
                                {{ brand.outlet.name }}
                            </Badge>
                        </div>
                        <span v-else class="text-muted-foreground">No outlet assigned</span>
                    </CardContent>
                </Card>

                <!-- Timestamps -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Calendar class="h-5 w-5" />
                            <CardTitle class="text-lg">Timestamps</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Created</span>
                            <span class="text-sm">{{ formatDate(brand.created_at) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Updated</span>
                            <span class="text-sm">{{ formatDate(brand.updated_at) }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
