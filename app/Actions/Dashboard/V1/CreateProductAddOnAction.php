<?php

namespace Modules\Product\Actions\Dashboard\V1;

use Illuminate\Support\Str;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAddOn;

class CreateProductAddOnAction
{
    /**
     * Create a new product add-on.
     */
    public function execute(Product $product, array $data): ProductAddOn
    {
        $addOn = ProductAddOn::create([
            'uuid' => Str::uuid(),
            'product_id' => $product->id,
            'add_on_product_id' => $data['add_on_product_id'],
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'price_adjustment' => $data['price_adjustment'] ?? 0,
            'max_quantity' => $data['max_quantity'] ?? 1,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_required' => $data['is_required'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $addOn->load('addOnProduct');
    }
}
