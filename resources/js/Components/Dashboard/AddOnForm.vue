<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { ImageUpload } from '@/components/shared';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { InertiaForm } from '@inertiajs/vue3';
import type { ProductAddOnFormData, ProductSimple, ProductAddOn } from '../../types';

interface Props {
    mode?: 'create' | 'edit';
    availableProducts?: ProductSimple[];
    addOn?: ProductAddOn;
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    availableProducts: () => [],
});

const model = defineModel<InertiaForm<ProductAddOnFormData>>({ required: true });

// Computed for product select
const selectedProductId = computed({
    get: () => model.value.add_on_product_id?.toString() ?? '',
    set: (value: string | undefined) => {
        model.value.add_on_product_id = value ? parseInt(value) : null;
    },
});

// Computed for single image (ImageUpload expects string[])
const imageUrlArray = computed({
    get: () => model.value.image_url ? [model.value.image_url] : [],
    set: (value: string[]) => {
        model.value.image_url = value.length > 0 ? value[0] : '';
    },
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Product Selection (Create mode only) -->
        <div v-if="mode === 'create'" class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Select Product</h3>
                <p class="text-sm text-muted-foreground">
                    Choose a product to add as an add-on
                </p>
            </div>
            <Separator />

            <div class="space-y-2">
                <Label for="add_on_product_id">Product <span class="text-destructive">*</span></Label>
                <Select v-model="selectedProductId">
                    <SelectTrigger id="add_on_product_id">
                        <SelectValue placeholder="Select a product" />
                    </SelectTrigger>
                    <SelectContent class="z-9999 max-h-60 overflow-y-auto">
                        <SelectItem
                            v-for="prod in availableProducts"
                            :key="prod.id"
                            :value="prod.id.toString()"
                        >
                            {{ prod.name }} ({{ formatCurrency(prod.price) }})
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="model.errors.add_on_product_id" class="text-sm text-destructive">
                    {{ model.errors.add_on_product_id }}
                </p>
            </div>
        </div>

        <!-- Product Info (Edit mode only) -->
        <div v-if="mode === 'edit' && addOn?.add_on_product" class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Product</h3>
                <p class="text-sm text-muted-foreground">
                    Add-on product details
                </p>
            </div>
            <Separator />

            <div class="rounded-lg border p-4 bg-muted/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ addOn.add_on_product.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            SKU: {{ addOn.add_on_product.sku || 'N/A' }}
                        </p>
                    </div>
                    <Badge variant="outline">
                        {{ formatCurrency(addOn.add_on_product.effective_price) }}
                    </Badge>
                </div>
            </div>
        </div>

        <!-- Details Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Details</h3>
                <p class="text-sm text-muted-foreground">
                    Custom name, description, and image for this add-on
                </p>
            </div>
            <Separator />

            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        v-model="model.name"
                        placeholder="Custom add-on name (optional)"
                    />
                    <p class="text-xs text-muted-foreground">
                        Leave empty to use the product name
                    </p>
                    <p v-if="model.errors.name" class="text-sm text-destructive">
                        {{ model.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="model.description"
                        placeholder="Custom description for this add-on (optional)"
                        rows="3"
                    />
                    <p v-if="model.errors.description" class="text-sm text-destructive">
                        {{ model.errors.description }}
                    </p>
                </div>

                <div class="space-y-2">
                    <ImageUpload
                        v-model="imageUrlArray"
                        label="Image"
                        :multiple="false"
                        :max-files="1"
                        accept="image/*"
                    />
                    <p class="text-xs text-muted-foreground">
                        Custom image for this add-on (optional)
                    </p>
                    <p v-if="model.errors.image_url" class="text-sm text-destructive">
                        {{ model.errors.image_url }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Settings Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Settings</h3>
                <p class="text-sm text-muted-foreground">
                    Configure add-on pricing and limits
                </p>
            </div>
            <Separator />

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="price_adjustment">Price Adjustment ($) <span class="text-destructive">*</span></Label>
                    <Input
                        id="price_adjustment"
                        v-model.number="model.price_adjustment"
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        required
                    />
                    <p class="text-xs text-muted-foreground">
                        Additional charge (+) or discount (-)
                    </p>
                    <p v-if="model.errors.price_adjustment" class="text-sm text-destructive">
                        {{ model.errors.price_adjustment }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="max_quantity">Max Quantity <span class="text-destructive">*</span></Label>
                    <Input
                        id="max_quantity"
                        v-model.number="model.max_quantity"
                        type="number"
                        min="1"
                        placeholder="1"
                        required
                    />
                    <p class="text-xs text-muted-foreground">
                        Maximum quantity customer can add
                    </p>
                    <p v-if="model.errors.max_quantity" class="text-sm text-destructive">
                        {{ model.errors.max_quantity }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="sort_order">Sort Order</Label>
                    <Input
                        id="sort_order"
                        v-model.number="model.sort_order"
                        type="number"
                        min="0"
                        placeholder="0"
                    />
                    <p class="text-xs text-muted-foreground">
                        Lower numbers appear first
                    </p>
                    <p v-if="model.errors.sort_order" class="text-sm text-destructive">
                        {{ model.errors.sort_order }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Toggles Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Options</h3>
                <p class="text-sm text-muted-foreground">
                    Configure add-on behavior
                </p>
            </div>
            <Separator />

            <div class="space-y-4">
                <div class="flex items-center justify-between rounded-lg border p-4">
                    <div class="space-y-0.5">
                        <Label for="is_required">Required</Label>
                        <p class="text-sm text-muted-foreground">
                            Customer must select this add-on
                        </p>
                    </div>
                    <Switch id="is_required" v-model:checked="model.is_required" />
                </div>

                <div class="flex items-center justify-between rounded-lg border p-4">
                    <div class="space-y-0.5">
                        <Label for="is_active">Active</Label>
                        <p class="text-sm text-muted-foreground">
                            Show this add-on to customers
                        </p>
                    </div>
                    <Switch id="is_active" v-model:checked="model.is_active" />
                </div>
            </div>
        </div>
    </div>
</template>
