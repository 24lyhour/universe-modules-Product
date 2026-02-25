<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Package, Wand2, AlertTriangle } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import type { BreadcrumbItem } from '@/types';

interface ProductSettings {
    auto_generate_sku: boolean;
    sku_prefix: string;
    sku_separator: string;
    low_stock_threshold: number;
}

interface Props {
    productSettings: ProductSettings;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/dashboard/products' },
    { title: 'Settings', href: '/dashboard/products/settings' },
];

const form = useForm({
    auto_generate_sku: props.productSettings.auto_generate_sku ?? true,
    sku_prefix: props.productSettings.sku_prefix ?? '',
    sku_separator: props.productSettings.sku_separator ?? '-',
    low_stock_threshold: props.productSettings.low_stock_threshold ?? 5,
});

// Computed for Switch v-model
const autoGenerateSku = computed({
    get: () => form.auto_generate_sku,
    set: (value: boolean) => {
        form.auto_generate_sku = value;
    },
});

const handleSubmit = () => {
    form.post('/dashboard/products/settings', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Settings saved successfully');
        },
    });
};


</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Product Settings" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                    <Package class="h-6 w-6 text-primary" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Product Settings</h1>
                    <p class="text-muted-foreground">Configure product and variant behavior</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="grid gap-6 lg:grid-cols-2">
                <!-- SKU Settings -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Wand2 class="h-5 w-5 text-muted-foreground" />
                            <CardTitle>SKU Generation</CardTitle>
                        </div>
                        <CardDescription>
                            Configure how SKUs are automatically generated for variants
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <Label for="auto_generate_sku">Auto-Generate SKU</Label>
                                <p class="text-sm text-muted-foreground">
                                    Automatically generate SKU when creating variants
                                </p>
                            </div>
                            <Switch
                                id="auto_generate_sku"
                                v-model="autoGenerateSku"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="sku_prefix">SKU Prefix</Label>
                            <Input
                                id="sku_prefix"
                                v-model="form.sku_prefix"
                                placeholder="e.g., SKU, PROD"
                                maxlength="10"
                            />
                            <p class="text-xs text-muted-foreground">
                                Optional prefix added to all generated SKUs
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="sku_separator">SKU Separator</Label>
                            <Input
                                id="sku_separator"
                                v-model="form.sku_separator"
                                placeholder="-"
                                maxlength="3"
                                class="w-20"
                            />
                            <p class="text-xs text-muted-foreground">
                                Character used to separate SKU parts (default: -)
                            </p>
                        </div>

                        <div class="rounded-lg bg-muted p-4">
                            <p class="text-sm font-medium">Preview</p>
                            <p class="text-sm text-muted-foreground mt-1">
                                {{ form.sku_prefix || 'PRODUCT' }}{{ form.sku_separator }}VARIANT
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Inventory Settings -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <AlertTriangle class="h-5 w-5 text-muted-foreground" />
                            <CardTitle>Inventory Settings</CardTitle>
                        </div>
                        <CardDescription>
                            Configure default inventory behavior
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="space-y-2">
                            <Label for="low_stock_threshold">Default Low Stock Threshold</Label>
                            <Input
                                id="low_stock_threshold"
                                v-model.number="form.low_stock_threshold"
                                type="number"
                                min="0"
                                max="1000"
                                class="w-32"
                            />
                            <p class="text-xs text-muted-foreground">
                                Default threshold for low stock alerts on new variants
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Save Button -->
                <div class="lg:col-span-2">
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full sm:w-auto"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Settings' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
