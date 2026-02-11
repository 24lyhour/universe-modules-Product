<?php

namespace Modules\Product\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Product\Models\Product;

class CreateProductAction
{
    /**
     * Create a new product.
     */
    public function execute(array $data): Product
    {
        $data['uuid'] = (string) Str::uuid();
        $data['slug'] = Str::slug($data['name']);
        $data['created_by'] = Auth::id();

        // Handle images if they are uploaded files
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = $this->processImages($data['images']);
        }

        return Product::create($data);
    }

    /**
     * Process and store images.
     */
    protected function processImages(array $images): array
    {
        // Filter out empty values and return URLs
        // In production, you would upload files here
        return array_filter($images);
    }
}
