<?php

namespace Modules\Product\Actions\Dashboard\V1;

use Modules\Product\Models\ProductAddOn;

class DeleteProductAddOnAction
{
    /**
     * Delete a product add-on.
     */
    public function execute(ProductAddOn $addOn): bool
    {
        return $addOn->delete();
    }
}
