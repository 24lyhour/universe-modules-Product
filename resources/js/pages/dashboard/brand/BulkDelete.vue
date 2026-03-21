<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { ModalForm } from '@/components/shared';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { AlertTriangle, Tag } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface BrandItemForDelete {
    id: number;
    uuid: string;
    name: string;
    products_count: number;
}

interface Props {
    brandItems: BrandItemForDelete[];
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
    uuids: props.brandItems.map((b) => b.uuid),
});

watch(confirmDelete, () => {
    form.clearErrors();
});

const hasProducts = computed(() => {
    return props.brandItems.some((b) => (b.products_count ?? 0) > 0);
});

const totalProducts = computed(() => {
    return props.brandItems.reduce((sum, b) => sum + (b.products_count ?? 0), 0);
});

const canSubmit = computed(() => confirmDelete.value === true);

const handleSubmit = () => {
    form.delete('/dashboard/brands/bulk-delete', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${props.brandItems.length} brand(s) deleted successfully.`);
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
        :title="`Delete ${brandItems.length} Brand${brandItems.length > 1 ? 's' : ''}`"
        description="This action will move the selected brands to trash"
        mode="delete"
        size="md"
        :submit-text="`Delete ${brandItems.length} Brand${brandItems.length > 1 ? 's' : ''}`"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Brand List -->
            <div class="space-y-2">
                <p class="text-sm font-medium text-muted-foreground">
                    The following brands will be deleted:
                </p>
                <div class="max-h-48 space-y-2 overflow-y-auto rounded-lg border p-3">
                    <div
                        v-for="brand in brandItems"
                        :key="brand.uuid"
                        class="flex items-center gap-3 rounded-md bg-muted/30 p-2"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                            <Tag class="h-4 w-4 text-primary" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ brand.name }}</p>
                            <p class="truncate text-xs text-muted-foreground">
                                <span v-if="(brand.products_count ?? 0) > 0">
                                    {{ brand.products_count }} products
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
                        You are about to delete {{ brandItems.length }} brand{{ brandItems.length > 1 ? 's' : '' }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        <template v-if="hasProducts">
                            This will affect {{ totalProducts }} product(s) associated with these brands.
                            Products will have their brand unassigned.
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
                        I understand that {{ brandItems.length }} brand{{ brandItems.length > 1 ? 's' : '' }} will be deleted.
                    </p>
                </div>
            </div>

            <p v-if="form.errors.uuids" class="text-sm text-destructive">
                {{ form.errors.uuids }}
            </p>
        </div>
    </ModalForm>
</template>
