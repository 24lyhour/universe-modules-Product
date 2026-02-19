<?php

namespace Modules\Product\Actions\Dashboard\V1;

use Modules\Product\Models\ProductAddOn;

class UpdateProductAddOnAction
{
    /**
     * Update a product add-on.
     */
    public function execute(ProductAddOn $addOn, array $data): ProductAddOn
    {
        $addOn->update([
            'name' => array_key_exists('name', $data) ? ($data['name'] ?: null) : $addOn->name,
            'description' => array_key_exists('description', $data) ? ($data['description'] ?: null) : $addOn->description,
            'image_url' => array_key_exists('image_url', $data) ? ($data['image_url'] ?: null) : $addOn->image_url,
            'price_adjustment' => $data['price_adjustment'] ?? $addOn->price_adjustment,
            'max_quantity' => $data['max_quantity'] ?? $addOn->max_quantity,
            'sort_order' => $data['sort_order'] ?? $addOn->sort_order,
            'is_required' => $data['is_required'] ?? $addOn->is_required,
            'is_active' => $data['is_active'] ?? $addOn->is_active,
        ]);

        return $addOn->load('addOnProduct');
    }
}
