<?php

namespace Modules\Product\Actions\Dashboard\V1;

use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAddOn;

class ReorderProductAddOnsAction
{
    /**
     * Reorder product add-ons.
     *
     * @param  array<array{id: int, sort_order: int}>  $items
     */
    public function execute(Product $product, array $items): void
    {
        foreach ($items as $item) {
            ProductAddOn::where('id', $item['id'])
                ->where('product_id', $product->id)
                ->update(['sort_order' => $item['sort_order']]);
        }
    }
}
