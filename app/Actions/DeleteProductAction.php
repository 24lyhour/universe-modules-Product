<?php

namespace Modules\Product\Actions;

use Modules\Product\Models\Product;

class DeleteProductAction
{
    /**
     * Delete a product.
     */
    public function execute(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Force delete a product.
     */
    public function forceDelete(Product $product): bool
    {
        return $product->forceDelete();
    }

    /**
     * Restore a soft-deleted product.
     */
    public function restore(Product $product): bool
    {
        return $product->restore();
    }
}
