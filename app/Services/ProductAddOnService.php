<?php

namespace Modules\Product\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAddOn;

class ProductAddOnService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const CACHE_KEY_STATS = 'product_addon_stats';

    /**
     * Get paginated add-ons with search.
     */
    public function getPaginatedAddOns(int $perPage = 10, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        $query = ProductAddOn::with(['product', 'addOnProduct'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('addOnProduct', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'true' || $status === '1');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get paginated trashed add-ons.
     */
    public function getTrashedAddOns(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = ProductAddOn::onlyTrashed()
            ->with(['product', 'addOnProduct'])
            ->orderBy('deleted_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('addOnProduct', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get add-ons for a specific product.
     */
    public function getProductAddOns(Product $product): Collection
    {
        return $product->addOns()
            ->with('addOnProduct')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get stats for all add-ons (cached).
     */
    public function getGlobalStats(): array
    {
        return Cache::remember(self::CACHE_KEY_STATS, self::CACHE_TTL, function () {
            return [
                'total' => ProductAddOn::count(),
                'required' => ProductAddOn::where('is_required', true)->count(),
                'optional' => ProductAddOn::where('is_required', false)->count(),
                'active' => ProductAddOn::where('is_active', true)->count(),
                'trashed' => ProductAddOn::onlyTrashed()->count(),
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
     * Get stats for a specific product's add-ons.
     */
    public function getProductStats(Collection $addOns): array
    {
        return [
            'total' => $addOns->count(),
            'required' => $addOns->where('is_required', true)->count(),
            'optional' => $addOns->where('is_required', false)->count(),
            'active' => $addOns->where('is_active', true)->count(),
        ];
    }

    /**
     * Get available products for add-on selection (excluding current product and existing add-ons).
     */
    public function getAvailableProducts(Product $product, bool $excludeExisting = false): Collection
    {
        $query = Product::select('id', 'name', 'price', 'sku', 'status')
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->orderBy('name');

        if ($excludeExisting) {
            $existingIds = $product->addOns()->pluck('add_on_product_id')->toArray();
            $query->whereNotIn('id', $existingIds);
        }

        return $query->get();
    }

    /**
     * Get all active products for create dropdown.
     */
    public function getActiveProducts(): Collection
    {
        return Product::select('id', 'name', 'sku', 'status')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Format product data for response.
     */
    public function formatProductData(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => (float) $product->price,
        ];
    }

    /**
     * Bulk delete add-ons.
     */
    public function bulkDelete(array $uuids): int
    {
        $count = ProductAddOn::whereIn('uuid', $uuids)->delete();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Bulk restore add-ons.
     */
    public function bulkRestore(array $uuids): int
    {
        $count = ProductAddOn::onlyTrashed()->whereIn('uuid', $uuids)->restore();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Bulk force delete add-ons.
     */
    public function bulkForceDelete(array $uuids): int
    {
        $count = ProductAddOn::onlyTrashed()->whereIn('uuid', $uuids)->forceDelete();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Empty trash (force delete all trashed).
     */
    public function emptyTrash(): int
    {
        $count = ProductAddOn::onlyTrashed()->forceDelete();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Restore a single add-on.
     */
    public function restore(ProductAddOn $addOn): bool
    {
        $result = $addOn->restore();
        $this->clearStatsCache();
        return $result;
    }

    /**
     * Force delete a single add-on.
     */
    public function forceDelete(ProductAddOn $addOn): bool
    {
        $result = $addOn->forceDelete();
        $this->clearStatsCache();
        return $result;
    }
}
