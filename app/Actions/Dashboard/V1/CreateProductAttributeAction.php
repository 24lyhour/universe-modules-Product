<?php

namespace Modules\Product\Actions\Dashboard\V1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Product\Models\ProductAttribute;
use Modules\Product\Models\ProductAttributeValue;

class CreateProductAttributeAction
{
    /**
     * Create a new product attribute with values.
     */
    public function execute(array $data): ProductAttribute
    {
        $attribute = ProductAttribute::create([
            'uuid' => Str::uuid(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'created_by' => Auth::id(),
        ]);

        // Create values if provided
        if (!empty($data['values'])) {
            $this->createValues($attribute, $data['values']);
        }

        return $attribute->load('values');
    }

    /**
     * Create attribute values.
     */
    protected function createValues(ProductAttribute $attribute, array $values): void
    {
        foreach ($values as $index => $valueData) {
            ProductAttributeValue::create([
                'uuid' => Str::uuid(),
                'attribute_id' => $attribute->id,
                'value' => $valueData['value'],
                'label' => $valueData['label'] ?? $valueData['value'],
                'color_code' => $valueData['color_code'] ?? null,
                'price_adjustment' => $valueData['price_adjustment'] ?? 0,
                'sort_order' => $valueData['sort_order'] ?? $index,
                'is_active' => $valueData['is_active'] ?? true,
            ]);
        }
    }
}
