<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttributeValueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'attribute_id' => $this->attribute_id,
            'value' => $this->value,
            'label' => $this->label,
            'color_code' => $this->color_code,
            'image_url' => $this->image_url,
            'price_adjustment' => (float) $this->price_adjustment,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'display_label' => $this->display_label,
            'formatted_price_adjustment' => $this->formatted_price_adjustment,
            'attribute' => new ProductAttributeResource($this->whenLoaded('attribute')),
        ];
    }
}
