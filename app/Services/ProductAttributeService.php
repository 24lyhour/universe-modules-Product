<?php

namespace Modules\Product\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Product\Models\ProductAttribute;

class ProductAttributeService
{
    /**
     * Get paginated attributes with filters.
     */
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = ProductAttribute::query()->withCount('values');

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Type filter
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Status filter
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get attribute statistics.
     */
    public function getStats(): array
    {
        return [
            'total' => ProductAttribute::count(),
            'active' => ProductAttribute::where('is_active', true)->count(),
            'inactive' => ProductAttribute::where('is_active', false)->count(),
        ];
    }

    /**
     * Toggle attribute active status.
     */
    public function toggleStatus(ProductAttribute $attribute): ProductAttribute
    {
        $attribute->update([
            'is_active' => !$attribute->is_active,
        ]);

        return $attribute;
    }

    /**
     * Get all active attributes with values.
     */
    public function getActiveWithValues(): \Illuminate\Database\Eloquent\Collection
    {
        return ProductAttribute::with('values')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
