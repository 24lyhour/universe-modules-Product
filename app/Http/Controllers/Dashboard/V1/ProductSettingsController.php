<?php

namespace Modules\Product\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductSettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::getGroup('product');

        return Inertia::render('product::dashboard/settings/ProductSettings', [
            'productSettings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'auto_generate_sku' => 'required|boolean',
            'sku_prefix' => 'nullable|string|max:10',
            'sku_separator' => 'nullable|string|max:3',
            'low_stock_threshold' => 'required|integer|min:0|max:1000',
        ]);

        // Convert boolean to "1" or "0" for proper storage
        $autoGenerateSku = $validated['auto_generate_sku'] ? '1' : '0';

        Setting::setValue('product', 'auto_generate_sku', $autoGenerateSku, 'boolean');
        Setting::setValue('product', 'sku_prefix', $validated['sku_prefix'] ?? '', 'string');
        Setting::setValue('product', 'sku_separator', $validated['sku_separator'] ?? '-', 'string');
        Setting::setValue('product', 'low_stock_threshold', $validated['low_stock_threshold'], 'integer');

        return back()->with('success', 'Product settings updated successfully.');
    }
}
