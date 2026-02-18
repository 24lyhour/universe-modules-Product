<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
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
            'sku' => $this->sku,
            'name' => $this->name,
            'price' => $this->price ? (float) $this->price : null,
            'purchase_price' => $this->purchase_price ? (float) $this->purchase_price : null,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'stock' => $this->stock,
            'low_stock_threshold' => $this->low_stock_threshold,
            'barcode' => $this->barcode,
            'weight' => $this->weight ? (float) $this->weight : null,
            'images' => $this->images,
            'attribute_values' => $this->attribute_values,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'effective_price' => $this->effective_price,
            'display_price' => $this->display_price,
            'is_in_stock' => $this->isInStock(),
            'is_low_stock' => $this->isLowStock(),
            'is_on_sale' => $this->isOnSale(),
            'full_sku' => $this->full_sku,
            'display_images' => $this->display_images,
            'product' => new ProductResource($this->whenLoaded('product')),
            'attribute_value_relations' => ProductAttributeValueResource::collection($this->whenLoaded('attributeValueRelations')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
