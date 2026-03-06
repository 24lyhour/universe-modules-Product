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
            'product_type_id' => $this->product_type_id,
            'productType' => $this->whenLoaded('productType', function () {
                return [
                    'id' => $this->productType->id,
                    'uuid' => $this->productType->uuid,
                    'name' => $this->productType->name,
                    'slug' => $this->productType->slug,
                ];
            }),
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
                    'uuid' => $this->outlet->uuid,
                    'name' => $this->outlet->name,
                    'address' => $this->outlet->address,
                    'phone' => $this->outlet->phone,
                    'logo' => $this->outlet->logo,
                ];
            }),

            // Convenience fields for mobile app
            'image' => $this->images[0] ?? null, // First image for listing
            'upsale_id' => $this->upsale_id,
            'upsell' => $this->whenLoaded('upsell', function () {
                return [
                    'id' => $this->upsell->id,
                    'name' => $this->upsell->name,
                    'price' => (float) $this->upsell->price,
                    'sku' => $this->upsell->sku,
                ];
            }),
            'down_sale_id' => $this->down_sale_id,
            'downsell' => $this->whenLoaded('downsell', function () {
                return [
                    'id' => $this->downsell->id,
                    'name' => $this->downsell->name,
                    'price' => (float) $this->downsell->price,
                    'sku' => $this->downsell->sku,
                ];
            }),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'variants_count' => $this->whenCounted('variants'),
            'attributes_count' => $this->whenCounted('attributes'),
            'has_variants' => $this->when($this->variants_count !== null, fn() => $this->variants_count > 0),
            'has_attributes' => $this->when($this->attributes_count !== null, fn() => $this->attributes_count > 0),
            'variants' => $this->whenLoaded('variants', fn() => $this->variants->map(fn($variant) => [
                'id' => $variant->id,
                'uuid' => $variant->uuid,
                'sku' => $variant->sku,
                'name' => $variant->name,
                'price' => $variant->price ? (float) $variant->price : null,
                'sale_price' => $variant->sale_price ? (float) $variant->sale_price : null,
                'stock' => $variant->stock,
                'is_default' => $variant->is_default,
                'is_active' => $variant->is_active,
                'images' => $variant->images ?? [],
                'attribute_values' => $variant->attribute_values,
                'attribute_value_relations' => $variant->relationLoaded('attributeValueRelations')
                    ? $variant->attributeValueRelations->map(fn($av) => [
                        'id' => $av->id,
                        'value' => $av->value,
                        'label' => $av->label,
                        'color_code' => $av->color_code,
                        'attribute' => $av->relationLoaded('attribute') ? [
                            'id' => $av->attribute->id,
                            'name' => $av->attribute->name,
                            'type' => $av->attribute->type,
                        ] : null,
                    ])
                    : [],
            ])),
            'attributes' => $this->whenLoaded('attributes', fn() => $this->attributes->map(fn($attr) => [
                'id' => $attr->id,
                'uuid' => $attr->uuid,
                'name' => $attr->name,
                'type' => $attr->type,
                'is_active' => $attr->is_active,
                'values' => $attr->relationLoaded('values')
                    ? $attr->values->map(fn($v) => [
                        'id' => $v->id,
                        'value' => $v->value,
                        'label' => $v->label,
                        'color_code' => $v->color_code,
                        'is_active' => $v->is_active,
                    ])
                    : [],
            ])),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
