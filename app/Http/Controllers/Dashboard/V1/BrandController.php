<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Outlet\Models\Outlet;
use Modules\Product\Exports\BrandsExport;
use Modules\Product\Http\Requests\Dashboard\V1\Brand\StoreBrandRequest;
use Modules\Product\Http\Requests\Dashboard\V1\Brand\UpdateBrandRequest;
use Modules\Product\Http\Resources\BrandResource;
use Modules\Product\Models\Brand;
use Modules\Product\Services\BrandService;
use Momentum\Modal\Modal;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BrandController extends Controller
{
    public function __construct(
        protected BrandService $brandService
    ) {
    }

    /**
     * Display a listing of brands.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'is_active', 'outlet_id']);
        $perPage = $request->integer('per_page', 10);

        $brands = $this->brandService->paginate($perPage, $filters);
        $outlets = Outlet::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('product::dashboard/brand/Index', [
            'brands' => BrandResource::collection($brands)->response()->getData(true),
            'filters' => $filters,
            'stats' => $this->brandService->getStats(),
            'outlets' => $outlets,
        ]);
    }

    /**
     * Show the form for creating a new brand.
     */
    public function create(): Modal
    {
        $outlets = Outlet::select('id', 'name')->orderBy('name')->get();

        return Inertia::modal('product::dashboard/brand/Create', [
            'outlets' => $outlets,
        ])->baseRoute('product.brands.index');
    }

    /**
     * Store a newly created brand.
     */
    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $this->brandService->create($request->validated());

        return redirect()->route('product.brands.index')
            ->with('success', 'Brand created successfully.');
    }

    /**
     * Display the specified brand.
     */
    public function show(Brand $brand): Response
    {
        $brand->load('outlet')->loadCount('products');

        return Inertia::render('product::dashboard/brand/Show', [
            'brand' => (new BrandResource($brand))->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified brand.
     */
    public function edit(Brand $brand): Modal
    {
        $brand->load('outlet');
        $outlets = Outlet::select('id', 'name')->orderBy('name')->get();

        return Inertia::modal('product::dashboard/brand/Edit', [
            'brand' => (new BrandResource($brand))->resolve(),
            'outlets' => $outlets,
        ])->baseRoute('product.brands.index');
    }

    /**
     * Update the specified brand.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->brandService->update($brand, $request->validated());

        return redirect()->route('product.brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified brand.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        $this->brandService->delete($brand);

        return redirect()->route('product.brands.index')
            ->with('success', 'Brand deleted successfully.');
    }

    /**
     * Show bulk delete confirmation modal.
     */
    public function confirmBulkDelete(Request $request): Modal
    {
        $uuids = $request->input('uuids', []);

        $brands = Brand::whereIn('uuid', $uuids)
            ->withCount('products')
            ->get(['id', 'uuid', 'name']);

        return Inertia::modal('product::dashboard/brand/BulkDelete', [
            'brandItems' => $brands->map(fn ($b) => [
                'id' => $b->id,
                'uuid' => $b->uuid,
                'name' => $b->name,
                'products_count' => $b->products_count,
            ])->toArray(),
        ])->baseRoute('product.brands.index');
    }

    /**
     * Bulk delete brands.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'exists:brands,uuid'],
        ]);

        $deleted = Brand::whereIn('uuid', $request->uuids)->delete();
        $this->brandService->clearStatsCache();

        return redirect()->route('product.brands.index')
            ->with('success', "{$deleted} brand(s) deleted successfully.");
    }

    /**
     * Toggle brand status.
     */
    public function toggleStatus(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $this->brandService->updateStatus($brand, $validated['is_active']);

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    /**
     * Export brands to Excel.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['search', 'is_active', 'outlet_id']);
        $filename = 'brands_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new BrandsExport($filters), $filename);
    }

    /**
     * Display trash listing.
     */
    public function trash(Request $request): Response
    {
        $perPage = $request->integer('per_page', 10);
        $search = $request->input('search');

        $query = Brand::onlyTrashed()
            ->select(['id', 'uuid', 'name', 'deleted_at'])
            ->latest('deleted_at');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $trashItems = $query->paginate($perPage);

        return Inertia::render('product::dashboard/brand/Trash', [
            'trashItems' => [
                'data' => $trashItems->map(fn ($item) => [
                    'id' => $item->id,
                    'uuid' => $item->uuid,
                    'display_name' => $item->name,
                    'type' => 'brand',
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
                'entityLabel' => 'Brand',
                'entityLabelPlural' => 'Brands',
            ],
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Restore a trashed brand.
     */
    public function restore(string $uuid): RedirectResponse
    {
        $brand = Brand::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $brand->restore();
        $this->brandService->clearStatsCache();

        return redirect()->back()->with('success', 'Brand restored successfully.');
    }

    /**
     * Permanently delete a brand.
     */
    public function forceDelete(string $uuid): RedirectResponse
    {
        $brand = Brand::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $brand->forceDelete();
        $this->brandService->clearStatsCache();

        return redirect()->back()->with('success', 'Brand permanently deleted.');
    }

    /**
     * Empty all trash.
     */
    public function emptyTrash(): RedirectResponse
    {
        $deleted = Brand::onlyTrashed()->forceDelete();
        $this->brandService->clearStatsCache();

        return redirect()->back()->with('success', "{$deleted} brand(s) permanently deleted.");
    }

    /**
     * Bulk restore brands from trash.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $uuids = $request->input('uuids', []);

        if (empty($uuids)) {
            return redirect()->back()->with('error', 'No items selected for restore.');
        }

        $restored = Brand::onlyTrashed()->whereIn('uuid', $uuids)->restore();
        $this->brandService->clearStatsCache();

        return redirect()->back()->with('success', "{$restored} brand(s) restored successfully.");
    }

    /**
     * Bulk force delete brands from trash.
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $uuids = $request->input('uuids', []);

        if (empty($uuids)) {
            return redirect()->back()->with('error', 'No items selected for deletion.');
        }

        $deleted = Brand::onlyTrashed()->whereIn('uuid', $uuids)->forceDelete();
        $this->brandService->clearStatsCache();

        return redirect()->back()->with('success', "{$deleted} brand(s) permanently deleted.");
    }
}
