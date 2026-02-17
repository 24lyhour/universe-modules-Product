<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Product\Actions\Dashboard\V1\CreateProductAttributeAction;
use Modules\Product\Actions\Dashboard\V1\DeleteProductAttributeAction;
use Modules\Product\Actions\Dashboard\V1\UpdateProductAttributeAction;
use Modules\Product\Http\Requests\Dashboard\V1\Attribute\StoreProductAttributeRequest;
use Modules\Product\Http\Requests\Dashboard\V1\Attribute\UpdateProductAttributeRequest;
use Modules\Product\Http\Resources\ProductAttributeResource;
use Modules\Product\Models\ProductAttribute;
use Modules\Product\Services\ProductAttributeService;

class ProductAttributeController extends Controller
{
    public function __construct(
        protected ProductAttributeService $attributeService
    ) {}

    /**
     * Display a listing of attributes.
     */
    public function index(Request $request)
    {
        $attributes = $this->attributeService->paginate(
            perPage: $request->integer('per_page', 10),
            filters: $request->only(['search', 'type', 'is_active'])
        );

        return Inertia::render('product::dashboard/attribute/Index', [
            'attributes' => ProductAttributeResource::collection($attributes)->response()->getData(true),
            'filters' => $request->only(['search', 'type', 'is_active']),
            'stats' => $this->attributeService->getStats(),
        ]);
    }

    /**
     * Show the form for creating a new attribute.
     */
    public function create()
    {
        return Inertia::render('product::dashboard/attribute/Create');
    }

    /**
     * Store a newly created attribute.
     */
    public function store(
        StoreProductAttributeRequest $request,
        CreateProductAttributeAction $action
    ) {
        $action->execute($request->validated());

        return redirect()->route('dashboard.product.attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    /**
     * Display the specified attribute.
     */
    public function show(ProductAttribute $attribute)
    {
        $attribute->load('values');

        return Inertia::render('product::dashboard/attribute/Show', [
            'attribute' => (new ProductAttributeResource($attribute))->resolve(),
        ]);
    }

    /**
     * Show the form for editing the attribute.
     */
    public function edit(ProductAttribute $attribute)
    {
        $attribute->load('values');

        return Inertia::render('product::dashboard/attribute/Edit', [
            'attribute' => (new ProductAttributeResource($attribute))->resolve(),
        ]);
    }

    /**
     * Update the specified attribute.
     */
    public function update(
        UpdateProductAttributeRequest $request,
        ProductAttribute $attribute,
        UpdateProductAttributeAction $action
    ) {
        $action->execute($attribute, $request->validated());

        return redirect()->route('dashboard.product.attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    /**
     * Remove the specified attribute.
     */
    public function destroy(
        ProductAttribute $attribute,
        DeleteProductAttributeAction $action
    ) {
        $action->execute($attribute);

        return redirect()->route('dashboard.product.attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    /**
     * Toggle attribute active status.
     */
    public function toggleStatus(ProductAttribute $attribute)
    {
        $this->attributeService->toggleStatus($attribute);

        return back()->with('success', 'Attribute status updated.');
    }
}
