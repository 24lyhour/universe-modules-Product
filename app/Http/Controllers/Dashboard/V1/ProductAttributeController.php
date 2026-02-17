<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Modules\Product\Http\Resources\ProductAttributeResource;
use Modules\Product\Models\ProductAttribute;
use Modules\Product\Models\ProductAttributeValue;

class ProductAttributeController extends Controller
{
    /**
     * Display a listing of attributes.
     */
    public function index(Request $request)
    {
        $query = ProductAttribute::query()
            ->withCount('values');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $attributes = $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($request->get('per_page', 10));

        $stats = [
            'total' => ProductAttribute::count(),
            'active' => ProductAttribute::where('is_active', true)->count(),
            'inactive' => ProductAttribute::where('is_active', false)->count(),
        ];

        return Inertia::render('product::dashboard/attribute/Index', [
            'attributes' => ProductAttributeResource::collection($attributes)->response()->getData(true),
            'filters' => $request->only(['search', 'type', 'is_active']),
            'stats' => $stats,
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:select,color,button',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'values' => 'nullable|array',
            'values.*.value' => 'required|string|max:255',
            'values.*.label' => 'nullable|string|max:255',
            'values.*.color_code' => 'nullable|string|max:20',
            'values.*.price_adjustment' => 'nullable|numeric',
            'values.*.sort_order' => 'nullable|integer|min:0',
            'values.*.is_active' => 'boolean',
        ]);

        $attribute = ProductAttribute::create([
            'uuid' => Str::uuid(),
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
            'created_by' => auth()->id(),
        ]);

        // Create values if provided
        if (!empty($validated['values'])) {
            foreach ($validated['values'] as $index => $valueData) {
                ProductAttributeValue::create([
                    'uuid' => Str::uuid(),
                    'attribute_id' => $attribute->id,
                    'value' => $valueData['value'],
                    'label' => $valueData['label'] ?? $valueData['value'],
                    'color_code' => $valueData['color_code'] ?? null,
                    'price_adjustment' => $valueData['price_adjustment'] ?? 0,
                    'sort_order' => $valueData['sort_order'] ?? $index,
                    'is_active' => $valueData['is_active'] ?? true,
                ]);
            }
        }

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
            'attribute' => new ProductAttributeResource($attribute),
        ]);
    }

    /**
     * Show the form for editing the attribute.
     */
    public function edit(ProductAttribute $attribute)
    {
        $attribute->load('values');

        return Inertia::render('product::dashboard/attribute/Edit', [
            'attribute' => new ProductAttributeResource($attribute),
        ]);
    }

    /**
     * Update the specified attribute.
     */
    public function update(Request $request, ProductAttribute $attribute)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:select,color,button',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'values' => 'nullable|array',
            'values.*.id' => 'nullable|integer',
            'values.*.value' => 'required|string|max:255',
            'values.*.label' => 'nullable|string|max:255',
            'values.*.color_code' => 'nullable|string|max:20',
            'values.*.price_adjustment' => 'nullable|numeric',
            'values.*.sort_order' => 'nullable|integer|min:0',
            'values.*.is_active' => 'boolean',
        ]);

        $attribute->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
            'updated_by' => auth()->id(),
        ]);

        // Sync values
        if (isset($validated['values'])) {
            $existingIds = [];

            foreach ($validated['values'] as $index => $valueData) {
                if (!empty($valueData['id'])) {
                    // Update existing
                    $value = ProductAttributeValue::find($valueData['id']);
                    if ($value && $value->attribute_id === $attribute->id) {
                        $value->update([
                            'value' => $valueData['value'],
                            'label' => $valueData['label'] ?? $valueData['value'],
                            'color_code' => $valueData['color_code'] ?? null,
                            'price_adjustment' => $valueData['price_adjustment'] ?? 0,
                            'sort_order' => $valueData['sort_order'] ?? $index,
                            'is_active' => $valueData['is_active'] ?? true,
                        ]);
                        $existingIds[] = $value->id;
                    }
                } else {
                    // Create new
                    $value = ProductAttributeValue::create([
                        'uuid' => Str::uuid(),
                        'attribute_id' => $attribute->id,
                        'value' => $valueData['value'],
                        'label' => $valueData['label'] ?? $valueData['value'],
                        'color_code' => $valueData['color_code'] ?? null,
                        'price_adjustment' => $valueData['price_adjustment'] ?? 0,
                        'sort_order' => $valueData['sort_order'] ?? $index,
                        'is_active' => $valueData['is_active'] ?? true,
                    ]);
                    $existingIds[] = $value->id;
                }
            }

            // Delete removed values
            $attribute->values()->whereNotIn('id', $existingIds)->delete();
        }

        return redirect()->route('dashboard.product.attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    /**
     * Remove the specified attribute.
     */
    public function destroy(ProductAttribute $attribute)
    {
        // Delete associated values first
        $attribute->values()->delete();
        $attribute->delete();

        return redirect()->route('dashboard.product.attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    /**
     * Toggle attribute active status.
     */
    public function toggleStatus(ProductAttribute $attribute)
    {
        $attribute->update([
            'is_active' => !$attribute->is_active,
        ]);

        return back()->with('success', 'Attribute status updated.');
    }
}
