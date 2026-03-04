<?php

namespace Modules\Product\Services;

use Illuminate\Pagination\LengthAwarePaginator;
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

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
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

        return ProductType::create($data);
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

        return $productType->fresh();
    }

    /**
     * Delete a product type.
     */
    public function delete(ProductType $productType): bool
    {
        return $productType->delete();
    }

    /**
     * Get statistics for product types.
     */
    public function getStats(): array
    {
        return [
            'total' => ProductType::count(),
            'active' => ProductType::where('is_active', true)->count(),
            'inactive' => ProductType::where('is_active', false)->count(),
        ];
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
