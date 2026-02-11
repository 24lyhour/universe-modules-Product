<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sku' => $this->sku,
            'product_type' => $this->product_type,
            'price' => (float) $this->price,
            'purchase_price' => $this->purchase_price ? (float) $this->purchase_price : null,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'effective_price' => (float) $this->effective_price,
            'discount_percentage' => $this->discount_percentage,
            'is_on_sale' => $this->isOnSale(),
            'stock' => $this->stock,
            'low_stock_threshold' => $this->low_stock_threshold,
            'is_in_stock' => $this->isInStock(),
            'is_low_stock' => $this->isLowStock(),
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'pre_order' => $this->pre_order,
            'images' => $this->images ?? [],
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'outlet_id' => $this->outlet_id,
            'outlet' => $this->whenLoaded('outlet', function () {
                return [
                    'id' => $this->outlet->id,
                    'name' => $this->outlet->name,
                ];
            }),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
