<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { ImageUpload } from '@/components/shared';
import type { InertiaForm } from '@inertiajs/vue3';
import type { ProductFormData, Outlet, ProductType } from '../../types';

// Product type options
const productTypeOptions: { value: ProductType; label: string }[] = [
    { value: 'phone', label: 'Phone' },
    { value: 'computer', label: 'Computer' },
    { value: 'tablet', label: 'Tablet' },
    { value: 'accessory', label: 'Accessory' },
    { value: 'other', label: 'Other' },
];

interface Props {
    mode?: 'create' | 'edit';
    outlets?: Outlet[];
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    outlets: () => [],
});

const model = defineModel<InertiaForm<ProductFormData>>({ required: true });

// Convert outlet_id to string for Select component
const outletIdString = computed({
    get: () => model.value.outlet_id?.toString() ?? '',
    set: (val: string) => {
        model.value.outlet_id = val ? Number(val) : null;
    },
});
</script>

<template>
    <div class="space-y-6">
        <!-- Basic Information Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Basic Information</h3>
                <p class="text-sm text-muted-foreground">
                    {{ mode === 'create' ? 'Enter the product details' : 'Update the product details' }}
                </p>
            </div>
            <Separator />

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2 sm:col-span-2">
                    <Label for="name">Product Name <span class="text-destructive">*</span></Label>
                    <Input
                        id="name"
                        v-model="model.name"
                        type="text"
                        placeholder="Enter product name"
                    />
                    <p v-if="model.errors.name" class="text-sm text-destructive">
                        {{ model.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="product_type">Product Type</Label>
                    <Select v-model="model.product_type">
                        <SelectTrigger>
                            <SelectValue placeholder="Select product type" />
                        </SelectTrigger>
                        <SelectContent class="z-200">
                            <SelectItem
                                v-for="option in productTypeOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="model.errors.product_type" class="text-sm text-destructive">
                        {{ model.errors.product_type }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="outlet_id">Outlet</Label>
                    <Select v-model="outletIdString">
                        <SelectTrigger>
                            <SelectValue placeholder="Select outlet" />
                        </SelectTrigger>
                        <SelectContent class="z-200">
                            <SelectItem
                                v-for="outlet in props.outlets"
                                :key="outlet.id"
                                :value="outlet.id.toString()"
                            >
                                {{ outlet.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="model.errors.outlet_id" class="text-sm text-destructive">
                        {{ model.errors.outlet_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="sku">SKU</Label>
                    <Input
                        id="sku"
                        v-model="model.sku"
                        type="text"
                        placeholder="e.g., PROD-001"
                    />
                    <p v-if="model.errors.sku" class="text-sm text-destructive">
                        {{ model.errors.sku }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="status">Status</Label>
                    <Select v-model="model.status">
                        <SelectTrigger>
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent class="z-200">
                            <SelectItem value="draft">Draft</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                            <SelectItem value="out_of_stock">Out of Stock</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="model.errors.status" class="text-sm text-destructive">
                        {{ model.errors.status }}
                    </p>
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="model.description"
                        placeholder="Enter product description"
                        rows="3"
                    />
                    <p v-if="model.errors.description" class="text-sm text-destructive">
                        {{ model.errors.description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Product Images Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Product Images</h3>
                <p class="text-sm text-muted-foreground">Upload product images (first image will be the main image)</p>
            </div>
            <Separator />

            <ImageUpload
                v-model="model.images"
                label=""
                :multiple="true"
                :max-files="10"
                :max-size="5"
                :error="model.errors.images"
            />
        </div>

        <!-- Pricing Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Pricing</h3>
                <p class="text-sm text-muted-foreground">Set product pricing information</p>
            </div>
            <Separator />

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="space-y-2">
                    <Label for="price">Price <span class="text-destructive">*</span></Label>
                    <Input
                        id="price"
                        v-model.number="model.price"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                    />
                    <p v-if="model.errors.price" class="text-sm text-destructive">
                        {{ model.errors.price }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="sale_price">Sale Price</Label>
                    <Input
                        id="sale_price"
                        :model-value="model.sale_price ?? undefined"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        @update:model-value="model.sale_price = $event ? Number($event) : null"
                    />
                    <p v-if="model.errors.sale_price" class="text-sm text-destructive">
                        {{ model.errors.sale_price }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="purchase_price">Purchase Price</Label>
                    <Input
                        id="purchase_price"
                        :model-value="model.purchase_price ?? undefined"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        @update:model-value="model.purchase_price = $event ? Number($event) : null"
                    />
                    <p v-if="model.errors.purchase_price" class="text-sm text-destructive">
                        {{ model.errors.purchase_price }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Inventory Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Inventory</h3>
                <p class="text-sm text-muted-foreground">Manage stock and inventory settings</p>
            </div>
            <Separator />

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="stock">Stock Quantity <span class="text-destructive">*</span></Label>
                    <Input
                        id="stock"
                        v-model.number="model.stock"
                        type="number"
                        min="0"
                        placeholder="0"
                    />
                    <p v-if="model.errors.stock" class="text-sm text-destructive">
                        {{ model.errors.stock }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="low_stock_threshold">Low Stock Threshold</Label>
                    <Input
                        id="low_stock_threshold"
                        v-model.number="model.low_stock_threshold"
                        type="number"
                        min="0"
                        placeholder="10"
                    />
                    <p v-if="model.errors.low_stock_threshold" class="text-sm text-destructive">
                        {{ model.errors.low_stock_threshold }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Options Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Options</h3>
                <p class="text-sm text-muted-foreground">Additional product options</p>
            </div>
            <Separator />

            <div class="space-y-4">
                <div class="flex items-center space-x-2">
                    <Checkbox
                        id="is_featured"
                        :checked="model.is_featured"
                        @update:checked="model.is_featured = $event"
                    />
                    <Label for="is_featured" class="cursor-pointer">
                        Featured Product
                        <span class="text-muted-foreground text-sm block">
                            Display this product in featured sections
                        </span>
                    </Label>
                </div>

                <div class="flex items-center space-x-2">
                    <Checkbox
                        id="pre_order"
                        :checked="model.pre_order"
                        @update:checked="model.pre_order = $event"
                    />
                    <Label for="pre_order" class="cursor-pointer">
                        Allow Pre-orders
                        <span class="text-muted-foreground text-sm block">
                            Allow customers to order even when out of stock
                        </span>
                    </Label>
                </div>
            </div>
        </div>
    </div>
</template>
