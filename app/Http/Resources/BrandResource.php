<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
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
            'logo' => $this->logo,
            'website' => $this->website,
            'outlet_id' => $this->outlet_id,
            'outlet' => $this->whenLoaded('outlet', fn () => [
                'id' => $this->outlet->id,
                'name' => $this->outlet->name,
            ]),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'products_count' => $this->whenCounted('products'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
