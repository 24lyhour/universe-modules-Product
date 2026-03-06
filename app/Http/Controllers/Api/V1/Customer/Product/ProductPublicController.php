<?php

namespace Modules\Product\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\Http\Resources\ProductResource;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductType;
use Modules\Product\Services\ProductService;

class ProductPublicController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => 'active',
            'search' => $request->input('search'),
            'category_id' => $request->input('category_id'),
            'outlet_id' => $request->input('outlet_id'),
            'product_type_id' => $request->input('product_type_id'),
            'product_type' => $request->input('product_type'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'in_stock' => $request->boolean('in_stock', true),
        ];

        $products = $this->productService->paginate(
            perPage: $request->integer('per_page', 15),
            filters: array_filter($filters)
        );

        $products->getCollection()->load(['outlet', 'category']);

        return response()->json(
            ProductResource::collection($products)->response()->getData(true)
        );
    }

    public function show(string $identifier): JsonResponse
    {
        $product = Product::where('status', 'active')
            ->where(fn($q) => $q->where('uuid', $identifier)->orWhere('slug', $identifier))
            ->with(['outlet', 'category', 'variants', 'attributes.values', 'productType'])
            ->firstOrFail();

        return response()->json(['data' => new ProductResource($product)]);
    }

    public function search(Request $request): JsonResponse
    {
        $filters = array_filter([
            'status' => 'active',
            'search' => $request->input('q'),
            'outlet_id' => $request->input('outlet_id'),
            'category_id' => $request->input('category_id'),
        ]);

        $products = $this->productService->paginate(
            perPage: $request->integer('per_page', 10),
            filters: $filters
        );

        $products->getCollection()->load(['outlet', 'category']);

        return response()->json(
            ProductResource::collection($products)->response()->getData(true)
        );
    }

    public function featured(Request $request): JsonResponse
    {
        $products = Product::where('status', 'active')
            ->where('is_featured', true)
            ->when($request->input('outlet_id'), fn($q, $id) => $q->where('outlet_id', $id))
            ->with(['outlet', 'category'])
            ->limit($request->integer('limit', 10))
            ->latest()
            ->get();

        return response()->json(['data' => ProductResource::collection($products)]);
    }

    public function newArrivals(Request $request): JsonResponse
    {
        $products = Product::where('status', 'active')
            ->when($request->input('outlet_id'), fn($q, $id) => $q->where('outlet_id', $id))
            ->with(['outlet', 'category'])
            ->orderByDesc('created_at')
            ->limit($request->integer('limit', 10))
            ->get();

        return response()->json(['data' => ProductResource::collection($products)]);
    }

    public function onSale(Request $request): JsonResponse
    {
        $products = Product::where('status', 'active')
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->when($request->input('outlet_id'), fn($q, $id) => $q->where('outlet_id', $id))
            ->with(['outlet', 'category'])
            ->limit($request->integer('limit', 10))
            ->latest()
            ->get();

        return response()->json(['data' => ProductResource::collection($products)]);
    }

    public function related(string $identifier, Request $request): JsonResponse
    {
        $product = Product::where('status', 'active')
            ->where(fn($q) => $q->where('uuid', $identifier)->orWhere('slug', $identifier))
            ->firstOrFail();

        $related = $this->productService->getRelated($product, $request->integer('limit', 4));
        $related->load(['outlet', 'category']);

        return response()->json(['data' => ProductResource::collection($related)]);
    }

    public function types(): JsonResponse
    {
        $types = ProductType::where('status', 'active')
            ->select('id', 'uuid', 'name', 'slug', 'description')
            ->get();

        return response()->json(['data' => $types]);
    }
}
