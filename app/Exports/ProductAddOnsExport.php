<?php

namespace Modules\Product\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Product\Models\ProductAddOn;

class ProductAddOnsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected ?string $search;
    protected ?string $status;

    public function __construct(?string $search = null, ?string $status = null)
    {
        $this->search = $search;
        $this->status = $status;
    }

    public function query()
    {
        $query = ProductAddOn::query()->with(['product', 'addOnProduct']);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('addOnProduct', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($this->status !== null && $this->status !== '') {
            $query->where('is_active', $this->status === 'true' || $this->status === '1');
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Parent Product',
            'Add-on Name',
            'Add-on Product',
            'Price Adjustment',
            'Max Quantity',
            'Sort Order',
            'Required',
            'Status',
            'Created At',
        ];
    }

    public function map($addOn): array
    {
        return [
            $addOn->id,
            $addOn->uuid,
            $addOn->product?->name ?? '-',
            $addOn->name ?? '-',
            $addOn->addOnProduct?->name ?? '-',
            '$' . number_format($addOn->price_adjustment, 2),
            $addOn->max_quantity,
            $addOn->sort_order,
            $addOn->is_required ? 'Yes' : 'No',
            $addOn->is_active ? 'Active' : 'Inactive',
            $addOn->created_at?->format('Y-m-d H:i:s'),
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
