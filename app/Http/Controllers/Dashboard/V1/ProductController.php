<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Outlet\Models\Outlet;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use Modules\Product\Http\Resources\ProductAttributeResource;
use Modules\Product\Http\Resources\ProductResource;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAttribute;
use Modules\Product\Services\ProductService;
use Momentum\Modal\Modal;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'search', 'category_id', 'outlet_id', 'product_type', 'is_featured', 'in_stock', 'low_stock']);

        $products = $this->productService->paginate(
            perPage: $request->integer('per_page', 10),
            filters: $filters
        );

        $outlets = Outlet::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('product::dashboard/product/Index', [
            'products' => ProductResource::collection($products)->response()->getData(true),
            'filters' => $filters,
            'stats' => $this->productService->getStats(),
            'outlets' => $outlets,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Modal
    {
        $outlets = Outlet::select('id', 'name')->orderBy('name')->get();
        $products = Product::select('id', 'name', 'price', 'sku')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return Inertia::modal('product::dashboard/product/Create', [
            'outlets' => $outlets,
            'products' => $products,
        ])->baseRoute('product.products.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $this->productService->create($request->validated());

        return redirect()->route('product.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): Response
    {
        $product->load([
            'outlet',
            'upsell',
            'downsell',
            'variants.attributeValueRelations.attribute',
            'attributes.values',
        ]);

        return Inertia::render('product::dashboard/product/Show', [
            'product' => (new ProductResource($product))->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): Modal
    {
        $product->load(['upsell', 'downsell']);

        $outlets = Outlet::select('id', 'name')->orderBy('name')->get();
        $products = Product::select('id', 'name', 'price', 'sku')
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->orderBy('name')
            ->get();

        return Inertia::modal('product::dashboard/product/Edit', [
            'product' => (new ProductResource($product))->resolve(),
            'outlets' => $outlets,
            'products' => $products,
        ])->baseRoute('product.products.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->productService->update($product, $request->validated());

        return redirect()->route('product.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->productService->delete($product);

        return redirect()->route('product.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Product $product)
    {
        $this->productService->toggleFeatured($product);

        return redirect()->back()
            ->with('success', 'Product featured status updated.');
    }

    /**
     * Update product status.
     */
    public function updateStatus(Request $request, Product $product)
    {
        $request->validate([
            'status' => ['required', 'in:active,inactive,draft,out_of_stock'],
        ]);

        $this->productService->updateStatus($product, $request->status);

        return redirect()->back()
            ->with('success', 'Product status updated.');
    }

    /**
     * Show modal for managing product attributes.
     */
    public function manageAttributes(Product $product): Modal
    {
        $product->load('attributes');

        $allAttributes = ProductAttribute::with('values')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $assignedAttributeIds = $product->attributes->pluck('id')->toArray();

        return Inertia::modal('product::dashboard/product/ManageAttributes', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
            'allAttributes' => ProductAttributeResource::collection($allAttributes)->resolve(),
            'assignedAttributeIds' => $assignedAttributeIds,
        ])->baseRoute('product.products.show', ['product' => $product->id]);
    }

    /**
     * Sync attributes for a product.
     */
    public function syncAttributes(Request $request, Product $product)
    {
        $request->validate([
            'attribute_ids' => ['array'],
            'attribute_ids.*' => ['integer', 'exists:product_attributes,id'],
        ]);

        $syncData = [];
        foreach ($request->attribute_ids ?? [] as $index => $attributeId) {
            $syncData[$attributeId] = ['sort_order' => $index];
        }

        $product->attributes()->sync($syncData);

        return redirect()->route('product.products.show', $product)
            ->with('success', 'Product attributes updated successfully.');
    }
}
