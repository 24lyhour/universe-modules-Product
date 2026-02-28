<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { ModalForm } from '@/components/shared';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, Tags } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface Attribute {
    id: number;
    uuid: string;
    name: string;
    type: string;
    values_count?: number;
}

interface Props {
    attributeItems: Attribute[];
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

const confirmDelete = ref(false);

const form = useForm({
    uuids: props.attributeItems.map((a) => a.uuid),
});

watch(confirmDelete, () => {
    form.clearErrors();
});

const hasValues = computed(() => {
    return props.attributeItems.some((a) => (a.values_count ?? 0) > 0);
});

const totalValues = computed(() => {
    return props.attributeItems.reduce((sum, a) => sum + (a.values_count ?? 0), 0);
});

const canSubmit = computed(() => confirmDelete.value === true);

const handleSubmit = () => {
    form.delete('/dashboard/products/attributes/bulk-delete', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${props.attributeItems.length} attribute(s) deleted successfully.`);
            setTimeout(() => {
                close();
                redirect();
            }, 100);
        },
        onError: (errors) => {
            if (errors.uuids) {
                toast.error(errors.uuids);
            }
        },
    });
};

const handleCancel = () => {
    close();
    redirect();
};

const getTypeVariant = (type: string) => {
    switch (type) {
        case 'select':
            return 'default';
        case 'color':
            return 'secondary';
        case 'button':
            return 'outline';
        default:
            return 'outline';
    }
};
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="`Delete ${attributeItems.length} Attribute${attributeItems.length > 1 ? 's' : ''}`"
        description="This action will permanently delete the selected attributes and their values"
        mode="delete"
        size="md"
        :submit-text="`Delete ${attributeItems.length} Attribute${attributeItems.length > 1 ? 's' : ''}`"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Attribute List -->
            <div class="space-y-2">
                <p class="text-sm font-medium text-muted-foreground">
                    The following attributes will be deleted:
                </p>
                <div class="max-h-48 space-y-2 overflow-y-auto rounded-lg border p-3">
                    <div
                        v-for="attribute in attributeItems"
                        :key="attribute.uuid"
                        class="flex items-center gap-3 rounded-md bg-muted/30 p-2"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                            <Tags class="h-4 w-4 text-primary" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-medium">{{ attribute.name }}</p>
                                <Badge :variant="getTypeVariant(attribute.type)" class="text-xs">
                                    {{ attribute.type }}
                                </Badge>
                            </div>
                            <p v-if="(attribute.values_count ?? 0) > 0" class="truncate text-xs text-muted-foreground">
                                {{ attribute.values_count }} value(s)
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning Banner -->
            <div class="flex items-start gap-3 rounded-lg border border-destructive/50 bg-destructive/10 p-4">
                <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-destructive" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-destructive">
                        You are about to delete {{ attributeItems.length }} attribute{{ attributeItems.length > 1 ? 's' : '' }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        <template v-if="hasValues">
                            This will also delete {{ totalValues }} value(s) associated with these attributes.
                        </template>
                        This action cannot be undone.
                    </p>
                </div>
            </div>

            <!-- Confirmation Checkbox -->
            <div class="flex items-start space-x-3 rounded-lg border p-4">
                <Checkbox
                    id="bulk-confirmed"
                    :model-value="confirmDelete"
                    @update:model-value="(val: boolean | 'indeterminate') => confirmDelete = val === true"
                />
                <div class="space-y-1">
                    <Label for="bulk-confirmed" class="cursor-pointer font-medium">
                        I confirm this bulk deletion
                    </Label>
                    <p class="text-sm text-muted-foreground">
                        I understand that {{ attributeItems.length }} attribute{{ attributeItems.length > 1 ? 's' : '' }} will be permanently deleted.
                    </p>
                </div>
            </div>

            <p v-if="form.errors.uuids" class="text-sm text-destructive">
                {{ form.errors.uuids }}
            </p>
        </div>
    </ModalForm>
</template>
