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

class ProductsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithBatchInserts, WithChunkReading
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

    public function __construct(string $duplicateHandling = self::DUPLICATE_SKIP, bool $previewMode = false)
    {
        $this->duplicateHandling = $duplicateHandling;
        $this->previewMode = $previewMode;
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
                'sku' => $data['sku'] ?? null,
                'price' => $data['price'] ?? null,
                'stock' => $data['stock'] ?? null,
                'status' => 'ready',
                'errors' => [],
                'warnings' => [],
                'is_duplicate' => false,
                'existing_record' => null,
            ];

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                $preview['status'] = 'error';
                $preview['errors'] = $validator->errors()->all();
            }

            // Check for duplicate by SKU
            $sku = $data['sku'] ?? null;
            if (!empty($sku)) {
                $existing = Product::withoutGlobalScopes()->where('sku', $sku)->first();
                if ($existing) {
                    $preview['is_duplicate'] = true;
                    $preview['existing_record'] = ['id' => $existing->id, 'name' => $existing->name];

                    if ($this->duplicateHandling === self::DUPLICATE_FAIL) {
                        $preview['status'] = 'error';
                        $preview['errors'][] = "Duplicate SKU: {$sku}";
                    } elseif ($this->duplicateHandling === self::DUPLICATE_SKIP) {
                        $preview['status'] = 'skip';
                        $preview['warnings'][] = 'Will be skipped (duplicate SKU)';
                    } else {
                        $preview['status'] = 'update';
                        $preview['warnings'][] = 'Will update existing record';
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
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            $this->addFailedRow($rowNumber, $data, $validator->errors()->all());
            return;
        }

        $sku = $data['sku'] ?? null;
        $existing = $sku ? Product::withoutGlobalScopes()->where('sku', $sku)->first() : null;

        if ($existing) {
            switch ($this->duplicateHandling) {
                case self::DUPLICATE_FAIL:
                    $this->addFailedRow($rowNumber, $data, ["SKU '{$sku}' already exists"]);
                    return;
                case self::DUPLICATE_SKIP:
                    $this->skippedCount++;
                    return;
                case self::DUPLICATE_UPDATE:
                    $this->updateRecord($existing, $data, $rowNumber);
                    return;
            }
        }

        $this->createRecord($data, $rowNumber);
    }

    protected function createRecord(array $data, int $rowNumber): void
    {
        try {
            Product::create([
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'sku' => $data['sku'] ?? null,
                'description' => $data['description'] ?? null,
                'price' => floatval($data['price']),
                'purchase_price' => !empty($data['purchase_price']) ? floatval($data['purchase_price']) : null,
                'sale_price' => !empty($data['sale_price']) ? floatval($data['sale_price']) : null,
                'stock' => intval($data['stock'] ?? 0),
                'low_stock_threshold' => intval($data['low_stock_threshold'] ?? 10),
                'status' => $this->parseStatus($data['status'] ?? 'draft'),
            ]);

            $this->importedCount++;
        } catch (\Exception $e) {
            $this->addFailedRow($rowNumber, $data, [$e->getMessage()]);
        }
    }

    protected function updateRecord(Product $record, array $data, int $rowNumber): void
    {
        try {
            $record->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? $record->description,
                'price' => floatval($data['price']),
                'purchase_price' => !empty($data['purchase_price']) ? floatval($data['purchase_price']) : $record->purchase_price,
                'sale_price' => !empty($data['sale_price']) ? floatval($data['sale_price']) : $record->sale_price,
                'stock' => isset($data['stock']) ? intval($data['stock']) : $record->stock,
                'low_stock_threshold' => isset($data['low_stock_threshold']) ? intval($data['low_stock_threshold']) : $record->low_stock_threshold,
                'status' => isset($data['status']) ? $this->parseStatus($data['status']) : $record->status,
            ]);

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

    protected function parseStatus($value): string
    {
        if (empty($value)) return 'draft';
        $value = strtolower(trim((string) $value));
        return in_array($value, ['active', 'inactive', 'draft', 'out_of_stock']) ? $value : 'draft';
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
