<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, GripVertical } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
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
    type: 'select',
    description: '',
    sort_order: 0,
    is_active: true,
    values: [] as ValueItem[],
});

let valueKeyCounter = 0;

const addValue = () => {
    form.values.push({
        _key: ++valueKeyCounter,
        value: '',
        label: '',
        color_code: '',
        image_url: '',
        price_adjustment: 0,
        sort_order: form.values.length,
        is_active: true,
    });
};

const removeValue = (index: number) => {
    form.values.splice(index, 1);
    // Update sort orders
    form.values.forEach((v, i) => {
        v.sort_order = i;
    });
};

const handleSubmit = () => {
    form.post('/dashboard/products/attributes', {
        preserveScroll: true,
    });
};

// Add initial value
addValue();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Attribute" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Create Attribute</h1>
                    <p class="text-muted-foreground">Add a new product variation attribute</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Info Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Attribute Information</CardTitle>
                            <CardDescription>Basic details about the attribute</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="name">Name *</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="e.g., Size, Color"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                    <p v-if="form.errors.name" class="text-sm text-red-500">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="type">Type *</Label>
                                    <Select v-model="form.type">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="select">Select (Dropdown)</SelectItem>
                                            <SelectItem value="button">Button (Pills)</SelectItem>
                                            <SelectItem value="color">Color (Swatches)</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Describe this attribute..."
                                    rows="3"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Values Card -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle>Attribute Values</CardTitle>
                                    <CardDescription>Define possible values for this attribute</CardDescription>
                                </div>
                                <Button type="button" variant="outline" size="sm" @click="addValue">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Value
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="form.values.length === 0" class="text-center py-8 text-muted-foreground">
                                No values yet. Click "Add Value" to add one.
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="(value, index) in form.values"
                                    :key="value._key"
                                    class="flex items-start gap-4 p-4 border rounded-lg bg-muted/30"
                                >
                                    <div class="flex items-center pt-2 text-muted-foreground cursor-grab">
                                        <GripVertical class="h-5 w-5" />
                                    </div>

                                    <div class="flex-1 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <div class="space-y-2">
                                            <Label>Value *</Label>
                                            <Input
                                                v-model="value.value"
                                                placeholder="e.g., S, M, L"
                                            />
                                        </div>

                                        <div class="space-y-2">
                                            <Label>Label</Label>
                                            <Input
                                                v-model="value.label"
                                                placeholder="e.g., Small, Medium"
                                            />
                                        </div>

                                        <div v-if="form.type === 'color'" class="space-y-2">
                                            <Label>Color Code</Label>
                                            <div class="flex gap-2">
                                                <Input
                                                    v-model="value.color_code"
                                                    placeholder="#FF0000"
                                                    class="flex-1"
                                                />
                                                <input
                                                    type="color"
                                                    v-model="value.color_code"
                                                    class="h-10 w-10 rounded border cursor-pointer"
                                                />
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <Label>Price Adjustment</Label>
                                            <Input
                                                v-model.number="value.price_adjustment"
                                                type="number"
                                                step="0.01"
                                                placeholder="0.00"
                                            />
                                        </div>
                                    </div>

                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="text-red-500 hover:text-red-600 hover:bg-red-50"
                                        @click="removeValue(index)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Settings Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Settings</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="sort_order">Sort Order</Label>
                                <Input
                                    id="sort_order"
                                    v-model.number="form.sort_order"
                                    type="number"
                                    min="0"
                                />
                            </div>

                            <div class="flex items-center justify-between">
                                <Label for="is_active">Active</Label>
                                <Switch
                                    id="is_active"
                                    :checked="form.is_active"
                                    @update:checked="form.is_active = $event"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions Card -->
                    <Card>
                        <CardContent class="pt-6 space-y-3">
                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Attribute' }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                class="w-full"
                                as-child
                            >
                                <Link href="/dashboard/products/attributes">Cancel</Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
