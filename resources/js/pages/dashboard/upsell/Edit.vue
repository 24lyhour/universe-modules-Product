<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import type { ProductUpsellEditProps } from '../../../types';

const props = defineProps<ProductUpsellEditProps>();

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
    type: props.upsell.type,
    discount_percentage: props.upsell.discount_percentage,
    sort_order: props.upsell.sort_order,
    is_active: props.upsell.is_active,
});

const handleSubmit = () => {
    form.put(`/dashboard/products/${props.product.id}/upsells/${props.upsell.id}`, {
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
        title="Edit Upsell"
        :description="`Update upsell settings for ${product.name}`"
        mode="edit"
        size="lg"
        submit-text="Save Changes"
        :loading="form.processing"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Current Product (Read-only) -->
            <div class="rounded-lg border bg-muted/50 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Linked Product</p>
                        <p class="font-medium">{{ upsell.upsell_product?.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ formatCurrency(upsell.upsell_product?.effective_price || 0) }}
                            <span v-if="upsell.upsell_product?.is_on_sale" class="ml-1 text-green-600">
                                (On Sale)
                            </span>
                        </p>
                    </div>
                    <Badge :variant="upsell.upsell_product?.is_in_stock ? 'default' : 'destructive'">
                        {{ upsell.upsell_product?.is_in_stock ? 'In Stock' : 'Out of Stock' }}
                    </Badge>
                </div>
            </div>

            <Separator />

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
