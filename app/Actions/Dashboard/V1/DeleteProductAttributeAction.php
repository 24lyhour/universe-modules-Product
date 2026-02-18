<?php

namespace Modules\Product\Actions\Dashboard\V1;

use Modules\Product\Models\ProductAttribute;

class DeleteProductAttributeAction
{
    /**
     * Delete a product attribute and its values.
     */
    public function execute(ProductAttribute $attribute): bool
    {
        // Delete associated values first
        $attribute->values()->delete();

        return $attribute->delete();
    }
}
