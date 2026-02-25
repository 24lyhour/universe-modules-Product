<script setup lang="ts">
import { computed, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
import TiptapEditor from '@/components/TiptapEditor.vue';
import type { InertiaForm } from '@inertiajs/vue3';
import type { ProductFormData, Outlet, ProductType, ProductSimple, ProductCategory } from '../../types';

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
    products?: ProductSimple[];
    categories?: ProductCategory[];
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    outlets: () => [],
    products: () => [],
    categories: () => [],
});

const model = defineModel<InertiaForm<ProductFormData>>({ required: true });

// Placeholder value for "None" option (empty string not allowed by SelectItem)
const NONE_VALUE = '__none__';

// Convert product_type to string for Select component
const productTypeString = computed({
    get: () => model.value.product_type ?? NONE_VALUE,
    set: (val: string) => {
        model.value.product_type = val === NONE_VALUE ? null : (val as ProductType);
    },
});

// Convert outlet_id to string for Select component
const outletIdString = computed({
    get: () => model.value.outlet_id?.toString() ?? NONE_VALUE,
    set: (val: string) => {
        model.value.outlet_id = val === NONE_VALUE ? null : Number(val);
    },
});

// Convert category_id to string for Select component
const categoryIdString = computed({
    get: () => model.value.category_id?.toString() ?? NONE_VALUE,
    set: (val: string) => {
        model.value.category_id = val === NONE_VALUE ? null : Number(val);
    },
});

// Filter categories based on selected product type
const filteredCategories = computed(() => {
    if (!model.value.product_type) {
        return props.categories;
    }
    return props.categories.filter(
        cat => cat.product_type === null || cat.product_type === model.value.product_type
    );
});

// Clear category when product type changes and current category is incompatible
watch(() => model.value.product_type, (newType) => {
    if (model.value.category_id) {
        const currentCategory = props.categories.find(c => c.id === model.value.category_id);
        if (currentCategory && currentCategory.product_type !== null && currentCategory.product_type !== newType) {
            model.value.category_id = null;
        }
    }
});

// Status type alias
type ProductStatus = 'active' | 'inactive' | 'draft' | 'out_of_stock';

// Convert status to string for Select component
const statusString = computed({
    get: () => model.value.status ?? 'draft',
    set: (val: string) => {
        model.value.status = (val as ProductStatus) ?? 'draft';
    },
});

// Convert upsale_id to string for Select component
const upsaleIdString = computed({
    get: () => model.value.upsale_id?.toString() ?? NONE_VALUE,
    set: (val: string) => {
        model.value.upsale_id = val === NONE_VALUE ? null : Number(val);
    },
});

// Convert down_sale_id to string for Select component
const downSaleIdString = computed({
    get: () => model.value.down_sale_id?.toString() ?? NONE_VALUE,
    set: (val: string) => {
        model.value.down_sale_id = val === NONE_VALUE ? null : Number(val);
    },
});

// Computed property for TiptapEditor v-model
const editorContent = computed({
    get: () => model.value.description ?? '',
    set: (val: string) => {
        model.value.description = val;
    },
});

// Format currency for display
const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};
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
                    <Select v-model="productTypeString">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select product type" />
                        </SelectTrigger>
                        <SelectContent class="z-200">
                            <SelectItem :value="NONE_VALUE">
                                None
                            </SelectItem>
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
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select outlet" />
                        </SelectTrigger>
                        <SelectContent class="z-200">
                            <SelectItem :value="NONE_VALUE">
                                None
                            </SelectItem>
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
                    <Label for="category_id">Category</Label>
                    <Select v-model="categoryIdString">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select category" />
                        </SelectTrigger>
                        <SelectContent class="z-200">
                            <SelectItem :value="NONE_VALUE">
                                None
                            </SelectItem>
                            <SelectItem
                                v-for="category in filteredCategories"
                                :key="category.id"
                                :value="category.id.toString()"
                            >
                                {{ category.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="model.errors.category_id" class="text-sm text-destructive">
                        {{ model.errors.category_id }}
                    </p>
                    <p v-if="model.product_type && filteredCategories.length === 0" class="text-xs text-muted-foreground">
                        No categories for this product type
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
                    <Select v-model="statusString">
                        <SelectTrigger class="w-full">
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
                    <TiptapEditor
                        v-model="editorContent"
                        placeholder="Enter detailed product description with formatting..."
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
                    <Label for="sale_price">Sale Price <span class="text-destructive">*</span></Label>
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

        <!-- Upsell/Downsell Section -->
        <div v-if="props.products && props.products.length > 0" class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Upsell & Downsell</h3>
                <p class="text-sm text-muted-foreground">Recommend related products to customers</p>
            </div>
            <Separator />

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="upsale_id">Upsell Product</Label>
                    <Select v-model="upsaleIdString">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select upsell product" />
                        </SelectTrigger>
                        <SelectContent class="z-200">
                            <SelectItem :value="NONE_VALUE">None</SelectItem>
                            <SelectItem
                                v-for="product in props.products"
                                :key="product.id"
                                :value="product.id.toString()"
                            >
                                {{ product.name }} ({{ formatCurrency(product.price) }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p class="text-xs text-muted-foreground">
                        Higher-priced alternative to offer customers
                    </p>
                    <p v-if="model.errors.upsale_id" class="text-sm text-destructive">
                        {{ model.errors.upsale_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="down_sale_id">Downsell Product</Label>
                    <Select v-model="downSaleIdString">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select downsell product" />
                        </SelectTrigger>
                        <SelectContent class="z-200">
                            <SelectItem :value="NONE_VALUE">None</SelectItem>
                            <SelectItem
                                v-for="product in props.products"
                                :key="product.id"
                                :value="product.id.toString()"
                            >
                                {{ product.name }} ({{ formatCurrency(product.price) }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p class="text-xs text-muted-foreground">
                        Lower-priced alternative if customer declines
                    </p>
                    <p v-if="model.errors.down_sale_id" class="text-sm text-destructive">
                        {{ model.errors.down_sale_id }}
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
                        v-model="model.is_featured"
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
                        v-model="model.pre_order"
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
