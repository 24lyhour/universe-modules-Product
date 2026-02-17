<?php

namespace Modules\Product\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductVariant;

class ProductVariantService
{
    /**
     * Get paginated variants for a product.
     */
    public function paginateForProduct(Product $product, int $perPage = 10): LengthAwarePaginator
    {
        return $product->variants()
            ->with('attributeValueRelations.attribute')
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    /**
     * Get variant with relations.
     */
    public function getWithRelations(ProductVariant $variant): ProductVariant
    {
        return $variant->load('attributeValueRelations.attribute');
    }

    /**
     * Get variant statistics for a product.
     */
    public function getStatsForProduct(Product $product): array
    {
        $variants = $product->variants;

        return [
            'total' => $variants->count(),
            'totalStock' => $variants->sum('stock'),
            'active' => $variants->where('is_active', true)->count(),
            'lowStock' => $variants->filter(fn($v) => $v->isLowStock())->count(),
        ];
    }

    /**
     * Set variant as default.
     */
    public function setAsDefault(Product $product, ProductVariant $variant): ProductVariant
    {
        // Unset other defaults
        $product->variants()->where('id', '!=', $variant->id)->update(['is_default' => false]);

        // Set this as default
        $variant->update(['is_default' => true]);

        return $variant;
    }

    /**
     * Update variant stock.
     */
    public function updateStock(ProductVariant $variant, int $quantity, string $operation = 'set'): ProductVariant
    {
        switch ($operation) {
            case 'add':
                $variant->stock += $quantity;
                break;
            case 'subtract':
                $variant->stock = max(0, $variant->stock - $quantity);
                break;
            default:
                $variant->stock = $quantity;
        }

        $variant->save();

        return $variant;
    }
}
