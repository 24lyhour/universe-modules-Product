<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { ModalForm } from '@/components/shared';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { AlertTriangle, Package } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface Product {
    id: number;
    uuid: string;
    name: string;
    sku: string | null;
    status: string;
    variants_count?: number;
}

interface Props {
    productItems: Product[];
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
    uuids: props.productItems.map((p) => p.uuid),
});

watch(confirmDelete, () => {
    form.clearErrors();
});

const hasVariants = computed(() => {
    return props.productItems.some((p) => (p.variants_count ?? 0) > 0);
});

const totalVariants = computed(() => {
    return props.productItems.reduce((sum, p) => sum + (p.variants_count ?? 0), 0);
});

const canSubmit = computed(() => confirmDelete.value === true);

const handleSubmit = () => {
    form.delete('/dashboard/products/bulk-delete', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${props.productItems.length} product(s) deleted successfully.`);
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
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="`Delete ${productItems.length} Product${productItems.length > 1 ? 's' : ''}`"
        description="This action will move the selected products to trash"
        mode="delete"
        size="md"
        :submit-text="`Delete ${productItems.length} Product${productItems.length > 1 ? 's' : ''}`"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Product List -->
            <div class="space-y-2">
                <p class="text-sm font-medium text-muted-foreground">
                    The following products will be deleted:
                </p>
                <div class="max-h-48 space-y-2 overflow-y-auto rounded-lg border p-3">
                    <div
                        v-for="product in productItems"
                        :key="product.uuid"
                        class="flex items-center gap-3 rounded-md bg-muted/30 p-2"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                            <Package class="h-4 w-4 text-primary" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ product.name }}</p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ product.sku || 'No SKU' }}
                                <span v-if="(product.variants_count ?? 0) > 0" class="ml-2">
                                    ({{ product.variants_count }} variants)
                                </span>
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
                        You are about to delete {{ productItems.length }} product{{ productItems.length > 1 ? 's' : '' }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        <template v-if="hasVariants">
                            This will also affect {{ totalVariants }} variant(s) associated with these products.
                        </template>
                        Items will be moved to trash and can be restored within 30 days.
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
                        I understand that {{ productItems.length }} product{{ productItems.length > 1 ? 's' : '' }} will be deleted.
                    </p>
                </div>
            </div>

            <p v-if="form.errors.uuids" class="text-sm text-destructive">
                {{ form.errors.uuids }}
            </p>
        </div>
    </ModalForm>
</template>
