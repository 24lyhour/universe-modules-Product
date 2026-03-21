<script setup lang="ts">
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
import type { InertiaForm } from '@inertiajs/vue3';
import type { BrandFormData, Outlet } from '@product/types';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { ImageUpload } from '@/components/shared';
import { useTranslation } from '@/composables/useTranslation';

const { __ } = useTranslation();

interface Props {
    mode?: 'create' | 'edit';
    outlets?: Outlet[];
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    outlets: () => [],
});

const model = defineModel<InertiaForm<BrandFormData>>({ required: true });

// Convert outlet_id to string for Select component
const outletIdString = computed({
    get: () => model.value.outlet_id?.toString() ?? 'none',
    set: (val: string) => {
        model.value.outlet_id = val && val !== 'none' ? Number(val) : null;
    },
});

// Handle single logo upload
const logoArray = computed({
    get: () => model.value.logo ? [model.value.logo] : [],
    set: (val: string[]) => {
        model.value.logo = val.length > 0 ? val[0] : '';
    },
});

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
                    {{ mode === 'create' ? __('Enter the brand details') : __('Update the brand details') }}
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
                        :placeholder="__('Enter brand name')"
                    />
                    <p v-if="model.errors.name" class="text-sm text-destructive">
                        {{ model.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="outlet_id">{{ __('Outlet') }}</Label>
                    <Select v-model="outletIdString">
                        <SelectTrigger class="w-full">
                            <SelectValue :placeholder="__('Select outlet (optional)')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="none">{{ __('No outlet') }}</SelectItem>
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
                    <Label for="website">{{ __('Website') }}</Label>
                    <Input
                        id="website"
                        v-model="model.website"
                        type="url"
                        placeholder="https://example.com"
                    />
                    <p v-if="model.errors.website" class="text-sm text-destructive">
                        {{ model.errors.website }}
                    </p>
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <Label for="description">{{ __('Description') }}</Label>
                    <TiptapEditor
                        v-model="model.description"
                        :placeholder="__('Enter description (optional)')"
                        max-height="300px"
                        min-height="150px"
                    />
                    <p v-if="model.errors.description" class="text-sm text-destructive">
                        {{ model.errors.description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Logo Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">{{ __('Logo') }}</h3>
                <p class="text-sm text-muted-foreground">{{ __('Upload your brand logo') }}</p>
            </div>
            <Separator />

            <div class="space-y-2">
                <ImageUpload
                    v-model="logoArray"
                    :max-files="1"
                    accept="image/*"
                    folder="brands"
                />
                <p v-if="model.errors.logo" class="text-sm text-destructive">
                    {{ model.errors.logo }}
                </p>
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
                    {{ __('Enable this brand for use') }}
                </p>
            </div>
        </div>
    </div>
</template>
