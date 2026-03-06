<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Outlet\Models\Outlet;
use Modules\Product\Exports\ProductTypesExport;
use Modules\Product\Http\Requests\Dashboard\V1\ProductType\StoreProductTypeRequest;
use Modules\Product\Http\Requests\Dashboard\V1\ProductType\UpdateProductTypeRequest;
use Modules\Product\Http\Resources\ProductTypeResource;
use Modules\Product\Models\ProductType;
use Modules\Product\Services\ProductTypeService;
use Momentum\Modal\Modal;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductTypeController extends Controller
{
    public function __construct(
        protected ProductTypeService $productTypeService
    ) {
        // Authorization is handled by 'auto.permission' middleware in routes
    }

    /**
     * Display a listing of product types.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'is_active', 'outlet_id']);
        $perPage = $request->integer('per_page', 10);

        $productTypes = $this->productTypeService->paginate($perPage, $filters);
        $outlets = Outlet::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('product::dashboard/productType/Index', [
            'productTypes' => ProductTypeResource::collection($productTypes)->response()->getData(true),
            'filters' => $filters,
            'stats' => $this->productTypeService->getStats(),
            'outlets' => $outlets,
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
        $productType->load('outlet')->loadCount('products');

        return Inertia::render('product::dashboard/productType/Show', [
            'productType' => (new ProductTypeResource($productType))->resolve(),
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
            'productType' => (new ProductTypeResource($productType))->resolve(),
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

    /**
     * Show bulk delete confirmation modal.
     */
    public function confirmBulkDelete(Request $request): Modal
    {
        $uuids = $request->input('uuids', []);

        $productTypes = ProductType::whereIn('uuid', $uuids)
            ->withCount('products')
            ->get(['id', 'uuid', 'name']);

        return Inertia::modal('product::dashboard/productType/BulkDelete', [
            'productTypeItems' => $productTypes->map(fn ($p) => [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'name' => $p->name,
                'products_count' => $p->products_count,
            ])->toArray(),
        ])->baseRoute('product.product-types.index');
    }

    /**
     * Bulk delete product types.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'exists:product_types,uuid'],
        ]);

        $deleted = ProductType::whereIn('uuid', $request->uuids)->delete();
        $this->productTypeService->clearStatsCache();

        return redirect()->route('product.product-types.index')
            ->with('success', "{$deleted} product type(s) deleted successfully.");
    }

    /**
     * Toggle product type status.
     */
    public function toggleStatus(Request $request, ProductType $productType): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $this->productTypeService->updateStatus($productType, $validated['is_active']);

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    /**
     * Export product types to Excel.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['search', 'is_active', 'outlet_id']);
        $filename = 'product_types_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new ProductTypesExport($filters), $filename);
    }

    /**
     * Display trash listing.
     */
    public function trash(Request $request): Response
    {
        $perPage = $request->integer('per_page', 10);
        $search = $request->input('search');

        $query = ProductType::onlyTrashed()
            ->select(['id', 'uuid', 'name', 'deleted_at'])
            ->latest('deleted_at');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $trashItems = $query->paginate($perPage);

        return Inertia::render('product::dashboard/productType/Trash', [
            'trashItems' => [
                'data' => $trashItems->map(fn ($item) => [
                    'id' => $item->id,
                    'uuid' => $item->uuid,
                    'display_name' => $item->name,
                    'type' => 'product_type',
                    'deleted_at' => $item->deleted_at?->toISOString(),
                ]),
                'meta' => [
                    'current_page' => $trashItems->currentPage(),
                    'last_page' => $trashItems->lastPage(),
                    'per_page' => $trashItems->perPage(),
                    'total' => $trashItems->total(),
                ],
            ],
            'config' => [
                'entityLabel' => 'Product Type',
                'entityLabelPlural' => 'Product Types',
            ],
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Restore a trashed product type.
     */
    public function restore(string $uuid): RedirectResponse
    {
        $productType = ProductType::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $productType->restore();
        $this->productTypeService->clearStatsCache();

        return redirect()->back()->with('success', 'Product type restored successfully.');
    }

    /**
     * Permanently delete a product type.
     */
    public function forceDelete(string $uuid): RedirectResponse
    {
        $productType = ProductType::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $productType->forceDelete();
        $this->productTypeService->clearStatsCache();

        return redirect()->back()->with('success', 'Product type permanently deleted.');
    }

    /**
     * Empty all trash.
     */
    public function emptyTrash(): RedirectResponse
    {
        $deleted = ProductType::onlyTrashed()->forceDelete();
        $this->productTypeService->clearStatsCache();

        return redirect()->back()->with('success', "{$deleted} product type(s) permanently deleted.");
    }

    /**
     * Bulk restore product types from trash.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $uuids = $request->input('uuids', []);

        if (empty($uuids)) {
            return redirect()->back()->with('error', 'No items selected for restore.');
        }

        $restored = ProductType::onlyTrashed()->whereIn('uuid', $uuids)->restore();
        $this->productTypeService->clearStatsCache();

        return redirect()->back()->with('success', "{$restored} product type(s) restored successfully.");
    }

    /**
     * Bulk force delete product types from trash.
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $uuids = $request->input('uuids', []);

        if (empty($uuids)) {
            return redirect()->back()->with('error', 'No items selected for deletion.');
        }

        $deleted = ProductType::onlyTrashed()->whereIn('uuid', $uuids)->forceDelete();
        $this->productTypeService->clearStatsCache();

        return redirect()->back()->with('success', "{$deleted} product type(s) permanently deleted.");
    }
}
