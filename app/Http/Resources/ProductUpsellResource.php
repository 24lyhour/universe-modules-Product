<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductUpsellResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'product_id' => $this->product_id,
            'upsell_product_id' => $this->upsell_product_id,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'discount_percentage' => $this->discount_percentage ? (float) $this->discount_percentage : null,
            'discounted_price' => $this->discounted_price,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'upsell_product' => $this->whenLoaded('upsellProduct', function () {
                return [
                    'id' => $this->upsellProduct->id,
                    'uuid' => $this->upsellProduct->uuid,
                    'name' => $this->upsellProduct->name,
                    'slug' => $this->upsellProduct->slug,
                    'sku' => $this->upsellProduct->sku,
                    'price' => (float) $this->upsellProduct->price,
                    'sale_price' => $this->upsellProduct->sale_price ? (float) $this->upsellProduct->sale_price : null,
                    'effective_price' => (float) $this->upsellProduct->effective_price,
                    'is_on_sale' => $this->upsellProduct->isOnSale(),
                    'stock' => $this->upsellProduct->stock,
                    'is_in_stock' => $this->upsellProduct->isInStock(),
                    'images' => $this->upsellProduct->images ?? [],
                    'status' => $this->upsellProduct->status,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
