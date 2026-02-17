<?php

namespace Modules\Product\Actions\Dashboard\V1;

use Modules\Product\Models\ProductVariant;

class DeleteProductVariantAction
{
    /**
     * Delete a product variant.
     */
    public function execute(ProductVariant $variant): bool
    {
        // Detach attribute values first
        $variant->attributeValueRelations()->detach();

        return $variant->delete();
    }
}
