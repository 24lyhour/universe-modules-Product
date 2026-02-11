<?php

namespace Modules\Product\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Product\Models\Product;

class UpdateProductAction
{
    /**
     * Update a product.
     */
    public function execute(Product $product, array $data): Product
    {
        $data['updated_by'] = Auth::id();

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Handle images
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = $this->processImages($data['images']);
        }

        $product->update($data);

        return $product->fresh();
    }

    /**
     * Process and store images.
     */
    protected function processImages(array $images): array
    {
        return array_filter($images);
    }
}
