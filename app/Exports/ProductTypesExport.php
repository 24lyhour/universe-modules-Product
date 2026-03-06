<?php

namespace Modules\Product\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Product\Models\ProductType;

class ProductTypesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = ProductType::query()->with('outlet');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($this->filters['is_active']) && $this->filters['is_active'] !== '') {
            $isActive = filter_var($this->filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        if (!empty($this->filters['outlet_id'])) {
            $query->where('outlet_id', $this->filters['outlet_id']);
        }

        return $query->withCount('products')->orderBy('sort_order');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Slug',
            'Description',
            'Outlet',
            'Products Count',
            'Status',
            'Sort Order',
            'Created At',
        ];
    }

    public function map($productType): array
    {
        return [
            $productType->id,
            $productType->name,
            $productType->slug,
            $productType->description,
            $productType->outlet?->name ?? '-',
            $productType->products_count ?? 0,
            $productType->is_active ? 'Active' : 'Inactive',
            $productType->sort_order,
            $productType->created_at?->format('Y-m-d H:i:s'),
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
