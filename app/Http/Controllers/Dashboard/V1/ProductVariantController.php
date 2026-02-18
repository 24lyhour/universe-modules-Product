<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Product\Actions\Dashboard\V1\CreateProductVariantAction;
use Modules\Product\Actions\Dashboard\V1\DeleteProductVariantAction;
use Modules\Product\Actions\Dashboard\V1\UpdateProductVariantAction;
use Modules\Product\Http\Requests\Dashboard\V1\Variant\StoreProductVariantRequest;
use Modules\Product\Http\Requests\Dashboard\V1\Variant\UpdateProductVariantRequest;
use Modules\Product\Http\Resources\ProductAttributeResource;
use Modules\Product\Http\Resources\ProductResource;
use Modules\Product\Http\Resources\ProductVariantResource;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductVariant;
use Modules\Product\Services\ProductAttributeService;
use Modules\Product\Services\ProductVariantService;

class ProductVariantController extends Controller
{
    public function __construct(
        protected ProductVariantService $variantService,
        protected ProductAttributeService $attributeService
    ) {}

    /**
     * Display a listing of variants for a product.
     */
    public function index(Request $request, Product $product)
    {
        $variants = $this->variantService->paginateForProduct(
            product: $product,
            perPage: $request->integer('per_page', 10)
        );

        $attributes = $this->attributeService->getActiveWithValues();

        return Inertia::render('product::dashboard/variant/Index', [
            'product' => (new ProductResource($product))->resolve(),
            'variants' => ProductVariantResource::collection($variants)->response()->getData(true),
            'attributes' => ProductAttributeResource::collection($attributes)->resolve(),
        ]);
    }

    /**
     * Show the form for creating a new variant.
     */
    public function create(Product $product)
    {
        $attributes = $this->attributeService->getActiveWithValues();

        return Inertia::render('product::dashboard/variant/Create', [
            'product' => (new ProductResource($product))->resolve(),
            'attributes' => ProductAttributeResource::collection($attributes)->resolve(),
        ]);
    }

    /**
     * Store a newly created variant.
     */
    public function store(
        StoreProductVariantRequest $request,
        Product $product,
        CreateProductVariantAction $action
    ) {
        $action->execute($product, $request->validated());

        return redirect()->route('dashboard.product.variants.index', $product)
            ->with('success', 'Variant created successfully.');
    }

    /**
     * Display the specified variant.
     */
    public function show(Product $product, ProductVariant $variant)
    {
        $variant = $this->variantService->getWithRelations($variant);

        return Inertia::render('product::dashboard/variant/Show', [
            'product' => (new ProductResource($product))->resolve(),
            'variant' => (new ProductVariantResource($variant))->resolve(),
        ]);
    }

    /**
     * Show the form for editing the variant.
     */
    public function edit(Product $product, ProductVariant $variant)
    {
        $variant->load('attributeValueRelations');
        $attributes = $this->attributeService->getActiveWithValues();

        return Inertia::render('product::dashboard/variant/Edit', [
            'product' => (new ProductResource($product))->resolve(),
            'variant' => (new ProductVariantResource($variant))->resolve(),
            'attributes' => ProductAttributeResource::collection($attributes)->resolve(),
        ]);
    }

    /**
     * Update the specified variant.
     */
    public function update(
        UpdateProductVariantRequest $request,
        Product $product,
        ProductVariant $variant,
        UpdateProductVariantAction $action
    ) {
        $action->execute($product, $variant, $request->validated());

        return redirect()->route('dashboard.product.variants.index', $product)
            ->with('success', 'Variant updated successfully.');
    }

    /**
     * Remove the specified variant.
     */
    public function destroy(
        Product $product,
        ProductVariant $variant,
        DeleteProductVariantAction $action
    ) {
        $action->execute($variant);

        return redirect()->route('dashboard.product.variants.index', $product)
            ->with('success', 'Variant deleted successfully.');
    }
}
