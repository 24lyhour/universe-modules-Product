<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Outlet\Models\Outlet;
use Modules\Product\Http\Requests\Dashboard\V1\ProductType\StoreProductTypeRequest;
use Modules\Product\Http\Requests\Dashboard\V1\ProductType\UpdateProductTypeRequest;
use Modules\Product\Http\Resources\ProductTypeResource;
use Modules\Product\Models\ProductType;
use Modules\Product\Services\ProductTypeService;
use Momentum\Modal\Modal;

class ProductTypeController extends Controller
{
    public function __construct(
        protected ProductTypeService $productTypeService
    ) {}

    /**
     * Display a listing of product types.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'is_active', 'outlet_id']);
        $perPage = $request->integer('per_page', 10);

        $productTypes = $this->productTypeService->paginate($perPage, $filters);

        return Inertia::render('product::dashboard/productType/Index', [
            'productTypes' => ProductTypeResource::collection($productTypes)->response()->getData(true),
            'filters' => $filters,
            'stats' => $this->productTypeService->getStats(),
        ]);
    }

    /**
     * Show the form for creating a new product type.
     */
    public function create(): Modal
    {
        $outlets = Outlet::select('id', 'name')->orderBy('name')->get();

        return Inertia::modal('product::dashboard/productType/Create', [
            'outlets' => $outlets,
        ])->baseRoute('product.product-types.index');
    }

    /**
     * Store a newly created product type.
     */
    public function store(StoreProductTypeRequest $request): RedirectResponse
    {
        $this->productTypeService->create($request->validated());

        return redirect()->route('product.product-types.index')
            ->with('success', 'Product type created successfully.');
    }

    /**
     * Display the specified product type.
     */
    public function show(ProductType $productType): Response
    {
        $productType->load('outlet');

        return Inertia::render('product::dashboard/productType/Show', [
            'productType' => new ProductTypeResource($productType),
        ]);
    }

    /**
     * Show the form for editing the specified product type.
     */
    public function edit(ProductType $productType): Modal
    {
        $productType->load('outlet');
        $outlets = Outlet::select('id', 'name')->orderBy('name')->get();

        return Inertia::modal('product::dashboard/productType/Edit', [
            'productType' => new ProductTypeResource($productType),
            'outlets' => $outlets,
        ])->baseRoute('product.product-types.index');
    }

    /**
     * Update the specified product type.
     */
    public function update(UpdateProductTypeRequest $request, ProductType $productType): RedirectResponse
    {
        $this->productTypeService->update($productType, $request->validated());

        return redirect()->route('product.product-types.index')
            ->with('success', 'Product type updated successfully.');
    }

    /**
     * Remove the specified product type.
     */
    public function destroy(ProductType $productType): RedirectResponse
    {
        $this->productTypeService->delete($productType);

        return redirect()->route('product.product-types.index')
            ->with('success', 'Product type deleted successfully.');
    }
}
