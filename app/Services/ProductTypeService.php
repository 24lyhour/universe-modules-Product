<?php

namespace Modules\Product\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Product\Models\ProductType;

class ProductTypeService
{
    /**
     * Get paginated product types with filters.
     */
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = ProductType::query()->with('outlet');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            // Handle string "true"/"false" from frontend
            $isActive = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        if (!empty($filters['outlet_id'])) {
            $query->where('outlet_id', $filters['outlet_id']);
        }

        return $query->withCount('products')->ordered()->paginate($perPage);
    }

    /**
     * Get all active product types for dropdowns.
     */
    public function getActiveList(): \Illuminate\Database\Eloquent\Collection
    {
        return ProductType::active()->ordered()->get(['id', 'name']);
    }

    /**
     * Find product type by ID.
     */
    public function find(int $id): ?ProductType
    {
        return ProductType::with('outlet')->find($id);
    }

    /**
     * Create a new product type.
     */
    public function create(array $data): ProductType
    {
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? ProductType::max('sort_order') + 1;

        $productType = ProductType::create($data);
        $this->clearStatsCache();

        return $productType;
    }

    /**
     * Update an existing product type.
     */
    public function update(ProductType $productType, array $data): ProductType
    {
        if (isset($data['name']) && $data['name'] !== $productType->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $productType->id);
        }

        $productType->update($data);
        $this->clearStatsCache();

        return $productType->fresh();
    }

    /**
     * Delete a product type.
     */
    public function delete(ProductType $productType): bool
    {
        $result = $productType->delete();
        $this->clearStatsCache();

        return $result;
    }

    /**
     * Get statistics for product types (cached for 5 minutes).
     */
    public function getStats(): array
    {
        return Cache::remember('product_type_stats', 300, function () {
            return [
                'total' => ProductType::count(),
                'active' => ProductType::where('is_active', true)->count(),
                'inactive' => ProductType::where('is_active', false)->count(),
            ];
        });
    }

    /**
     * Clear product type stats cache.
     */
    public function clearStatsCache(): void
    {
        Cache::forget('product_type_stats');
    }

    /**
     * Update product type status.
     */
    public function updateStatus(ProductType $productType, bool $isActive): ProductType
    {
        $productType->is_active = $isActive;
        $productType->save();
        $this->clearStatsCache();

        return $productType;
    }

    /**
     * Generate a unique slug.
     */
    protected function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (true) {
            $query = ProductType::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
