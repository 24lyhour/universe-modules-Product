<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { ImageUpload } from '@/components/shared';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { ProductSimple } from '../../../types';

interface Props {
    parentProducts: ProductSimple[];
}

const props = defineProps<Props>();

const { show, close, redirect } = useModal();

const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => {
        if (!val) {
            close();
            redirect();
        }
    },
});

const form = useForm({
    product_id: null as number | null,
    name: '',
    description: '',
    image_url: '',
    price_adjustment: 0,
    max_quantity: 1,
    sort_order: 0,
    is_required: false,
    is_active: true,
});

// Computed for product select (convert number to string for Select component)
const selectedProductId = computed({
    get: () => form.product_id?.toString() ?? '',
    set: (value: string | undefined) => {
        form.product_id = value ? parseInt(value) : null;
    },
});

// Computed for single image (ImageUpload expects string[])
const imageUrlArray = computed({
    get: () => form.image_url ? [form.image_url] : [],
    set: (value: string[]) => {
        form.image_url = value.length > 0 ? value[0] : '';
    },
});

// Check if form is valid
const isFormInvalid = computed(() => {
    return !form.product_id || !form.name.trim();
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const handleSubmit = () => {
    form.post('/dashboard/products/addons', {
        onSuccess: () => {
            toast.success('Add-on created successfully.');
            setTimeout(() => {
                close();
                redirect();
            }, 100);
        },
    });
};

const handleCancel = () => {
    close();
    redirect();
};
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        title="Create Add-on"
        description="Create a new product add-on"
        mode="create"
        size="lg"
        submit-text="Create Add-on"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Parent Product Selection -->
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium">Select Parent Product</h3>
                    <p class="text-sm text-muted-foreground">
                        Choose the product to add the add-on to
                    </p>
                </div>
                <Separator />

                <div class="space-y-2">
                    <Label for="product_id">Parent Product <span class="text-destructive">*</span></Label>
                    <Select v-model="selectedProductId">
                        <SelectTrigger id="product_id">
                            <SelectValue placeholder="Select a parent product" />
                        </SelectTrigger>
                        <SelectContent class="z-9999 max-h-60 overflow-y-auto">
                            <SelectItem
                                v-for="prod in parentProducts"
                                :key="prod.id"
                                :value="prod.id.toString()"
                            >
                                {{ prod.name }} ({{ formatCurrency(prod.price) }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.product_id" class="text-sm text-destructive">
                        {{ form.errors.product_id }}
                    </p>
                </div>
            </div>

            <!-- Details Section -->
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium">Add-on Details</h3>
                    <p class="text-sm text-muted-foreground">
                        Name, description, and image for this add-on
                    </p>
                </div>
                <Separator />

                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Name <span class="text-destructive">*</span></Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="e.g. Extra Sauce, Gift Wrapping"
                            required
                        />
                        <p v-if="form.errors.name" class="text-sm text-destructive">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <TiptapEditor
                            v-model="form.description"
                            placeholder="Custom description for this add-on (optional)..."
                            min-height="120px"
                            max-height="250px"
                        />
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
                            v-model.number="form.price_adjustment"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            required
                        />
                        <p class="text-xs text-muted-foreground">
                            Additional charge (+) or discount (-)
                        </p>
                        <p v-if="form.errors.price_adjustment" class="text-sm text-destructive">
                            {{ form.errors.price_adjustment }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="max_quantity">Max Quantity <span class="text-destructive">*</span></Label>
                        <Input
                            id="max_quantity"
                            v-model.number="form.max_quantity"
                            type="number"
                            min="1"
                            placeholder="1"
                            required
                        />
                        <p class="text-xs text-muted-foreground">
                            Maximum quantity customer can add
                        </p>
                        <p v-if="form.errors.max_quantity" class="text-sm text-destructive">
                            {{ form.errors.max_quantity }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="sort_order">Sort Order</Label>
                        <Input
                            id="sort_order"
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            placeholder="0"
                        />
                        <p class="text-xs text-muted-foreground">
                            Lower numbers appear first
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
                        <Switch id="is_required" v-model:checked="form.is_required" />
                    </div>

                    <div class="flex items-center justify-between rounded-lg border p-4">
                        <div class="space-y-0.5">
                            <Label for="is_active">Active</Label>
                            <p class="text-sm text-muted-foreground">
                                Show this add-on to customers
                            </p>
                        </div>
                        <Switch id="is_active" v-model:checked="form.is_active" />
                    </div>
                </div>
            </div>
        </div>
    </ModalForm>
</template>
