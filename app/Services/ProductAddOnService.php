<?php

namespace Modules\Product\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAddOn;

class ProductAddOnService
{
    /**
     * Get paginated add-ons with search.
     */
    public function getPaginatedAddOns(int $perPage = 10, ?string $search = null): LengthAwarePaginator
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
     * Get stats for all add-ons.
     */
    public function getGlobalStats(): array
    {
        return [
            'total' => ProductAddOn::count(),
            'required' => ProductAddOn::where('is_required', true)->count(),
            'optional' => ProductAddOn::where('is_required', false)->count(),
            'active' => ProductAddOn::where('is_active', true)->count(),
        ];
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
}
