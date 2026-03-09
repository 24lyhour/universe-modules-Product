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
use Modules\Product\Exports\ProductAddOnsExport;
use Modules\Product\Imports\ProductAddOnsImport;
use Modules\Product\Http\Resources\ProductAddOnResource;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAddOn;
use Modules\Product\Services\ProductAddOnService;
use Momentum\Modal\Modal;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;

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
        $status = $request->string('status')->toString();

        $addOns = $this->service->getPaginatedAddOns($perPage, $search ?: null, $status ?: null);

        return Inertia::render('product::dashboard/addOn/Index', [
            'addOns' => ProductAddOnResource::collection($addOns)->response()->getData(true),
            'filters' => $request->only(['search', 'per_page', 'status']),
            'stats' => $this->service->getGlobalStats(),
        ]);
    }

    /**
     * Display trashed add-ons.
     */
    public function trash(Request $request): Response
    {
        $perPage = $request->integer('per_page', 10);
        $search = $request->string('search')->toString();

        $addOns = $this->service->getTrashedAddOns($perPage, $search ?: null);

        return Inertia::render('product::dashboard/addOn/Trash', [
            'addOns' => ProductAddOnResource::collection($addOns)->response()->getData(true),
            'filters' => $request->only(['search', 'per_page']),
            'stats' => $this->service->getGlobalStats(),
        ]);
    }

    /**
     * Export add-ons to Excel.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        return Excel::download(
            new ProductAddOnsExport($search ?: null, $status ?: null),
            'product-add-ons-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    protected array $duplicateOptions = [
        ['value' => 'skip', 'label' => 'Skip duplicates', 'description' => 'Skip rows with existing add-on name for product'],
        ['value' => 'update', 'label' => 'Update existing', 'description' => 'Update existing add-ons with new data'],
        ['value' => 'fail', 'label' => 'Fail on duplicate', 'description' => 'Stop import if duplicates found'],
    ];

    /**
     * Show import page.
     */
    public function import(): Response
    {
        return Inertia::render('product::dashboard/addOn/Import', [
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

            $import = new ProductAddOnsImport($duplicateHandling, true);
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

            $import = new ProductAddOnsImport($duplicateHandling, false);
            Excel::import($import, $file);

            $results = $import->getResults();

            $messages = [];
            if ($results['imported'] > 0) $messages[] = "{$results['imported']} imported";
            if ($results['updated'] > 0) $messages[] = "{$results['updated']} updated";
            if ($results['skipped'] > 0) $messages[] = "{$results['skipped']} skipped";
            if ($results['failed'] > 0) $messages[] = "{$results['failed']} failed";

            $message = 'Import completed: ' . implode(', ', $messages);

            $this->service->clearStatsCache();

            if ($results['failed'] > 0) {
                session()->flash('import_failed_rows', $results['failed_rows']);
                return redirect()->route('dashboard.product.addons.all')->with('warning', $message);
            }

            return redirect()->route('dashboard.product.addons.all')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('dashboard.product.addons.import')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download import template.
     */
    public function template(): BinaryFileResponse
    {
        $headers = ['Name', 'Product SKU', 'Description', 'Price Adjustment', 'Max Quantity', 'Sort Order', 'Is Required', 'Is Active', 'Add-on Product SKU'];
        $sampleData = ['Extra Cheese', 'PIZZA-001', 'Add extra cheese topping', '2.50', '5', '1', 'false', 'true', ''];
        $instructions = [
            'Name, Product SKU, and Price Adjustment are required fields',
            'Product SKU must match an existing product in the system',
            'Name + Product SKU combination is used for duplicate detection',
            'Is Required: true/false, yes/no, 1/0 (defaults to false)',
            'Is Active: true/false, yes/no, 1/0 (defaults to true)',
            'Max Quantity: integer value (defaults to 10)',
            'Add-on Product SKU: optional, links to another product as the add-on item',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Add-ons Import Template');

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

        foreach ($instructions as $i => $instruction) {
            $instructionsSheet->setCellValue('A' . ($i + 3), ($i + 1) . '. ' . $instruction);
        }
        $instructionsSheet->getColumnDimension('A')->setWidth(80);

        // Set active sheet back to data
        $spreadsheet->setActiveSheetIndex(0);

        // Create temp file and download
        $filename = 'product-add-ons-template.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Bulk delete add-ons.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->service->bulkDelete($validated['uuids']);

        return redirect()->route('dashboard.product.addons.all')
            ->with('success', "{$count} add-on(s) moved to trash.");
    }

    /**
     * Restore a trashed add-on.
     */
    public function restore(string $uuid): RedirectResponse
    {
        $addOn = ProductAddOn::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $this->service->restore($addOn);

        return redirect()->back()
            ->with('success', 'Add-on restored successfully.');
    }

    /**
     * Force delete a trashed add-on.
     */
    public function forceDelete(string $uuid): RedirectResponse
    {
        $addOn = ProductAddOn::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $this->service->forceDelete($addOn);

        return redirect()->back()
            ->with('success', 'Add-on permanently deleted.');
    }

    /**
     * Bulk restore add-ons.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->service->bulkRestore($validated['uuids']);

        return redirect()->route('dashboard.product.addons.trash')
            ->with('success', "{$count} add-on(s) restored.");
    }

    /**
     * Bulk force delete add-ons.
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->service->bulkForceDelete($validated['uuids']);

        return redirect()->route('dashboard.product.addons.trash')
            ->with('success', "{$count} add-on(s) permanently deleted.");
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(): RedirectResponse
    {
        $count = $this->service->emptyTrash();

        return redirect()->route('dashboard.product.addons.trash')
            ->with('success', "{$count} add-on(s) permanently deleted.");
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
        $this->service->clearStatsCache();

        return redirect()->route('dashboard.product.addons.index', $product)
            ->with('success', 'Add-on moved to trash.');
    }

    /**
     * Remove add-on from global list.
     */
    public function destroyGlobal(ProductAddOn $addon): RedirectResponse
    {
        $this->deleteAction->execute($addon);
        $this->service->clearStatsCache();

        return redirect()->route('dashboard.product.addons.all')
            ->with('success', 'Add-on moved to trash.');
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
