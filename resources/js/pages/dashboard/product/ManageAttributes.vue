<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { useForm, Link } from '@inertiajs/vue3';
import { Check, Plus, X, ExternalLink } from 'lucide-vue-next';
import { useModal } from 'momentum-modal';
import { computed, ref } from 'vue';
import type { ProductAttribute } from '../../../types';

interface Props {
    product: {
        id: number;
        name: string;
    };
    allAttributes: ProductAttribute[];
    assignedAttributeIds: number[];
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

const selectedIds = ref<number[]>([...props.assignedAttributeIds]);

const form = useForm({
    attribute_ids: props.assignedAttributeIds,
});

// Separate assigned and available attributes
const assignedAttributes = computed(() => {
    return props.allAttributes.filter(attr => selectedIds.value.includes(attr.id));
});

const availableAttributes = computed(() => {
    return props.allAttributes.filter(attr => !selectedIds.value.includes(attr.id));
});

const hasChanges = computed(() => {
    const original = [...props.assignedAttributeIds].sort();
    const current = [...selectedIds.value].sort();
    if (original.length !== current.length) return true;
    return original.some((id, i) => id !== current[i]);
});

const toggleAttribute = (attributeId: number) => {
    const index = selectedIds.value.indexOf(attributeId);
    if (index === -1) {
        selectedIds.value.push(attributeId);
    } else {
        selectedIds.value.splice(index, 1);
    }
    form.attribute_ids = [...selectedIds.value];
};

const removeAttribute = (attributeId: number) => {
    const index = selectedIds.value.indexOf(attributeId);
    if (index !== -1) {
        selectedIds.value.splice(index, 1);
        form.attribute_ids = [...selectedIds.value];
    }
};

const addAttribute = (attributeId: number) => {
    if (!selectedIds.value.includes(attributeId)) {
        selectedIds.value.push(attributeId);
        form.attribute_ids = [...selectedIds.value];
    }
};

const handleSubmit = () => {
    form.post(`/dashboard/products/${props.product.id}/attributes/sync`, {
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
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="`Manage Attributes - ${product.name}`"
        :description="assignedAttributes.length > 0
            ? `${assignedAttributes.length} attribute(s) assigned. Add or remove as needed.`
            : 'No attributes assigned yet. Add attributes below.'"
        mode="edit"
        size="lg"
        :submit-text="hasChanges ? 'Save Changes' : 'No Changes'"
        :loading="form.processing"
        :disabled="!hasChanges"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <ScrollArea class="h-[400px] pr-4">
            <div class="space-y-6">
                <!-- Assigned Attributes Section -->
                <div v-if="assignedAttributes.length > 0">
                    <div class="flex items-center gap-2 mb-3">
                        <Check class="h-4 w-4 text-green-600" />
                        <h4 class="text-sm font-semibold">Assigned Attributes ({{ assignedAttributes.length }})</h4>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="attribute in assignedAttributes"
                            :key="attribute.id"
                            class="flex items-center justify-between p-3 rounded-lg border border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950/30"
                        >
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium">{{ attribute.name }}</span>
                                    <Badge variant="outline" class="text-xs">
                                        {{ attribute.type }}
                                    </Badge>
                                </div>
                                <div v-if="attribute.values && attribute.values.length > 0" class="flex flex-wrap gap-1">
                                    <template v-for="value in attribute.values.slice(0, 4)" :key="value.id">
                                        <Badge
                                            v-if="attribute.type === 'color'"
                                            variant="secondary"
                                            class="text-xs gap-1"
                                        >
                                            <span
                                                v-if="value.color_code"
                                                class="w-2 h-2 rounded-full border"
                                                :style="{ backgroundColor: value.color_code }"
                                            />
                                            {{ value.label || value.value }}
                                        </Badge>
                                        <Badge v-else variant="secondary" class="text-xs">
                                            {{ value.label || value.value }}
                                        </Badge>
                                    </template>
                                    <Badge
                                        v-if="attribute.values.length > 4"
                                        variant="outline"
                                        class="text-xs"
                                    >
                                        +{{ attribute.values.length - 4 }} more
                                    </Badge>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="ml-2 p-1.5 rounded-md hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 transition-colors"
                                title="Remove attribute"
                                @click="removeAttribute(attribute.id)"
                            >
                                <X class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Separator when both sections exist -->
                <Separator v-if="assignedAttributes.length > 0 && availableAttributes.length > 0" />

                <!-- Available Attributes Section -->
                <div v-if="availableAttributes.length > 0">
                    <div class="flex items-center gap-2 mb-3">
                        <Plus class="h-4 w-4 text-muted-foreground" />
                        <h4 class="text-sm font-semibold text-muted-foreground">Available Attributes ({{ availableAttributes.length }})</h4>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="attribute in availableAttributes"
                            :key="attribute.id"
                            class="flex items-center justify-between p-3 rounded-lg border hover:bg-muted/50 transition-colors cursor-pointer"
                            @click="addAttribute(attribute.id)"
                        >
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium">{{ attribute.name }}</span>
                                    <Badge variant="outline" class="text-xs">
                                        {{ attribute.type }}
                                    </Badge>
                                    <span class="text-xs text-muted-foreground">
                                        {{ attribute.values?.length || 0 }} value(s)
                                    </span>
                                </div>
                                <div v-if="attribute.values && attribute.values.length > 0" class="flex flex-wrap gap-1">
                                    <template v-for="value in attribute.values.slice(0, 4)" :key="value.id">
                                        <Badge
                                            v-if="attribute.type === 'color'"
                                            variant="secondary"
                                            class="text-xs gap-1"
                                        >
                                            <span
                                                v-if="value.color_code"
                                                class="w-2 h-2 rounded-full border"
                                                :style="{ backgroundColor: value.color_code }"
                                            />
                                            {{ value.label || value.value }}
                                        </Badge>
                                        <Badge v-else variant="secondary" class="text-xs">
                                            {{ value.label || value.value }}
                                        </Badge>
                                    </template>
                                    <Badge
                                        v-if="attribute.values.length > 4"
                                        variant="outline"
                                        class="text-xs"
                                    >
                                        +{{ attribute.values.length - 4 }} more
                                    </Badge>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="ml-2 p-1.5 rounded-md hover:bg-green-100 dark:hover:bg-green-900/30 text-green-600 transition-colors"
                                title="Add attribute"
                                @click.stop="addAttribute(attribute.id)"
                            >
                                <Plus class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="allAttributes.length === 0" class="text-center py-8">
                    <p class="text-muted-foreground mb-4">No attributes available. Create attributes first.</p>
                    <Button variant="outline" as-child>
                        <Link href="/dashboard/products/attributes/create">
                            <ExternalLink class="mr-2 h-4 w-4" />
                            Create New Attribute
                        </Link>
                    </Button>
                </div>

                <!-- All assigned state -->
                <div v-if="allAttributes.length > 0 && availableAttributes.length === 0 && assignedAttributes.length > 0" class="text-center py-4">
                    <p class="text-muted-foreground text-sm mb-3">All available attributes are assigned to this product.</p>
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/dashboard/products/attributes/create">
                            <ExternalLink class="mr-2 h-3 w-3" />
                            Create More Attributes
                        </Link>
                    </Button>
                </div>
            </div>
        </ScrollArea>
    </ModalForm>
</template>
