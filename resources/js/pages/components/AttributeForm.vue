<script setup lang="ts">
import { computed } from 'vue';
import { Plus, Trash2, GripVertical } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { Switch } from '@/components/ui/switch';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { ScrollArea } from '@/components/ui/scroll-area';
import type { InertiaForm } from '@inertiajs/vue3';
import type { AttributeType, ProductAttributeValueFormData } from '../../types';

interface ValueItem extends ProductAttributeValueFormData {
    _key: number;
    id?: number;
}

interface AttributeFormData {
    name: string;
    type: AttributeType;
    description: string;
    sort_order: number;
    is_active: boolean;
    values: ValueItem[];
}

interface Props {
    mode?: 'create' | 'edit';
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
});

const model = defineModel<InertiaForm<AttributeFormData>>({ required: true });

// Keep track of key counter for new values
let valueKeyCounter = model.value.values.length > 0
    ? Math.max(...model.value.values.map(v => v._key)) + 1
    : 0;

const addValue = () => {
    model.value.values.push({
        _key: ++valueKeyCounter,
        value: '',
        label: '',
        color_code: '',
        image_url: '',
        price_adjustment: 0,
        sort_order: model.value.values.length,
        is_active: true,
    });
};

const removeValue = (index: number) => {
    model.value.values.splice(index, 1);
    // Update sort orders
    model.value.values.forEach((v, i) => {
        v.sort_order = i;
    });
};

// Attribute type options
const typeOptions: { value: AttributeType; label: string; description: string }[] = [
    { value: 'select', label: 'Select (Dropdown)', description: 'Standard dropdown selection' },
    { value: 'button', label: 'Button (Pills)', description: 'Clickable button pills' },
    { value: 'color', label: 'Color (Swatches)', description: 'Color picker swatches' },
];

// Check if type is color for showing color picker
const isColorType = computed(() => model.value.type === 'color');
</script>

<template>
    <div class="space-y-6">
        <!-- Grid Layout for Information and Values -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Basic Information Section -->
            <Card class="lg:col-span-1">
                <CardHeader>
                    <CardTitle>Attribute Information</CardTitle>
                    <CardDescription>
                        {{ mode === 'create' ? 'Define the attribute details' : 'Update the attribute details' }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Name <span class="text-destructive">*</span></Label>
                        <Input
                            id="name"
                            v-model="model.name"
                            placeholder="e.g., Storage, Color, RAM"
                            :class="{ 'border-destructive': model.errors.name }"
                        />
                        <p v-if="model.errors.name" class="text-sm text-destructive">
                            {{ model.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="type">Display Type <span class="text-destructive">*</span></Label>
                        <Select v-model="model.type">
                            <SelectTrigger>
                                <SelectValue placeholder="Select type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in typeOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p class="text-xs text-muted-foreground">
                            How this attribute will be displayed to customers
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <TiptapEditor
                            v-model="model.description"
                            placeholder="Describe this attribute (optional)..."
                            min-height="150px"
                            max-height="300px"
                        />
                    </div>

                    <!-- Settings moved here -->
                    <Separator class="my-4" />

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="sort_order">Sort Order</Label>
                            <Input
                                id="sort_order"
                                v-model.number="model.sort_order"
                                type="number"
                                min="0"
                                placeholder="0"
                            />
                            <p class="text-xs text-muted-foreground">Lower numbers appear first</p>
                        </div>

                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div>
                                <Label for="is_active">Active</Label>
                                <p class="text-xs text-muted-foreground">Enable this attribute</p>
                            </div>
                            <Switch
                                id="is_active"
                                :checked="model.is_active"
                                @update:checked="model.is_active = $event"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Values Section -->
            <Card class="lg:col-span-2">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle>Attribute Values</CardTitle>
                        <CardDescription>Define possible values for this attribute</CardDescription>
                    </div>
                    <Button type="button" variant="outline" size="sm" @click="addValue">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Value
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="model.values.length === 0" class="text-center py-8 text-muted-foreground">
                    No values yet. Click "Add Value" to add one.
                </div>

                <ScrollArea v-else class="max-h-[500px] pr-4">
                <div class="space-y-4">
                    <div
                        v-for="(value, index) in model.values"
                        :key="value._key"
                        class="flex items-start gap-4 p-4 border rounded-lg bg-muted/30"
                    >
                        <div class="flex items-center pt-2 text-muted-foreground cursor-grab">
                            <GripVertical class="h-5 w-5" />
                        </div>

                        <div class="flex-1 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="space-y-2">
                                <Label>Value <span class="text-destructive">*</span></Label>
                                <Input
                                    v-model="value.value"
                                    placeholder="e.g., 128gb, red"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label>Label</Label>
                                <Input
                                    v-model="value.label"
                                    placeholder="e.g., 128 GB, Red"
                                />
                                <p class="text-xs text-muted-foreground">Display name (optional)</p>
                            </div>

                            <div v-if="isColorType" class="space-y-2">
                                <Label>Color Code</Label>
                                <div class="flex gap-2">
                                    <Input
                                        v-model="value.color_code"
                                        placeholder="#FF0000"
                                        class="flex-1"
                                    />
                                    <input
                                        type="color"
                                        v-model="value.color_code"
                                        class="h-10 w-10 rounded border cursor-pointer"
                                    />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label>Price Adjustment</Label>
                                <Input
                                    v-model.number="value.price_adjustment"
                                    type="number"
                                    step="0.01"
                                    placeholder="0.00"
                                />
                                <p class="text-xs text-muted-foreground">+ or - from base price</p>
                            </div>
                        </div>

                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="text-destructive hover:text-destructive hover:bg-destructive/10"
                            @click="removeValue(index)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
                </ScrollArea>
            </CardContent>
        </Card>
        </div>
    </div>
</template>
