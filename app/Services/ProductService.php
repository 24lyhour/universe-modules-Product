<?php

namespace Modules\Product\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Product\Models\Product;

class ProductService
{
    /**
     * Get paginated products with filters.
     */
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Product::query();

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Outlet filter
        if (!empty($filters['outlet_id'])) {
            $query->where('outlet_id', $filters['outlet_id']);
        }

        // Product type filter
        if (!empty($filters['product_type'])) {
            $query->where('product_type', $filters['product_type']);
        }

        // Featured filter
        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        // In stock filter
        if (!empty($filters['in_stock'])) {
            $query->where('stock', '>', 0);
        }

        // Low stock filter
        if (!empty($filters['low_stock'])) {
            $query->lowStock();
        }

        // Price range filter
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        $data['uuid'] = (string) Str::uuid();
        $data['slug'] = Str::slug($data['name']);
        $data['created_by'] = Auth::id();

        // Handle images
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = $this->processImages($data['images']);
        }

        return Product::create($data);
    }

    /**
     * Update a product.
     */
    public function update(Product $product, array $data): Product
    {
        $data['updated_by'] = Auth::id();

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Handle images
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = $this->processImages($data['images']);
        }

        $product->update($data);

        return $product->fresh();
    }

    /**
     * Delete a product.
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Get product statistics.
     */
    public function getStats(): array
    {
        return [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'inactive' => Product::where('status', 'inactive')->count(),
            'draft' => Product::where('status', 'draft')->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
            'low_stock' => Product::lowStock()->count(),
            'featured' => Product::where('is_featured', true)->count(),
        ];
    }

    /**
     * Update product stock.
     */
    public function updateStock(Product $product, int $quantity, string $operation = 'set'): Product
    {
        switch ($operation) {
            case 'add':
                $product->stock += $quantity;
                break;
            case 'subtract':
                $product->stock = max(0, $product->stock - $quantity);
                break;
            default:
                $product->stock = $quantity;
        }

        // Update status if out of stock
        if ($product->stock <= 0 && $product->status === 'active') {
            $product->status = 'out_of_stock';
        } elseif ($product->stock > 0 && $product->status === 'out_of_stock') {
            $product->status = 'active';
        }

        $product->save();

        return $product;
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Product $product): Product
    {
        $product->is_featured = !$product->is_featured;
        $product->save();

        return $product;
    }

    /**
     * Update product status.
     */
    public function updateStatus(Product $product, string $status): Product
    {
        $product->status = $status;
        $product->save();

        return $product;
    }

    /**
     * Process and store images.
     */
    protected function processImages(array $images): array
    {
        return array_filter($images);
    }

    /**
     * Find product by slug.
     */
    public function findBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)->first();
    }

    /**
     * Get related products.
     */
    public function getRelated(Product $product, int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->where('status', 'active')
            ->limit($limit)
            ->get();
    }
}
