<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import AttributeForm from '../../components/AttributeForm.vue';
import type { ProductAttributeEditProps, ProductAttributeValueFormData } from '../../../types';

const props = defineProps<ProductAttributeEditProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: 'Attributes', href: '/dashboard/products/attributes' },
    { title: props.attribute.name, href: `/dashboard/products/attributes/${props.attribute.id}` },
    { title: 'Edit', href: `/dashboard/products/attributes/${props.attribute.id}/edit` },
];

interface ValueItem extends ProductAttributeValueFormData {
    _key: number;
    id?: number;
}

let valueKeyCounter = 0;

const form = useForm({
    name: props.attribute.name,
    type: props.attribute.type,
    description: props.attribute.description || '',
    sort_order: props.attribute.sort_order,
    is_active: props.attribute.is_active,
    values: (props.attribute.values || []).map((v) => ({
        _key: ++valueKeyCounter,
        id: v.id,
        value: v.value,
        label: v.label || '',
        color_code: v.color_code || '',
        image_url: v.image_url || '',
        price_adjustment: v.price_adjustment,
        sort_order: v.sort_order,
        is_active: v.is_active,
    })) as ValueItem[],
});

const handleSubmit = () => {
    form.put(`/dashboard/products/attributes/${props.attribute.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit ${attribute.name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/dashboard/products/attributes">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Attribute</h1>
                    <p class="text-muted-foreground">Update attribute: {{ attribute.name }}</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Form Content -->
                <AttributeForm v-model="form" mode="edit" />

                <!-- Actions -->
                <Card>
                    <CardContent class="pt-6 flex gap-3 justify-end">
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
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </CardContent>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
