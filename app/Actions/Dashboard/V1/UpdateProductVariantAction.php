<?php

namespace Modules\Product\Actions\Dashboard\V1;

use Modules\Product\Models\Product;
use Modules\Product\Models\ProductVariant;

class UpdateProductVariantAction
{
    /**
     * Update a product variant.
     */
    public function execute(Product $product, ProductVariant $variant, array $data): ProductVariant
    {
        // If this is set as default, unset other defaults
        if ($data['is_default'] ?? false) {
            $product->variants()->where('id', '!=', $variant->id)->update(['is_default' => false]);
        }

        $variant->update([
            'sku' => $data['sku'] ?? null,
            'name' => $data['name'] ?? null,
            'price' => $data['price'] ?? null,
            'purchase_price' => $data['purchase_price'] ?? null,
            'sale_price' => $data['sale_price'] ?? null,
            'stock' => $data['stock'],
            'low_stock_threshold' => $data['low_stock_threshold'] ?? 5,
            'barcode' => $data['barcode'] ?? null,
            'weight' => $data['weight'] ?? null,
            'images' => $data['images'] ?? null,
            'is_default' => $data['is_default'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        // Sync attribute values
        if (!empty($data['attribute_value_ids'])) {
            $variant->attributeValueRelations()->sync($data['attribute_value_ids']);
        }

        return $variant->fresh()->load('attributeValueRelations.attribute');
    }
}
