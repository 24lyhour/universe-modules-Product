<?php

namespace Modules\Product\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Product\Models\Brand;

class BrandsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Brand::query()->with('outlet')->withCount('products');

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

        return $query->orderBy('sort_order');
    }

    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Name',
            'Slug',
            'Description',
            'Logo',
            'Website',
            'Outlet',
            'Sort Order',
            'Status',
            'Products Count',
            'Created At',
            'Updated At',
        ];
    }

    public function map($brand): array
    {
        return [
            $brand->id,
            $brand->uuid,
            $brand->name,
            $brand->slug,
            $brand->description,
            $brand->logo,
            $brand->website,
            $brand->outlet?->name ?? '-',
            $brand->sort_order,
            $brand->is_active ? 'Active' : 'Inactive',
            $brand->products_count,
            $brand->created_at?->format('Y-m-d H:i:s'),
            $brand->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
