<?php

namespace Modules\Product\Actions\Dashboard\V1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Product\Models\ProductAttribute;
use Modules\Product\Models\ProductAttributeValue;

class UpdateProductAttributeAction
{
    /**
     * Update a product attribute with values.
     */
    public function execute(ProductAttribute $attribute, array $data): ProductAttribute
    {
        $attribute->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'updated_by' => Auth::id(),
        ]);

        // Sync values if provided
        if (isset($data['values'])) {
            $this->syncValues($attribute, $data['values']);
        }

        return $attribute->fresh()->load('values');
    }

    /**
     * Sync attribute values (create, update, delete).
     */
    protected function syncValues(ProductAttribute $attribute, array $values): void
    {
        $existingIds = [];

        foreach ($values as $index => $valueData) {
            if (!empty($valueData['id'])) {
                // Update existing value
                $value = ProductAttributeValue::find($valueData['id']);
                if ($value && $value->attribute_id === $attribute->id) {
                    $value->update([
                        'value' => $valueData['value'],
                        'label' => $valueData['label'] ?? $valueData['value'],
                        'color_code' => $valueData['color_code'] ?? null,
                        'price_adjustment' => $valueData['price_adjustment'] ?? 0,
                        'sort_order' => $valueData['sort_order'] ?? $index,
                        'is_active' => $valueData['is_active'] ?? true,
                    ]);
                    $existingIds[] = $value->id;
                }
            } else {
                // Create new value
                $value = ProductAttributeValue::create([
                    'uuid' => Str::uuid(),
                    'attribute_id' => $attribute->id,
                    'value' => $valueData['value'],
                    'label' => $valueData['label'] ?? $valueData['value'],
                    'color_code' => $valueData['color_code'] ?? null,
                    'price_adjustment' => $valueData['price_adjustment'] ?? 0,
                    'sort_order' => $valueData['sort_order'] ?? $index,
                    'is_active' => $valueData['is_active'] ?? true,
                ]);
                $existingIds[] = $value->id;
            }
        }

        // Delete removed values
        $attribute->values()->whereNotIn('id', $existingIds)->delete();
    }
}
