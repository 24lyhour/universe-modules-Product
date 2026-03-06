<?php

namespace Modules\Product\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Modules\Product\Models\ProductAttribute;

class ProductAttributeService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const CACHE_KEY_STATS = 'product_attribute_stats';

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
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $isActive = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        return $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get paginated trashed attributes.
     */
    public function getTrashed(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = ProductAttribute::onlyTrashed()->withCount('values');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('deleted_at', 'desc')->paginate($perPage);
    }

    /**
     * Get attribute statistics (cached).
     */
    public function getStats(): array
    {
        return Cache::remember(self::CACHE_KEY_STATS, self::CACHE_TTL, function () {
            return [
                'total' => ProductAttribute::count(),
                'active' => ProductAttribute::where('is_active', true)->count(),
                'inactive' => ProductAttribute::where('is_active', false)->count(),
                'trashed' => ProductAttribute::onlyTrashed()->count(),
            ];
        });
    }

    /**
     * Clear the stats cache.
     */
    public function clearStatsCache(): void
    {
        Cache::forget(self::CACHE_KEY_STATS);
    }

    /**
     * Toggle attribute active status.
     */
    public function toggleStatus(ProductAttribute $attribute): ProductAttribute
    {
        $attribute->update([
            'is_active' => !$attribute->is_active,
        ]);
        $this->clearStatsCache();

        return $attribute;
    }

    /**
     * Get all active attributes with values.
     */
    public function getActiveWithValues(): Collection
    {
        return ProductAttribute::with('values')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Bulk delete attributes.
     */
    public function bulkDelete(array $uuids): int
    {
        $count = ProductAttribute::whereIn('uuid', $uuids)->delete();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Restore a single attribute.
     */
    public function restore(ProductAttribute $attribute): bool
    {
        $result = $attribute->restore();
        $this->clearStatsCache();
        return $result;
    }

    /**
     * Force delete a single attribute.
     */
    public function forceDelete(ProductAttribute $attribute): bool
    {
        $result = $attribute->forceDelete();
        $this->clearStatsCache();
        return $result;
    }

    /**
     * Bulk restore attributes.
     */
    public function bulkRestore(array $uuids): int
    {
        $count = ProductAttribute::onlyTrashed()->whereIn('uuid', $uuids)->restore();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Bulk force delete attributes.
     */
    public function bulkForceDelete(array $uuids): int
    {
        $count = ProductAttribute::onlyTrashed()->whereIn('uuid', $uuids)->forceDelete();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Empty trash (force delete all trashed).
     */
    public function emptyTrash(): int
    {
        $count = ProductAttribute::onlyTrashed()->forceDelete();
        $this->clearStatsCache();
        return $count;
    }
}
