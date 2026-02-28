<?php

namespace Modules\Product\Actions\Dashboard\V1;

use Illuminate\Support\Facades\DB;
use Modules\Product\Models\ProductAttribute;

class BulkDeleteProductAttributesAction
{
    /**
     * Execute bulk delete for product attributes.
     *
     * @param array $uuids
     * @return array
     */
    public function execute(array $uuids): array
    {
        $deleted = 0;
        $failed = 0;

        DB::transaction(function () use ($uuids, &$deleted, &$failed) {
            foreach ($uuids as $uuid) {
                $attribute = ProductAttribute::where('uuid', $uuid)->first();

                if ($attribute) {
                    $attribute->delete();
                    $deleted++;
                } else {
                    $failed++;
                }
            }
        });

        return [
            'deleted' => $deleted,
            'failed' => $failed,
        ];
    }
}
