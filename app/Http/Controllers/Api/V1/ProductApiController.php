<?php

namespace Modules\Product\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\Actions\CreateProductAction;
use Modules\Product\Actions\DeleteProductAction;
use Modules\Product\Actions\UpdateProductAction;
use Modules\Product\Http\Requests\Api\V1\Product\StoreProductRequest;
use Modules\Product\Http\Requests\Api\V1\Product\UpdateProductRequest;
use Modules\Product\Http\Resources\ProductResource;
use Modules\Product\Models\Product;
use Modules\Product\Services\ProductService;

class ProductApiController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display a listing of products.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'status',
            'search',
            'category_id',
            'is_featured',
            'in_stock',
            'low_stock',
            'min_price',
            'max_price',
        ]);

        $products = $this->productService->paginate(
            perPage: $request->integer('per_page', 15),
            filters: $filters
        );

        return response()->json(
            ProductResource::collection($products)->response()->getData(true)
        );
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request, CreateProductAction $action): JsonResponse
    {
        $product = $action->execute($request->validated());

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => new ProductResource($product),
        ], 201);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('category');

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action): JsonResponse
    {
        $product = $action->execute($product, $request->validated());

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product, DeleteProductAction $action): JsonResponse
    {
        $action->execute($product);

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * Get product statistics.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => $this->productService->getStats(),
        ]);
    }

    /**
     * Search products.
     */
    public function search(Request $request): JsonResponse
    {
        $products = $this->productService->paginate(
            perPage: $request->integer('per_page', 15),
            filters: ['search' => $request->input('q')]
        );

        return response()->json(
            ProductResource::collection($products)->response()->getData(true)
        );
    }
}
