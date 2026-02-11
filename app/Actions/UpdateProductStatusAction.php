<?php

namespace Modules\Product\Actions;

use Modules\Product\Models\Product;

class UpdateProductStatusAction
{
    /**
     * Update product status.
     */
    public function execute(Product $product, string $status): Product
    {
        $product->status = $status;
        $product->save();

        return $product;
    }

    /**
     * Activate a product.
     */
    public function activate(Product $product): Product
    {
        return $this->execute($product, 'active');
    }

    /**
     * Deactivate a product.
     */
    public function deactivate(Product $product): Product
    {
        return $this->execute($product, 'inactive');
    }

    /**
     * Set product as draft.
     */
    public function setDraft(Product $product): Product
    {
        return $this->execute($product, 'draft');
    }

    /**
     * Set product as out of stock.
     */
    public function setOutOfStock(Product $product): Product
    {
        return $this->execute($product, 'out_of_stock');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Product $product): Product
    {
        $product->is_featured = !$product->is_featured;
        $product->save();

        return $product;
    }
}
