<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import AttributeForm from '../../components/AttributeForm.vue';
import type { ProductAttributeValueFormData } from '../../../types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: 'Attributes', href: '/dashboard/products/attributes' },
    { title: 'Create', href: '/dashboard/products/attributes/create' },
];

interface ValueItem extends ProductAttributeValueFormData {
    _key: number;
}

const form = useForm({
    name: '',
    type: 'select' as const,
    description: '',
    sort_order: 0,
    is_active: true,
    values: [
        {
            _key: 1,
            value: '',
            label: '',
            color_code: '',
            image_url: '',
            price_adjustment: 0,
            sort_order: 0,
            is_active: true,
        },
    ] as ValueItem[],
});

const handleSubmit = () => {
    form.post('/dashboard/products/attributes', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Attribute" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/dashboard/products/attributes">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Create Attribute</h1>
                    <p class="text-muted-foreground">Add a new product attribute (e.g., Storage, Color)</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Form Content -->
                <AttributeForm v-model="form" mode="create" />

                <!-- Actions -->
                <div class="flex gap-3 justify-end">
                    <Button
                        type="button"
                        variant="outline"
                        as-child
                    >
                        <Link href="/dashboard/products/attributes">Cancel</Link>
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Attribute' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
