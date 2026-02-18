<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Product\Actions\Dashboard\V1\CreateProductAddOnAction;
use Modules\Product\Actions\Dashboard\V1\DeleteProductAddOnAction;
use Modules\Product\Actions\Dashboard\V1\ReorderProductAddOnsAction;
use Modules\Product\Actions\Dashboard\V1\ToggleProductAddOnStatusAction;
use Modules\Product\Actions\Dashboard\V1\UpdateProductAddOnAction;
use Modules\Product\Http\Requests\Dashboard\V1\AddOn\ReorderProductAddOnsRequest;
use Modules\Product\Http\Requests\Dashboard\V1\AddOn\StoreProductAddOnRequest;
use Modules\Product\Http\Requests\Dashboard\V1\AddOn\UpdateProductAddOnRequest;
use Modules\Product\Http\Resources\ProductAddOnResource;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAddOn;
use Modules\Product\Services\ProductAddOnService;
use Momentum\Modal\Modal;

class ProductAddOnController extends Controller
{
    public function __construct(
        protected ProductAddOnService $service,
        protected CreateProductAddOnAction $createAction,
        protected UpdateProductAddOnAction $updateAction,
        protected DeleteProductAddOnAction $deleteAction,
        protected ToggleProductAddOnStatusAction $toggleStatusAction,
        protected ReorderProductAddOnsAction $reorderAction,
    ) {}

    /**
     * Display all add-ons across all products.
     */
    public function indexAll(Request $request): Response
    {
        $perPage = $request->integer('per_page', 10);
        $search = $request->string('search')->toString();

        $addOns = $this->service->getPaginatedAddOns($perPage, $search ?: null);

        return Inertia::render('product::dashboard/addOn/Index', [
            'addOns' => ProductAddOnResource::collection($addOns)->response()->getData(true),
            'filters' => $request->only(['search', 'per_page']),
            'stats' => $this->service->getGlobalStats(),
        ]);
    }

    /**
     * Display a listing of add-ons for a product.
     */
    public function index(Product $product): Response
    {
        $addOns = $this->service->getProductAddOns($product);

        return Inertia::render('product::dashboard/addOn/ProductIndex', [
            'product' => $this->service->formatProductData($product),
            'addOns' => ProductAddOnResource::collection($addOns)->resolve(),
            'availableProducts' => $this->service->getAvailableProducts($product),
            'stats' => $this->service->getProductStats($addOns),
        ]);
    }

    /**
     * Show the form for creating a new add-on.
     */
    public function create(Product $product): Modal
    {
        return Inertia::modal('product::dashboard/addOn/Create', [
            'product' => $this->service->formatProductData($product),
            'availableProducts' => $this->service->getAvailableProducts($product, excludeExisting: true),
        ])->baseRoute('dashboard.product.addons.index', ['product' => $product->id]);
    }

    /**
     * Store a newly created add-on.
     */
    public function store(StoreProductAddOnRequest $request, Product $product): RedirectResponse
    {
        $this->createAction->execute($product, $request->validated());

        return redirect()->route('dashboard.product.addons.index', $product)
            ->with('success', 'Add-on created successfully.');
    }

    /**
     * Show the form for editing an add-on.
     */
    public function edit(Product $product, ProductAddOn $addon): Modal
    {
        $addon->load('addOnProduct');

        return Inertia::modal('product::dashboard/addOn/Edit', [
            'product' => $this->service->formatProductData($product),
            'addOn' => (new ProductAddOnResource($addon))->resolve(),
        ])->baseRoute('dashboard.product.addons.index', ['product' => $product->id]);
    }

    /**
     * Update the specified add-on.
     */
    public function update(UpdateProductAddOnRequest $request, Product $product, ProductAddOn $addon): RedirectResponse
    {
        $this->updateAction->execute($addon, $request->validated());

        return redirect()->route('dashboard.product.addons.index', $product)
            ->with('success', 'Add-on updated successfully.');
    }

    /**
     * Remove the specified add-on.
     */
    public function destroy(Product $product, ProductAddOn $addon): RedirectResponse
    {
        $this->deleteAction->execute($addon);

        return redirect()->route('dashboard.product.addons.index', $product)
            ->with('success', 'Add-on removed successfully.');
    }

    /**
     * Toggle the active status.
     */
    public function toggleStatus(Product $product, ProductAddOn $addon): RedirectResponse
    {
        $this->toggleStatusAction->execute($addon);

        return redirect()->back()
            ->with('success', 'Status updated successfully.');
    }

    /**
     * Reorder add-ons.
     */
    public function reorder(ReorderProductAddOnsRequest $request, Product $product): RedirectResponse
    {
        $this->reorderAction->execute($product, $request->validated()['items']);

        return redirect()->back()
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Show the standalone form for creating a new add-on (from global add-ons page).
     */
    public function createStandalone(): Modal
    {
        return Inertia::modal('product::dashboard/addOn/CreateStandalone', [
            'parentProducts' => $this->service->getActiveProducts(),
        ])->baseRoute('dashboard.product.addons.all');
    }

    /**
     * Store a newly created add-on from standalone form.
     */
    public function storeStandalone(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'price_adjustment' => ['required', 'numeric'],
            'max_quantity' => ['required', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_required' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $this->createAction->execute($product, $validated);

        return redirect()->route('dashboard.product.addons.all')
            ->with('success', 'Add-on created successfully.');
    }
}
