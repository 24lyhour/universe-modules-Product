<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { ModalForm } from '@/components/shared';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { AlertTriangle, PackageSearch } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface ProductTypeItem {
    id: number;
    uuid: string;
    name: string;
    products_count: number;
}

interface Props {
    productTypeItems: ProductTypeItem[];
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
    uuids: props.productTypeItems.map((p) => p.uuid),
});

watch(confirmDelete, () => {
    form.clearErrors();
});

const hasProducts = computed(() => {
    return props.productTypeItems.some((p) => (p.products_count ?? 0) > 0);
});

const totalProducts = computed(() => {
    return props.productTypeItems.reduce((sum, p) => sum + (p.products_count ?? 0), 0);
});

const canSubmit = computed(() => confirmDelete.value === true);

const handleSubmit = () => {
    form.delete('/dashboard/product-types/bulk-delete', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${props.productTypeItems.length} product type(s) deleted successfully.`);
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
        :title="`Delete ${productTypeItems.length} Product Type${productTypeItems.length > 1 ? 's' : ''}`"
        description="This action will move the selected product types to trash"
        mode="delete"
        size="md"
        :submit-text="`Delete ${productTypeItems.length} Product Type${productTypeItems.length > 1 ? 's' : ''}`"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Product Type List -->
            <div class="space-y-2">
                <p class="text-sm font-medium text-muted-foreground">
                    The following product types will be deleted:
                </p>
                <div class="max-h-48 space-y-2 overflow-y-auto rounded-lg border p-3">
                    <div
                        v-for="productType in productTypeItems"
                        :key="productType.uuid"
                        class="flex items-center gap-3 rounded-md bg-muted/30 p-2"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                            <PackageSearch class="h-4 w-4 text-primary" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ productType.name }}</p>
                            <p class="truncate text-xs text-muted-foreground">
                                <span v-if="(productType.products_count ?? 0) > 0">
                                    {{ productType.products_count }} products
                                </span>
                                <span v-else>No products</span>
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
                        You are about to delete {{ productTypeItems.length }} product type{{ productTypeItems.length > 1 ? 's' : '' }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        <template v-if="hasProducts">
                            This will affect {{ totalProducts }} product(s) associated with these types.
                            Products will have their product type unassigned.
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
                        I understand that {{ productTypeItems.length }} product type{{ productTypeItems.length > 1 ? 's' : '' }} will be deleted.
                    </p>
                </div>
            </div>

            <p v-if="form.errors.uuids" class="text-sm text-destructive">
                {{ form.errors.uuids }}
            </p>
        </div>
    </ModalForm>
</template>
