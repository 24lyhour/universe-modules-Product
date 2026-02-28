<?php

namespace Modules\Product\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Product\Models\Product;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Product::query()->with('outlet');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['outlet_id'])) {
            $query->where('outlet_id', $this->filters['outlet_id']);
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Name',
            'SKU',
            'Description',
            'Price',
            'Purchase Price',
            'Sale Price',
            'Stock',
            'Low Stock Threshold',
            'Status',
            'Is Featured',
            'Outlet',
            'Created At',
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->sku,
            $product->description,
            $product->price,
            $product->purchase_price,
            $product->sale_price,
            $product->stock,
            $product->low_stock_threshold,
            $product->status,
            $product->is_featured ? 'Yes' : 'No',
            $product->outlet?->name,
            $product->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ],
        ];
    }
}
