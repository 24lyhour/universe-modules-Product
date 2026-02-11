<?php

namespace Modules\Product\Actions;

use Modules\Product\Models\Product;

class UpdateProductStockAction
{
    /**
     * Set product stock to a specific value.
     */
    public function execute(Product $product, int $quantity): Product
    {
        $product->stock = max(0, $quantity);
        $this->updateStatusBasedOnStock($product);
        $product->save();

        return $product;
    }

    /**
     * Add stock to product.
     */
    public function add(Product $product, int $quantity): Product
    {
        $product->stock += $quantity;
        $this->updateStatusBasedOnStock($product);
        $product->save();

        return $product;
    }

    /**
     * Subtract stock from product.
     */
    public function subtract(Product $product, int $quantity): Product
    {
        $product->stock = max(0, $product->stock - $quantity);
        $this->updateStatusBasedOnStock($product);
        $product->save();

        return $product;
    }

    /**
     * Update product status based on stock level.
     */
    protected function updateStatusBasedOnStock(Product $product): void
    {
        if ($product->stock <= 0 && $product->status === 'active') {
            $product->status = 'out_of_stock';
        } elseif ($product->stock > 0 && $product->status === 'out_of_stock') {
            $product->status = 'active';
        }
    }
}
