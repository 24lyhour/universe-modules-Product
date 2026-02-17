<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Modules\Product\Http\Resources\ProductAttributeResource;
use Modules\Product\Http\Resources\ProductResource;
use Modules\Product\Http\Resources\ProductVariantResource;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAttribute;
use Modules\Product\Models\ProductVariant;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of variants for a product.
     */
    public function index(Request $request, Product $product)
    {
        $variants = $product->variants()
            ->with('attributeValueRelations.attribute')
            ->orderBy('sort_order')
            ->paginate($request->get('per_page', 10));

        $attributes = ProductAttribute::with('values')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('product::dashboard/variant/Index', [
            'product' => new ProductResource($product),
            'variants' => ProductVariantResource::collection($variants)->response()->getData(true),
            'attributes' => ProductAttributeResource::collection($attributes),
        ]);
    }

    /**
     * Show the form for creating a new variant.
     */
    public function create(Product $product)
    {
        $attributes = ProductAttribute::with('values')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('product::dashboard/variant/Create', [
            'product' => new ProductResource($product),
            'attributes' => ProductAttributeResource::collection($attributes),
        ]);
    }

    /**
     * Store a newly created variant.
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'nullable|string|max:255|unique:product_variants,sku',
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'attribute_value_ids' => 'required|array|min:1',
            'attribute_value_ids.*' => 'integer|exists:product_attribute_values,id',
        ]);

        // If this is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            $product->variants()->update(['is_default' => false]);
        }

        $variant = ProductVariant::create([
            'uuid' => Str::uuid(),
            'product_id' => $product->id,
            'sku' => $validated['sku'] ?? null,
            'name' => $validated['name'] ?? null,
            'price' => $validated['price'] ?? null,
            'purchase_price' => $validated['purchase_price'] ?? null,
            'sale_price' => $validated['sale_price'] ?? null,
            'stock' => $validated['stock'],
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
            'barcode' => $validated['barcode'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'images' => $validated['images'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        // Attach attribute values
        $variant->attributeValueRelations()->sync($validated['attribute_value_ids']);

        return redirect()->route('dashboard.product.variants.index', $product)
            ->with('success', 'Variant created successfully.');
    }

    /**
     * Display the specified variant.
     */
    public function show(Product $product, ProductVariant $variant)
    {
        $variant->load('attributeValueRelations.attribute');

        return Inertia::render('product::dashboard/variant/Show', [
            'product' => new ProductResource($product),
            'variant' => new ProductVariantResource($variant),
        ]);
    }

    /**
     * Show the form for editing the variant.
     */
    public function edit(Product $product, ProductVariant $variant)
    {
        $variant->load('attributeValueRelations');

        $attributes = ProductAttribute::with('values')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('product::dashboard/variant/Edit', [
            'product' => new ProductResource($product),
            'variant' => new ProductVariantResource($variant),
            'attributes' => ProductAttributeResource::collection($attributes),
        ]);
    }

    /**
     * Update the specified variant.
     */
    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        $validated = $request->validate([
            'sku' => 'nullable|string|max:255|unique:product_variants,sku,' . $variant->id,
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'attribute_value_ids' => 'required|array|min:1',
            'attribute_value_ids.*' => 'integer|exists:product_attribute_values,id',
        ]);

        // If this is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            $product->variants()->where('id', '!=', $variant->id)->update(['is_default' => false]);
        }

        $variant->update([
            'sku' => $validated['sku'] ?? null,
            'name' => $validated['name'] ?? null,
            'price' => $validated['price'] ?? null,
            'purchase_price' => $validated['purchase_price'] ?? null,
            'sale_price' => $validated['sale_price'] ?? null,
            'stock' => $validated['stock'],
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
            'barcode' => $validated['barcode'] ?? null,
            'weight' => $validated['weight'] ?? null,
            'images' => $validated['images'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        // Sync attribute values
        $variant->attributeValueRelations()->sync($validated['attribute_value_ids']);

        return redirect()->route('dashboard.product.variants.index', $product)
            ->with('success', 'Variant updated successfully.');
    }

    /**
     * Remove the specified variant.
     */
    public function destroy(Product $product, ProductVariant $variant)
    {
        $variant->attributeValueRelations()->detach();
        $variant->delete();

        return redirect()->route('dashboard.product.variants.index', $product)
            ->with('success', 'Variant deleted successfully.');
    }
}
