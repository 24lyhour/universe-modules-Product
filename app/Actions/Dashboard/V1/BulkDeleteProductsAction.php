<?php

namespace Modules\Product\Actions\Dashboard\V1;

use Illuminate\Support\Facades\DB;
use Modules\Product\Models\Product;

class BulkDeleteProductsAction
{
    /**
     * Execute bulk delete for products.
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
                $product = Product::where('uuid', $uuid)->first();

                if ($product) {
                    $product->delete();
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
