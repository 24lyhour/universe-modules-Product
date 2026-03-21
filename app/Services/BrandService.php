<?php

namespace Modules\Product\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Product\Models\Brand;

class BrandService
{
    /**
     * Get paginated brands with filters.
     */
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Brand::query()->with('outlet');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
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
     * Get all active brands for dropdowns.
     */
    public function getActiveList(): \Illuminate\Database\Eloquent\Collection
    {
        return Brand::active()->ordered()->get(['id', 'name']);
    }

    /**
     * Find brand by ID.
     */
    public function find(int $id): ?Brand
    {
        return Brand::with('outlet')->find($id);
    }

    /**
     * Create a new brand.
     */
    public function create(array $data): Brand
    {
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? Brand::max('sort_order') + 1;

        $brand = Brand::create($data);
        $this->clearStatsCache();

        return $brand;
    }

    /**
     * Update an existing brand.
     */
    public function update(Brand $brand, array $data): Brand
    {
        if (isset($data['name']) && $data['name'] !== $brand->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $brand->id);
        }

        $brand->update($data);
        $this->clearStatsCache();

        return $brand->fresh();
    }

    /**
     * Delete a brand.
     */
    public function delete(Brand $brand): bool
    {
        $result = $brand->delete();
        $this->clearStatsCache();

        return $result;
    }

    /**
     * Get statistics for brands (cached for 5 minutes).
     */
    public function getStats(): array
    {
        return Cache::remember('brand_stats', 300, function () {
            return [
                'total' => Brand::count(),
                'active' => Brand::where('is_active', true)->count(),
                'inactive' => Brand::where('is_active', false)->count(),
            ];
        });
    }

    /**
     * Clear brand stats cache.
     */
    public function clearStatsCache(): void
    {
        Cache::forget('brand_stats');
    }

    /**
     * Update brand status.
     */
    public function updateStatus(Brand $brand, bool $isActive): Brand
    {
        $brand->is_active = $isActive;
        $brand->save();
        $this->clearStatsCache();

        return $brand;
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
            $query = Brand::where('slug', $slug);
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
