<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Product\Actions\Dashboard\V1\BulkDeleteProductAttributesAction;
use Modules\Product\Actions\Dashboard\V1\CreateProductAttributeAction;
use Modules\Product\Actions\Dashboard\V1\DeleteProductAttributeAction;
use Modules\Product\Actions\Dashboard\V1\UpdateProductAttributeAction;
use Modules\Product\Http\Requests\BulkDeleteProductAttributesRequest;
use Modules\Product\Http\Requests\Dashboard\V1\Attribute\StoreProductAttributeRequest;
use Modules\Product\Http\Requests\Dashboard\V1\Attribute\UpdateProductAttributeRequest;
use Modules\Product\Exports\ProductAttributesExport;
use Modules\Product\Http\Resources\ProductAttributeResource;
use Modules\Product\Models\ProductAttribute;
use Modules\Product\Services\ProductAttributeService;
use Momentum\Modal\Modal;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Response;

class ProductAttributeController extends Controller
{
    public function __construct(
        protected ProductAttributeService $attributeService
    ) {}

    /**
     * Display a listing of attributes.
     */
    public function index(Request $request): Response
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
     * Display trashed attributes.
     */
    public function trash(Request $request): Response
    {
        $perPage = $request->integer('per_page', 10);
        $search = $request->string('search')->toString();

        $attributes = $this->attributeService->getTrashed($perPage, $search ?: null);

        return Inertia::render('product::dashboard/attribute/Trash', [
            'attributes' => ProductAttributeResource::collection($attributes)->response()->getData(true),
            'filters' => $request->only(['search', 'per_page']),
            'stats' => $this->attributeService->getStats(),
        ]);
    }

    /**
     * Export attributes to Excel.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['search', 'type', 'is_active']);

        return Excel::download(
            new ProductAttributesExport($filters),
            'product-attributes-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Restore a trashed attribute.
     */
    public function restore(string $uuid): RedirectResponse
    {
        $attribute = ProductAttribute::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $this->attributeService->restore($attribute);

        return redirect()->back()
            ->with('success', 'Attribute restored successfully.');
    }

    /**
     * Force delete a trashed attribute.
     */
    public function forceDelete(string $uuid): RedirectResponse
    {
        $attribute = ProductAttribute::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $this->attributeService->forceDelete($attribute);

        return redirect()->back()
            ->with('success', 'Attribute permanently deleted.');
    }

    /**
     * Bulk restore attributes.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->attributeService->bulkRestore($validated['uuids']);

        return redirect()->route('dashboard.product.attributes.trash')
            ->with('success', "{$count} attribute(s) restored.");
    }

    /**
     * Bulk force delete attributes.
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->attributeService->bulkForceDelete($validated['uuids']);

        return redirect()->route('dashboard.product.attributes.trash')
            ->with('success', "{$count} attribute(s) permanently deleted.");
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(): RedirectResponse
    {
        $count = $this->attributeService->emptyTrash();

        return redirect()->route('dashboard.product.attributes.trash')
            ->with('success', "{$count} attribute(s) permanently deleted.");
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
    ): RedirectResponse {
        $action->execute($request->validated());
        $this->attributeService->clearStatsCache();

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
    ): RedirectResponse {
        $action->execute($attribute);
        $this->attributeService->clearStatsCache();

        return redirect()->route('dashboard.product.attributes.index')
            ->with('success', 'Attribute moved to trash.');
    }

    /**
     * Toggle attribute active status.
     */
    public function toggleStatus(ProductAttribute $attribute)
    {
        $this->attributeService->toggleStatus($attribute);

        return back()->with('success', 'Attribute status updated.');
    }

    /**
     * Show bulk delete confirmation modal.
     */
    public function confirmBulkDelete(Request $request): Modal
    {
        $uuids = $request->input('uuids', []);

        $attributes = ProductAttribute::whereIn('uuid', $uuids)
            ->withCount('values')
            ->get(['id', 'uuid', 'name', 'type']);

        return Inertia::modal('product::dashboard/attribute/BulkDelete', [
            'attributeItems' => $attributes->map(fn ($a) => [
                'id' => $a->id,
                'uuid' => $a->uuid,
                'name' => $a->name,
                'type' => $a->type,
                'values_count' => $a->values_count,
            ])->toArray(),
        ])->baseRoute('dashboard.product.attributes.index');
    }

    /**
     * Bulk delete attributes.
     */
    public function bulkDelete(BulkDeleteProductAttributesRequest $request, BulkDeleteProductAttributesAction $action): RedirectResponse
    {
        $result = $action->execute($request->validated('uuids'));

        $message = "{$result['deleted']} attribute(s) deleted successfully.";

        if ($result['failed'] > 0) {
            $message .= " {$result['failed']} attribute(s) could not be found.";
        }

        return redirect()->route('dashboard.product.attributes.index')
            ->with('success', $message);
    }
}
