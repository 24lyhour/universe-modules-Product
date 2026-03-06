<?php

namespace Modules\Product\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\Http\Resources\ProductResource;
use Modules\Product\Models\Product;
use Modules\Product\Services\ProductService;

/**
 * Public API controller for products (no authentication required)
 * Used by mobile app to browse products
 */
class ProductPublicController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display a listing of active products.
     *
     * @queryParam per_page int Number of items per page. Default: 15
     * @queryParam search string Search by name, description
     * @queryParam category_id int Filter by category
     * @queryParam outlet_id int Filter by outlet
     * @queryParam min_price float Minimum price
     * @queryParam max_price float Maximum price
     * @queryParam sort string Sort by: price_asc, price_desc, newest, popular
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => 'active', // Only show active products
            'search' => $request->input('search'),
            'category_id' => $request->input('category_id'),
            'outlet_id' => $request->input('outlet_id'),
            'product_type_id' => $request->input('product_type_id'),
            'product_type' => $request->input('product_type'), // Filter by product_type string (food, beverage, etc.)
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'in_stock' => $request->boolean('in_stock', true), // Default show only in stock
        ];

        $products = $this->productService->paginate(
            perPage: $request->integer('per_page', 15),
            filters: array_filter($filters)
        );

        // Load outlet for each product
        $products->getCollection()->load(['outlet', 'category']);

        return response()->json(
            ProductResource::collection($products)->response()->getData(true)
        );
    }

    /**
     * Display the specified product with full details.
     */
    public function show(string $identifier): JsonResponse
    {
        // Find by UUID or slug
        $product = Product::where('status', 'active')
            ->where(function ($q) use ($identifier) {
                $q->where('uuid', $identifier)
                    ->orWhere('slug', $identifier);
            })
            ->with(['outlet', 'category', 'variants', 'attributes.values', 'productType'])
            ->firstOrFail();

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Search products.
     */
    public function search(Request $request): JsonResponse
    {
        $filters = [
            'status' => 'active',
            'search' => $request->input('q'),
            'outlet_id' => $request->input('outlet_id'),
            'category_id' => $request->input('category_id'),
        ];

        $products = $this->productService->paginate(
            perPage: $request->integer('per_page', 10),
            filters: array_filter($filters)
        );

        $products->getCollection()->load(['outlet', 'category']);

        return response()->json(
            ProductResource::collection($products)->response()->getData(true)
        );
    }

    /**
     * Get featured products for home page.
     */
    public function featured(Request $request): JsonResponse
    {
        $products = Product::where('status', 'active')
            ->where('is_featured', true)
            ->when($request->input('outlet_id'), function ($q, $outletId) {
                $q->where('outlet_id', $outletId);
            })
            ->with(['outlet', 'category'])
            ->limit($request->integer('limit', 10))
            ->latest()
            ->get();

        return response()->json([
            'data' => ProductResource::collection($products),
        ]);
    }

    /**
     * Get new arrivals for home page.
     */
    public function newArrivals(Request $request): JsonResponse
    {
        $products = Product::where('status', 'active')
            ->when($request->input('outlet_id'), function ($q, $outletId) {
                $q->where('outlet_id', $outletId);
            })
            ->with(['outlet', 'category'])
            ->orderByDesc('created_at')
            ->limit($request->integer('limit', 10))
            ->get();

        return response()->json([
            'data' => ProductResource::collection($products),
        ]);
    }

    /**
     * Get on-sale products.
     */
    public function onSale(Request $request): JsonResponse
    {
        $products = Product::where('status', 'active')
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->when($request->input('outlet_id'), function ($q, $outletId) {
                $q->where('outlet_id', $outletId);
            })
            ->with(['outlet', 'category'])
            ->limit($request->integer('limit', 10))
            ->latest()
            ->get();

        return response()->json([
            'data' => ProductResource::collection($products),
        ]);
    }

    /**
     * Get related products.
     */
    public function related(string $identifier, Request $request): JsonResponse
    {
        $product = Product::where('status', 'active')
            ->where(function ($q) use ($identifier) {
                $q->where('uuid', $identifier)
                    ->orWhere('slug', $identifier);
            })
            ->firstOrFail();

        $related = $this->productService->getRelated(
            $product,
            $request->integer('limit', 4)
        );

        $related->load(['outlet', 'category']);

        return response()->json([
            'data' => ProductResource::collection($related),
        ]);
    }

    /**
     * Get product types for filtering.
     */
    public function types(): JsonResponse
    {
        $types = \Modules\Product\Models\ProductType::where('status', 'active')
            ->select('id', 'uuid', 'name', 'slug', 'description')
            ->get();

        return response()->json([
            'data' => $types,
        ]);
    }
}
