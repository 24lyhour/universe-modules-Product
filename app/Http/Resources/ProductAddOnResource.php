<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAddOnResource extends JsonResource
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
            'add_on_product_id' => $this->add_on_product_id,
            'price_adjustment' => (float) $this->price_adjustment,
            'formatted_price_adjustment' => $this->formatted_price_adjustment,
            'final_price' => $this->final_price,
            'max_quantity' => $this->max_quantity,
            'sort_order' => $this->sort_order,
            'is_required' => $this->is_required,
            'is_active' => $this->is_active,
            'add_on_product' => $this->whenLoaded('addOnProduct', function () {
                return [
                    'id' => $this->addOnProduct->id,
                    'uuid' => $this->addOnProduct->uuid,
                    'name' => $this->addOnProduct->name,
                    'slug' => $this->addOnProduct->slug,
                    'sku' => $this->addOnProduct->sku,
                    'price' => (float) $this->addOnProduct->price,
                    'sale_price' => $this->addOnProduct->sale_price ? (float) $this->addOnProduct->sale_price : null,
                    'effective_price' => (float) $this->addOnProduct->effective_price,
                    'is_on_sale' => $this->addOnProduct->isOnSale(),
                    'stock' => $this->addOnProduct->stock,
                    'is_in_stock' => $this->addOnProduct->isInStock(),
                    'images' => $this->addOnProduct->images ?? [],
                    'status' => $this->addOnProduct->status,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
