<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Product\Actions\Dashboard\V1\BulkDeleteProductsAction;
use Modules\Product\Actions\Dashboard\V1\UpdateProductSettingsAction;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Menu\Models\Category;
use Modules\Outlet\Models\Outlet;
use Modules\Product\Http\Requests\BulkDeleteProductsRequest;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use Modules\Product\Http\Resources\ProductAttributeResource;
use Modules\Product\Http\Resources\ProductResource;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAttribute;
use Modules\Product\Models\ProductType;
use Modules\Product\Exports\ProductsExport;
use Modules\Product\Imports\ProductsImport;
use Modules\Product\Services\ProductService;
use Maatwebsite\Excel\Facades\Excel;
use Momentum\Modal\Modal;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {
        // Authorization is handled by 'auto.permission' middleware in routes
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'search', 'category_id', 'outlet_id', 'product_type', 'product_type_id', 'is_featured', 'in_stock', 'low_stock']);

        $products = $this->productService->paginate(
            perPage: $request->integer('per_page', 10),
            filters: $filters
        );

        $outlets = Outlet::select('id', 'name')->orderBy('name')->get();
        $categories = Category::select('id', 'name')
            ->where('status', true)
            ->orderBy('name')
            ->get();
        $productTypes = ProductType::select('id', 'name', 'slug')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('product::dashboard/product/Index', [
            'products' => ProductResource::collection($products)->response()->getData(true),
            'filters' => $filters,
            'stats' => $this->productService->getStats(),
            'outlets' => $outlets,
            'categories' => $categories,
            'productTypes' => $productTypes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Modal
    {
        $outlets = Outlet::select('id', 'name')->orderBy('name')->get();
        $products = Product::select('id', 'name', 'price', 'sku')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        $categories = Category::select('id', 'name', 'product_type')
            ->where('status', true)
            ->orderBy('name')
            ->get();
        $productTypes = ProductType::select('id', 'name', 'slug')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::modal('product::dashboard/product/Create', [
            'outlets' => $outlets,
            'products' => $products,
            'categories' => $categories,
            'productTypes' => $productTypes,
            'productSettings' => UpdateProductSettingsAction::getSettings(),
        ])->baseRoute('product.products.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $this->productService->create($request->validated());

        return redirect()->route('product.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): Response
    {
        $product->load([
            'outlet',
            'upsell',
            'downsell',
            'variants.attributeValueRelations.attribute',
            'attributes.values',
        ]);

        return Inertia::render('product::dashboard/product/Show', [
            'product' => (new ProductResource($product))->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): Modal
    {
        $product->load(['upsell', 'downsell', 'productType']);

        $outlets = Outlet::select('id', 'name')->orderBy('name')->get();
        $products = Product::select('id', 'name', 'price', 'sku')
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->orderBy('name')
            ->get();
        $categories = Category::select('id', 'name', 'product_type')
            ->where('status', true)
            ->orderBy('name')
            ->get();
        $productTypes = ProductType::select('id', 'name', 'slug')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::modal('product::dashboard/product/Edit', [
            'product' => (new ProductResource($product))->resolve(),
            'outlets' => $outlets,
            'products' => $products,
            'categories' => $categories,
            'productTypes' => $productTypes,
        ])->baseRoute('product.products.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->productService->update($product, $request->validated());

        return redirect()->route('product.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Show delete confirmation modal.
     */
    public function confirmDelete(Product $product): Modal
    {
        return Inertia::modal('product::dashboard/product/Delete', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
        ])->baseRoute('product.products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->productService->delete($product);

        return redirect()->route('product.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Product $product)
    {
        $this->productService->toggleFeatured($product);

        return redirect()->back()
            ->with('success', 'Product featured status updated.');
    }

    /**
     * Update product status.
     */
    public function updateStatus(Request $request, Product $product)
    {
        $request->validate([
            'status' => ['required', 'in:active,inactive,draft,out_of_stock'],
        ]);

        $this->productService->updateStatus($product, $request->status);

        return redirect()->back()
            ->with('success', 'Product status updated.');
    }

    /**
     * Show modal for managing product attributes.
     */
    public function manageAttributes(Product $product): Modal
    {
        $product->load('attributes');

        $allAttributes = ProductAttribute::with('values')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $assignedAttributeIds = $product->attributes->pluck('id')->toArray();

        return Inertia::modal('product::dashboard/product/ManageAttributes', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
            'allAttributes' => ProductAttributeResource::collection($allAttributes)->resolve(),
            'assignedAttributeIds' => $assignedAttributeIds,
        ])->baseRoute('product.products.show', ['product' => $product->id]);
    }

    /**
     * Sync attributes for a product.
     */
    public function syncAttributes(Request $request, Product $product)
    {
        $request->validate([
            'attribute_ids' => ['array'],
            'attribute_ids.*' => ['integer', 'exists:product_attributes,id'],
        ]);

        $syncData = [];
        foreach ($request->attribute_ids ?? [] as $index => $attributeId) {
            $syncData[$attributeId] = ['sort_order' => $index];
        }

        $product->attributes()->sync($syncData);

        return redirect()->route('product.products.show', $product)
            ->with('success', 'Product attributes updated successfully.');
    }

    /**
     * Show bulk delete confirmation modal.
     */
    public function confirmBulkDelete(Request $request): Modal
    {
        $uuids = $request->input('uuids', []);

        $products = Product::whereIn('uuid', $uuids)
            ->withCount('variants')
            ->get(['id', 'uuid', 'name', 'sku', 'status']);

        return Inertia::modal('product::dashboard/product/BulkDelete', [
            'productItems' => $products->map(fn ($p) => [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'name' => $p->name,
                'sku' => $p->sku,
                'status' => $p->status,
                'variants_count' => $p->variants_count,
            ])->toArray(),
        ])->baseRoute('product.products.index');
    }

    /**
     * Bulk delete products.
     */
    public function bulkDelete(BulkDeleteProductsRequest $request, BulkDeleteProductsAction $action): RedirectResponse
    {
        $result = $action->execute($request->validated('uuids'));

        $message = "{$result['deleted']} product(s) deleted successfully.";

        if ($result['failed'] > 0) {
            $message .= " {$result['failed']} product(s) could not be found.";
        }

        return redirect()->route('product.products.index')
            ->with('success', $message);
    }

    /**
     * Display trash listing.
     */
    public function trash(Request $request): Response
    {
        $perPage = $request->integer('per_page', 10);
        $search = $request->input('search');

        $query = Product::onlyTrashed()
            ->select(['id', 'uuid', 'name', 'deleted_at'])
            ->latest('deleted_at');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $trashItems = $query->paginate($perPage);

        return Inertia::render('product::dashboard/product/Trash', [
            'trashItems' => [
                'data' => $trashItems->map(fn ($item) => [
                    'id' => $item->id,
                    'uuid' => $item->uuid,
                    'display_name' => $item->name,
                    'type' => 'product',
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
                'entityLabel' => 'Product',
                'entityLabelPlural' => 'Products',
            ],
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Restore a trashed product.
     */
    public function restore(string $uuid): RedirectResponse
    {
        $product = Product::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $product->restore();

        return redirect()->back()->with('success', 'Product restored successfully.');
    }

    /**
     * Permanently delete a product.
     */
    public function forceDelete(string $uuid): RedirectResponse
    {
        $product = Product::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $product->forceDelete();

        return redirect()->back()->with('success', 'Product permanently deleted.');
    }

    /**
     * Empty all trash.
     */
    public function emptyTrash(): RedirectResponse
    {
        $deleted = Product::onlyTrashed()->forceDelete();

        return redirect()->back()->with('success', "{$deleted} product(s) permanently deleted.");
    }

    /**
     * Bulk restore products from trash.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $uuids = $request->input('uuids', []);

        if (empty($uuids)) {
            return redirect()->back()->with('error', 'No items selected for restore.');
        }

        $restored = Product::onlyTrashed()->whereIn('uuid', $uuids)->restore();

        return redirect()->back()->with('success', "{$restored} product(s) restored successfully.");
    }

    /**
     * Bulk force delete products from trash.
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $uuids = $request->input('uuids', []);

        if (empty($uuids)) {
            return redirect()->back()->with('error', 'No items selected for deletion.');
        }

        $deleted = Product::onlyTrashed()->whereIn('uuid', $uuids)->forceDelete();

        return redirect()->back()->with('success', "{$deleted} product(s) permanently deleted.");
    }

    protected array $duplicateOptions = [
        ['value' => 'skip', 'label' => 'Skip duplicates', 'description' => 'Skip rows with existing SKU'],
        ['value' => 'update', 'label' => 'Update existing', 'description' => 'Update existing products with new data'],
        ['value' => 'fail', 'label' => 'Fail on duplicate', 'description' => 'Stop import if duplicates found'],
    ];

    /**
     * Show import page.
     */
    public function import(): Response
    {
        return Inertia::render('product::dashboard/product/Import', [
            'duplicateOptions' => $this->duplicateOptions,
        ]);
    }

    /**
     * Preview import file.
     */
    public function previewImport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $duplicateHandling = $request->input('duplicate_handling', 'skip');

            $import = new ProductsImport($duplicateHandling, true);
            Excel::import($import, $file);

            $previewData = $import->getPreviewData();
            $results = $import->getResults();

            return response()->json([
                'success' => true,
                'preview' => $previewData,
                'stats' => $results['preview_stats'],
                'total_rows' => count($previewData),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Process import file.
     */
    public function processImport(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $duplicateHandling = $request->input('duplicate_handling', 'skip');

            $import = new ProductsImport($duplicateHandling, false);
            Excel::import($import, $file);

            $results = $import->getResults();

            $messages = [];
            if ($results['imported'] > 0) $messages[] = "{$results['imported']} imported";
            if ($results['updated'] > 0) $messages[] = "{$results['updated']} updated";
            if ($results['skipped'] > 0) $messages[] = "{$results['skipped']} skipped";
            if ($results['failed'] > 0) $messages[] = "{$results['failed']} failed";

            $message = 'Import completed: ' . implode(', ', $messages);

            if ($results['failed'] > 0) {
                session()->flash('import_failed_rows', $results['failed_rows']);
                return redirect()->route('product.products.index')->with('warning', $message);
            }

            return redirect()->route('product.products.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('product.products.import')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Export products to Excel.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['search', 'status', 'outlet_id']);
        $filename = 'products_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new ProductsExport($filters), $filename);
    }

    /**
     * Download import template.
     */
    public function template(): BinaryFileResponse
    {
        $headers = ['Name', 'SKU', 'Description', 'Price', 'Purchase Price', 'Sale Price', 'Stock', 'Low Stock Threshold', 'Status'];
        $sampleData = ['Sample Product', 'SKU-001', 'Product description here', '99.99', '50.00', '89.99', '100', '10', 'active'];
        $instructions = [
            'Name and Price are required fields',
            'SKU is used for duplicate detection',
            'If SKU matches existing product, it will be updated based on duplicate handling setting',
            'Status: active, inactive, draft, or out_of_stock (defaults to draft)',
            'Stock and Low Stock Threshold: integer values',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products Import Template');

        // Add headers
        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
        }

        // Style header row
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ]);

        // Auto-size columns
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add sample data
        foreach ($sampleData as $index => $value) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '2', $value);
        }
        $sheet->getStyle('A2:' . $lastColumn . '2')->getFont()->setItalic(true);

        // Add instructions sheet
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instructions');
        $instructionsSheet->setCellValue('A1', 'Import Instructions');
        $instructionsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $row = 3;
        foreach ($instructions as $index => $instruction) {
            $instructionsSheet->setCellValue('A' . $row, ($index + 1) . '. ' . $instruction);
            $row++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        $tempFile = tempnam(sys_get_temp_dir(), 'products_template_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, 'products_import_template.xlsx')->deleteFileAfterSend(true);
    }
}
