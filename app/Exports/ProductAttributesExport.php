<?php

namespace Modules\Product\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Product\Models\ProductAttribute;

class ProductAttributesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = ProductAttribute::query()->withCount('values');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (isset($this->filters['is_active']) && $this->filters['is_active'] !== '') {
            $isActive = filter_var($this->filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        return $query->orderBy('sort_order');
    }

    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Name',
            'Slug',
            'Type',
            'Description',
            'Values Count',
            'Sort Order',
            'Status',
            'Created At',
        ];
    }

    public function map($attribute): array
    {
        return [
            $attribute->id,
            $attribute->uuid,
            $attribute->name,
            $attribute->slug,
            ucfirst($attribute->type),
            strip_tags($attribute->description ?? ''),
            $attribute->values_count ?? 0,
            $attribute->sort_order,
            $attribute->is_active ? 'Active' : 'Inactive',
            $attribute->created_at?->format('Y-m-d H:i:s'),
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
