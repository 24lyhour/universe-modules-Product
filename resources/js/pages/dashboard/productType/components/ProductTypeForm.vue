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
import type { InertiaForm } from '@inertiajs/vue3';
import type { ProductTypeFormData, Outlet } from '@product/types';
import TiptapEditor from '@/components/TiptapEditor.vue';

interface Props {
    mode?: 'create' | 'edit';
    outlets?: Outlet[];
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    outlets: () => [],
});

const model = defineModel<InertiaForm<ProductTypeFormData>>({ required: true });

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
                    {{ mode === 'create' ? 'Enter the product type details' : 'Update the product type details' }}
                </p>
            </div>
            <Separator />

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2 sm:col-span-2">
                    <Label for="name">Name <span class="text-destructive">*</span></Label>
                    <Input
                        id="name"
                        v-model="model.name"
                        type="text"
                        placeholder="Enter product type name"
                    />
                    <p v-if="model.errors.name" class="text-sm text-destructive">
                        {{ model.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="outlet_id">Outlet <span class="text-destructive">*</span></Label>
                    <Select v-model="outletIdString">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select outlet" />
                        </SelectTrigger>
                        <SelectContent>
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
                    <Label for="sort_order">Sort Order</Label>
                    <Input
                        id="sort_order"
                        v-model.number="model.sort_order"
                        type="number"
                        min="0"
                        placeholder="0"
                    />
                    <p v-if="model.errors.sort_order" class="text-sm text-destructive">
                        {{ model.errors.sort_order }}
                    </p>
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <Label for="description">Description</Label>
                    <TiptapEditor
                       
                        v-model="model.description"
                        placeholder="Enter description (optional)"
                        max-height="400px"
                        min-height="250px"
                    />
                    <p v-if="model.errors.description" class="text-sm text-destructive">
                        {{ model.errors.description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Options Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Options</h3>
                <p class="text-sm text-muted-foreground">Additional settings</p>
            </div>
            <Separator />

            <div class="flex items-center space-x-2">
                <Checkbox
                    id="is_active"
                    v-model:checked="model.is_active"
                />
                <Label for="is_active" class="cursor-pointer">
                    Active
                    <span class="text-muted-foreground text-sm block">
                        Enable this product type for use
                    </span>
                </Label>
            </div>
        </div>
    </div>
</template>
