<?php

namespace Modules\Product\Actions\Dashboard\V1;

use Modules\Product\Models\ProductAddOn;

class ToggleProductAddOnStatusAction
{
    /**
     * Toggle the active status of a product add-on.
     */
    public function execute(ProductAddOn $addOn): ProductAddOn
    {
        $addOn->update([
            'is_active' => !$addOn->is_active,
        ]);

        return $addOn;
    }
}
