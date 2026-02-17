<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import type { ProductUpsellCreateProps, ProductUpsellFormData } from '../../../types';

const props = defineProps<ProductUpsellCreateProps>();

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

const form = useForm<ProductUpsellFormData>({
    upsell_product_id: null,
    type: 'upsell',
    discount_percentage: null,
    sort_order: 0,
    is_active: true,
});

const productIdString = computed({
    get: () => form.upsell_product_id?.toString() ?? '',
    set: (val: string) => {
        form.upsell_product_id = val ? Number(val) : null;
    },
});

const isFormInvalid = computed(() => {
    return !form.upsell_product_id;
});

const handleSubmit = () => {
    form.post(`/dashboard/products/${props.product.id}/upsells`, {
        onSuccess: () => {
            close();
            redirect();
        },
    });
};

const handleCancel = () => {
    close();
    redirect();
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const getTypeDescription = (type: string) => {
    switch (type) {
        case 'upsell':
            return 'Higher-priced alternative to offer customers';
        case 'downsell':
            return 'Lower-priced alternative if customer declines';
        case 'cross_sell':
            return 'Complementary product to suggest with purchase';
        default:
            return '';
    }
};
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        title="Add Upsell"
        :description="`Add upsell/downsell/cross-sell for ${product.name}`"
        mode="create"
        size="lg"
        submit-text="Add"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Type Selection -->
            <div class="space-y-2">
                <Label for="type">Type <span class="text-destructive">*</span></Label>
                <Select v-model="form.type">
                    <SelectTrigger>
                        <SelectValue placeholder="Select type" />
                    </SelectTrigger>
                    <SelectContent class="z-200">
                        <SelectItem value="upsell">Upsell</SelectItem>
                        <SelectItem value="downsell">Downsell</SelectItem>
                        <SelectItem value="cross_sell">Cross-sell</SelectItem>
                    </SelectContent>
                </Select>
                <p class="text-xs text-muted-foreground">
                    {{ getTypeDescription(form.type) }}
                </p>
                <p v-if="form.errors.type" class="text-sm text-destructive">
                    {{ form.errors.type }}
                </p>
            </div>

            <Separator />

            <!-- Product Selection -->
            <div class="space-y-2">
                <Label for="upsell_product_id">Product <span class="text-destructive">*</span></Label>
                <Select v-model="productIdString">
                    <SelectTrigger>
                        <SelectValue placeholder="Select a product" />
                    </SelectTrigger>
                    <SelectContent class="z-200">
                        <SelectItem
                            v-for="prod in props.availableProducts"
                            :key="prod.id"
                            :value="prod.id.toString()"
                        >
                            {{ prod.name }} ({{ formatCurrency(prod.price) }})
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.upsell_product_id" class="text-sm text-destructive">
                    {{ form.errors.upsell_product_id }}
                </p>
            </div>

            <Separator />

            <!-- Discount & Settings -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="discount_percentage">Discount (%)</Label>
                    <Input
                        id="discount_percentage"
                        :model-value="form.discount_percentage ?? undefined"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        placeholder="e.g., 10"
                        @update:model-value="form.discount_percentage = $event ? Number($event) : null"
                    />
                    <p class="text-xs text-muted-foreground">
                        Optional discount when purchased together
                    </p>
                    <p v-if="form.errors.discount_percentage" class="text-sm text-destructive">
                        {{ form.errors.discount_percentage }}
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
                    <p v-if="form.errors.sort_order" class="text-sm text-destructive">
                        {{ form.errors.sort_order }}
                    </p>
                </div>
            </div>

            <!-- Active Toggle -->
            <div class="flex items-center justify-between rounded-lg border p-4">
                <div class="space-y-0.5">
                    <Label>Active</Label>
                    <p class="text-sm text-muted-foreground">
                        Show this recommendation to customers
                    </p>
                </div>
                <Switch v-model:checked="form.is_active" />
            </div>
        </div>
    </ModalForm>
</template>
