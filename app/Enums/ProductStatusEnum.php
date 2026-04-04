<?php

namespace Modules\Product\Enums;

enum ProductStatusEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Draft = 'draft';
    case OutOfStock = 'out_of_stock';

    /**
     * Get the label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Draft => 'Draft',
            self::OutOfStock => 'Out of Stock',
        };
    }

    /**
     * Get all statuses as options for select inputs.
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            self::cases()
        );
    }

    /**
     * Get all status values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
