<?php

namespace Modules\Product\Actions\Dashboard\V1;

use App\Models\Setting;

class UpdateProductSettingsAction
{
    /**
     * Update product settings.
     */
    public function execute(array $validated): void
    {
        Setting::setValue('product', 'auto_generate_sku', $validated['auto_generate_sku'] ? '1' : '0', 'boolean');
        Setting::setValue('product', 'sku_prefix', $validated['sku_prefix'] ?? '', 'string');
        Setting::setValue('product', 'sku_separator', $validated['sku_separator'] ?? '-', 'string');
        Setting::setValue('product', 'low_stock_threshold', $validated['low_stock_threshold'], 'integer');
    }

    /**
     * Get product settings with defaults.
     */
    public static function getSettings(): array
    {
        $settings = Setting::getGroup('product');

        return [
            'auto_generate_sku' => $settings['auto_generate_sku'] ?? true,
            'sku_prefix' => $settings['sku_prefix'] ?? 'PRD',
            'sku_separator' => $settings['sku_separator'] ?? '-',
            'low_stock_threshold' => $settings['low_stock_threshold'] ?? 10,
        ];
    }
}
