<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Product\Actions\Dashboard\V1\CreateProductAddOnAction;
use Modules\Product\Actions\Dashboard\V1\DeleteProductAddOnAction;
use Modules\Product\Actions\Dashboard\V1\UpdateProductAddOnAction;
use Modules\Product\Http\Requests\Dashboard\V1\AddOn\StoreProductAddOnRequest;
use Modules\Product\Http\Requests\Dashboard\V1\AddOn\UpdateProductAddOnRequest;
use Modules\Product\Http\Resources\ProductAddOnResource;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAddOn;
use Momentum\Modal\Modal;

class ProductAddOnController extends Controller
{
    public function __construct(
        protected CreateProductAddOnAction $createAction,
        protected UpdateProductAddOnAction $updateAction,
        protected DeleteProductAddOnAction $deleteAction,
    ) {}

    /**
     * Display a listing of add-ons for a product.
     */
    public function index(Product $product): Response
    {
        $addOns = $product->addOns()
            ->with('addOnProduct')
            ->orderBy('sort_order')
            ->get();

        $availableProducts = Product::select('id', 'name', 'price', 'sku', 'status')
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return Inertia::render('product::dashboard/addOn/Index', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
            ],
            'addOns' => ProductAddOnResource::collection($addOns)->resolve(),
            'availableProducts' => $availableProducts,
            'stats' => [
                'total' => $addOns->count(),
                'required' => $addOns->where('is_required', true)->count(),
                'optional' => $addOns->where('is_required', false)->count(),
                'active' => $addOns->where('is_active', true)->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new add-on.
     */
    public function create(Product $product): Modal
    {
        $existingIds = $product->addOns()->pluck('add_on_product_id')->toArray();

        $availableProducts = Product::select('id', 'name', 'price', 'sku', 'status')
            ->where('id', '!=', $product->id)
            ->whereNotIn('id', $existingIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return Inertia::modal('product::dashboard/addOn/Create', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
            ],
            'availableProducts' => $availableProducts,
        ])->baseRoute('dashboard.product.addons.index', ['product' => $product->id]);
    }

    /**
     * Store a newly created add-on.
     */
    public function store(StoreProductAddOnRequest $request, Product $product)
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
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
            ],
            'addOn' => (new ProductAddOnResource($addon))->resolve(),
        ])->baseRoute('dashboard.product.addons.index', ['product' => $product->id]);
    }

    /**
     * Update the specified add-on.
     */
    public function update(UpdateProductAddOnRequest $request, Product $product, ProductAddOn $addon)
    {
        $this->updateAction->execute($addon, $request->validated());

        return redirect()->route('dashboard.product.addons.index', $product)
            ->with('success', 'Add-on updated successfully.');
    }

    /**
     * Remove the specified add-on.
     */
    public function destroy(Product $product, ProductAddOn $addon)
    {
        $this->deleteAction->execute($addon);

        return redirect()->route('dashboard.product.addons.index', $product)
            ->with('success', 'Add-on removed successfully.');
    }

    /**
     * Toggle the active status.
     */
    public function toggleStatus(Product $product, ProductAddOn $addon)
    {
        $addon->update(['is_active' => !$addon->is_active]);

        return redirect()->back()
            ->with('success', 'Status updated successfully.');
    }

    /**
     * Reorder add-ons.
     */
    public function reorder(Request $request, Product $product)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:product_add_ons,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['items'] as $item) {
            ProductAddOn::where('id', $item['id'])
                ->where('product_id', $product->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return redirect()->back()
            ->with('success', 'Order updated successfully.');
    }
}
