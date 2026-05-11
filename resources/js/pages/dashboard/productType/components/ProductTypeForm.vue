<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import type { InertiaForm } from '@inertiajs/vue3';
import type { ProductTypeFormData } from '@product/types';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { useTranslation } from '@/composables/useTranslation';

const { __ } = useTranslation();

interface Props {
    mode?: 'create' | 'edit';
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
});

const model = defineModel<InertiaForm<ProductTypeFormData>>({ required: true });

// Computed for Switch v-model (matching OutletForm pattern)
const isActive = computed({
    get: () => model.value.is_active === true,
    set: (value: boolean) => {
        model.value.is_active = value;
    },
});
</script>

<template>
    <div class="space-y-6">
        <!-- Basic Information Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">{{ __('Basic Information') }}</h3>
                <p class="text-sm text-muted-foreground">
                    {{ mode === 'create' ? __('Enter the product type details') : __('Update the product type details') }}
                </p>
            </div>
            <Separator />

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2 sm:col-span-2">
                    <Label for="name">{{ __('Name') }} <span class="text-destructive">*</span></Label>
                    <Input
                        id="name"
                        v-model="model.name"
                        type="text"
                        :placeholder="__('Enter product type name')"
                    />
                    <p v-if="model.errors.name" class="text-sm text-destructive">
                        {{ model.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="sort_order">{{ __('Sort Order') }}</Label>
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
                    <Label for="description">{{ __('Description') }}</Label>
                    <TiptapEditor
                        v-model="model.description"
                        :placeholder="__('Enter description (optional)')"
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
                <h3 class="text-sm font-medium">{{ __('Options') }}</h3>
                <p class="text-sm text-muted-foreground">{{ __('Additional settings') }}</p>
            </div>
            <Separator />

            <div class="space-y-2">
                <Label for="is_active">{{ __('Status') }}</Label>
                <div class="flex items-center space-x-2 pt-1">
                    <Switch id="is_active" v-model="isActive" />
                    <Label for="is_active" class="font-normal cursor-pointer">
                        {{ isActive ? __('Active') : __('Inactive') }}
                    </Label>
                </div>
                <p class="text-sm text-muted-foreground">
                    {{ __('Enable this product type for use') }}
                </p>
            </div>
        </div>
    </div>
</template>
