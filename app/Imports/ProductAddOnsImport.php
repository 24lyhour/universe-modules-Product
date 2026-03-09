<?php

namespace Modules\Product\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAddOn;

class ProductAddOnsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithBatchInserts, WithChunkReading
{
    public const DUPLICATE_SKIP = 'skip';
    public const DUPLICATE_UPDATE = 'update';
    public const DUPLICATE_FAIL = 'fail';

    protected array $errors = [];
    protected array $failedRows = [];
    protected int $importedCount = 0;
    protected int $updatedCount = 0;
    protected int $skippedCount = 0;
    protected string $duplicateHandling;
    protected bool $previewMode = false;
    protected array $previewData = [];
    protected array $productCache = [];

    public function __construct(string $duplicateHandling = self::DUPLICATE_SKIP, bool $previewMode = false)
    {
        $this->duplicateHandling = $duplicateHandling;
        $this->previewMode = $previewMode;
        $this->cacheProducts();
    }

    protected function cacheProducts(): void
    {
        $this->productCache = Product::withoutGlobalScopes()
            ->select('id', 'name', 'sku')
            ->get()
            ->keyBy(fn($p) => strtolower($p->sku ?? $p->name))
            ->toArray();
    }

    protected function findProduct(string $identifier): ?array
    {
        $key = strtolower(trim($identifier));
        return $this->productCache[$key] ?? null;
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            $this->errors[] = ['row' => 0, 'message' => 'No data rows found.'];
            return;
        }

        if ($this->previewMode) {
            $this->processPreview($rows);
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $this->processRow($row->toArray(), $index + 2);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errors[] = ['row' => 0, 'message' => "Import failed: {$e->getMessage()}"];
        }
    }

    protected function processPreview(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = $this->normalizeRow($row->toArray());

            $preview = [
                'row_number' => $rowNumber,
                'name' => $data['name'] ?? null,
                'product_sku' => $data['product_sku'] ?? null,
                'price_adjustment' => $data['price_adjustment'] ?? null,
                'max_quantity' => $data['max_quantity'] ?? null,
                'status' => 'ready',
                'errors' => [],
                'warnings' => [],
                'is_duplicate' => false,
                'existing_record' => null,
            ];

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'product_sku' => 'required|string',
                'price_adjustment' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                $preview['status'] = 'error';
                $preview['errors'] = $validator->errors()->all();
            }

            // Validate product exists
            $productSku = $data['product_sku'] ?? null;
            if (!empty($productSku) && !$this->findProduct($productSku)) {
                $preview['status'] = 'error';
                $preview['errors'][] = "Product with SKU '{$productSku}' not found";
            }

            // Check for duplicate by name + product combination
            $name = $data['name'] ?? null;
            if (!empty($name) && !empty($productSku)) {
                $product = $this->findProduct($productSku);
                if ($product) {
                    $existing = ProductAddOn::withoutGlobalScopes()
                        ->where('product_id', $product['id'])
                        ->where('name', $name)
                        ->first();

                    if ($existing) {
                        $preview['is_duplicate'] = true;
                        $preview['existing_record'] = ['id' => $existing->id, 'name' => $existing->name];

                        if ($this->duplicateHandling === self::DUPLICATE_FAIL) {
                            $preview['status'] = 'error';
                            $preview['errors'][] = "Duplicate add-on: {$name}";
                        } elseif ($this->duplicateHandling === self::DUPLICATE_SKIP) {
                            $preview['status'] = 'skip';
                            $preview['warnings'][] = 'Will be skipped (duplicate)';
                        } else {
                            $preview['status'] = 'update';
                            $preview['warnings'][] = 'Will update existing record';
                        }
                    }
                }
            }

            $this->previewData[] = $preview;
        }
    }

    protected function processRow(array $row, int $rowNumber): void
    {
        $data = $this->normalizeRow($row);

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'product_sku' => 'required|string',
            'price_adjustment' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $this->addFailedRow($rowNumber, $data, $validator->errors()->all());
            return;
        }

        $productSku = $data['product_sku'] ?? null;
        $product = $this->findProduct($productSku);

        if (!$product) {
            $this->addFailedRow($rowNumber, $data, ["Product with SKU '{$productSku}' not found"]);
            return;
        }

        $name = $data['name'];
        $existing = ProductAddOn::withoutGlobalScopes()
            ->where('product_id', $product['id'])
            ->where('name', $name)
            ->first();

        if ($existing) {
            switch ($this->duplicateHandling) {
                case self::DUPLICATE_FAIL:
                    $this->addFailedRow($rowNumber, $data, ["Add-on '{$name}' already exists for this product"]);
                    return;
                case self::DUPLICATE_SKIP:
                    $this->skippedCount++;
                    return;
                case self::DUPLICATE_UPDATE:
                    $this->updateRecord($existing, $data, $rowNumber);
                    return;
            }
        }

        $this->createRecord($product['id'], $data, $rowNumber);
    }

    protected function createRecord(int $productId, array $data, int $rowNumber): void
    {
        try {
            // Find add-on product if specified
            $addOnProductId = null;
            if (!empty($data['add_on_product_sku'])) {
                $addOnProduct = $this->findProduct($data['add_on_product_sku']);
                if ($addOnProduct) {
                    $addOnProductId = $addOnProduct['id'];
                }
            }

            ProductAddOn::create([
                'uuid' => (string) Str::uuid(),
                'product_id' => $productId,
                'add_on_product_id' => $addOnProductId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'price_adjustment' => floatval($data['price_adjustment']),
                'max_quantity' => intval($data['max_quantity'] ?? 10),
                'sort_order' => intval($data['sort_order'] ?? 0),
                'is_required' => $this->parseBool($data['is_required'] ?? false),
                'is_active' => $this->parseBool($data['is_active'] ?? true),
            ]);

            $this->importedCount++;
        } catch (\Exception $e) {
            $this->addFailedRow($rowNumber, $data, [$e->getMessage()]);
        }
    }

    protected function updateRecord(ProductAddOn $record, array $data, int $rowNumber): void
    {
        try {
            $updateData = [
                'description' => $data['description'] ?? $record->description,
                'price_adjustment' => floatval($data['price_adjustment']),
                'max_quantity' => isset($data['max_quantity']) ? intval($data['max_quantity']) : $record->max_quantity,
                'sort_order' => isset($data['sort_order']) ? intval($data['sort_order']) : $record->sort_order,
            ];

            if (isset($data['is_required'])) {
                $updateData['is_required'] = $this->parseBool($data['is_required']);
            }

            if (isset($data['is_active'])) {
                $updateData['is_active'] = $this->parseBool($data['is_active']);
            }

            if (!empty($data['add_on_product_sku'])) {
                $addOnProduct = $this->findProduct($data['add_on_product_sku']);
                if ($addOnProduct) {
                    $updateData['add_on_product_id'] = $addOnProduct['id'];
                }
            }

            $record->update($updateData);
            $this->updatedCount++;
        } catch (\Exception $e) {
            $this->addFailedRow($rowNumber, $data, [$e->getMessage()]);
        }
    }

    protected function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = Str::snake(str_replace(' ', '_', strtolower(trim($key))));
            $normalized[$normalizedKey] = is_string($value) ? trim($value) : $value;
        }
        return $normalized;
    }

    protected function parseBool($value): bool
    {
        if (is_bool($value)) return $value;
        if (is_numeric($value)) return (bool) $value;
        $value = strtolower(trim((string) $value));
        return in_array($value, ['1', 'true', 'yes', 'y', 'on']);
    }

    protected function addFailedRow(int $rowNumber, array $data, array $errors): void
    {
        $this->failedRows[] = ['row_number' => $rowNumber, 'data' => $data, 'errors' => $errors];
        $this->skippedCount++;
    }

    public function getPreviewData(): array
    {
        return $this->previewData;
    }

    public function getResults(): array
    {
        $stats = ['total' => count($this->previewData), 'ready' => 0, 'update' => 0, 'skip' => 0, 'error' => 0];
        foreach ($this->previewData as $row) {
            $status = $row['status'] ?? 'ready';
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
        }

        return [
            'imported' => $this->importedCount,
            'updated' => $this->updatedCount,
            'skipped' => $this->skippedCount,
            'failed' => count($this->failedRows),
            'failed_rows' => $this->failedRows,
            'preview_stats' => $stats,
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
