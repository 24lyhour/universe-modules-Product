<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttributeResource extends JsonResource
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
            'type' => $this->type,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'values' => $this->whenLoaded('values', fn () => $this->formatValues()),
            'values_count' => $this->whenCounted('values'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }

    /**
     * Format attribute values as array.
     */
    private function formatValues(): array
    {
        return $this->values->map(fn ($value) => [
            'id' => $value->id,
            'value' => $value->value,
            'label' => $value->label,
            'color_code' => $value->color_code,
            'image_url' => $value->image_url,
            'price_adjustment' => (float) $value->price_adjustment,
            'sort_order' => $value->sort_order,
            'is_active' => $value->is_active,
        ])->toArray();
    }
}
