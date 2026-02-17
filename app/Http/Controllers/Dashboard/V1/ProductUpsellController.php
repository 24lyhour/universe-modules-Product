<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Product\Http\Resources\ProductUpsellResource;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductUpsell;
use Momentum\Modal\Modal;

class ProductUpsellController extends Controller
{
    /**
     * Display a listing of upsells for a product.
     */
    public function index(Product $product): Response
    {
        $upsells = $product->productUpsells()
            ->with('upsellProduct')
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get();

        $availableProducts = Product::select('id', 'name', 'price', 'sku', 'status')
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return Inertia::render('product::dashboard/upsell/Index', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
            ],
            'upsells' => ProductUpsellResource::collection($upsells)->resolve(),
            'availableProducts' => $availableProducts,
            'stats' => [
                'total' => $upsells->count(),
                'upsells' => $upsells->where('type', ProductUpsell::TYPE_UPSELL)->count(),
                'downsells' => $upsells->where('type', ProductUpsell::TYPE_DOWNSELL)->count(),
                'cross_sells' => $upsells->where('type', ProductUpsell::TYPE_CROSS_SELL)->count(),
                'active' => $upsells->where('is_active', true)->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new upsell.
     */
    public function create(Product $product): Modal
    {
        $existingIds = $product->productUpsells()->pluck('upsell_product_id')->toArray();

        $availableProducts = Product::select('id', 'name', 'price', 'sku', 'status')
            ->where('id', '!=', $product->id)
            ->whereNotIn('id', $existingIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return Inertia::modal('product::dashboard/upsell/Create', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
            ],
            'availableProducts' => $availableProducts,
        ])->baseRoute('dashboard.product.upsells.index', ['product' => $product->id]);
    }

    /**
     * Store a newly created upsell.
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'upsell_product_id' => ['required', 'integer', 'exists:products,id', 'different:product_id'],
            'type' => ['required', 'in:upsell,downsell,cross_sell'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        // Check for duplicate
        $exists = $product->productUpsells()
            ->where('upsell_product_id', $validated['upsell_product_id'])
            ->where('type', $validated['type'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'upsell_product_id' => 'This product is already added as ' . $validated['type'] . '.',
            ]);
        }

        $product->productUpsells()->create($validated);

        return redirect()->route('dashboard.product.upsells.index', $product)
            ->with('success', ucfirst($validated['type']) . ' added successfully.');
    }

    /**
     * Show the form for editing an upsell.
     */
    public function edit(Product $product, ProductUpsell $upsell): Modal
    {
        $upsell->load('upsellProduct');

        return Inertia::modal('product::dashboard/upsell/Edit', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
            ],
            'upsell' => (new ProductUpsellResource($upsell))->resolve(),
        ])->baseRoute('dashboard.product.upsells.index', ['product' => $product->id]);
    }

    /**
     * Update the specified upsell.
     */
    public function update(Request $request, Product $product, ProductUpsell $upsell)
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'in:upsell,downsell,cross_sell'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $upsell->update($validated);

        return redirect()->route('dashboard.product.upsells.index', $product)
            ->with('success', 'Upsell updated successfully.');
    }

    /**
     * Remove the specified upsell.
     */
    public function destroy(Product $product, ProductUpsell $upsell)
    {
        $type = $upsell->type_label;
        $upsell->delete();

        return redirect()->route('dashboard.product.upsells.index', $product)
            ->with('success', $type . ' removed successfully.');
    }

    /**
     * Toggle the active status.
     */
    public function toggleStatus(Product $product, ProductUpsell $upsell)
    {
        $upsell->update(['is_active' => !$upsell->is_active]);

        return redirect()->back()
            ->with('success', 'Status updated successfully.');
    }

    /**
     * Reorder upsells.
     */
    public function reorder(Request $request, Product $product)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:product_upsells,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['items'] as $item) {
            ProductUpsell::where('id', $item['id'])
                ->where('product_id', $product->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return redirect()->back()
            ->with('success', 'Order updated successfully.');
    }
}
