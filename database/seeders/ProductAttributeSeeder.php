<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Product\Models\ProductAttribute;
use Modules\Product\Models\ProductAttributeValue;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'Storage',
                'type' => 'button',
                'description' => 'Device storage capacity',
                'sort_order' => 1,
                'values' => [
                    ['value' => '64gb', 'label' => '64 GB', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => '128gb', 'label' => '128 GB', 'price_adjustment' => 50.00, 'sort_order' => 2],
                    ['value' => '256gb', 'label' => '256 GB', 'price_adjustment' => 100.00, 'sort_order' => 3],
                    ['value' => '512gb', 'label' => '512 GB', 'price_adjustment' => 200.00, 'sort_order' => 4],
                    ['value' => '1tb', 'label' => '1 TB', 'price_adjustment' => 400.00, 'sort_order' => 5],
                    ['value' => '2tb', 'label' => '2 TB', 'price_adjustment' => 600.00, 'sort_order' => 6],
                ],
            ],
            [
                'name' => 'Color',
                'type' => 'color',
                'description' => 'Device color options',
                'sort_order' => 2,
                'values' => [
                    ['value' => 'black', 'label' => 'Black', 'color_code' => '#1F2937', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => 'white', 'label' => 'White', 'color_code' => '#F9FAFB', 'price_adjustment' => 0, 'sort_order' => 2],
                    ['value' => 'silver', 'label' => 'Silver', 'color_code' => '#9CA3AF', 'price_adjustment' => 0, 'sort_order' => 3],
                    ['value' => 'gold', 'label' => 'Gold', 'color_code' => '#D4AF37', 'price_adjustment' => 50.00, 'sort_order' => 4],
                    ['value' => 'space_gray', 'label' => 'Space Gray', 'color_code' => '#4B5563', 'price_adjustment' => 0, 'sort_order' => 5],
                    ['value' => 'midnight', 'label' => 'Midnight', 'color_code' => '#111827', 'price_adjustment' => 0, 'sort_order' => 6],
                    ['value' => 'blue', 'label' => 'Blue', 'color_code' => '#3B82F6', 'price_adjustment' => 0, 'sort_order' => 7],
                    ['value' => 'purple', 'label' => 'Purple', 'color_code' => '#A855F7', 'price_adjustment' => 0, 'sort_order' => 8],
                ],
            ],
            [
                'name' => 'RAM',
                'type' => 'button',
                'description' => 'Memory capacity',
                'sort_order' => 3,
                'values' => [
                    ['value' => '4gb', 'label' => '4 GB', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => '8gb', 'label' => '8 GB', 'price_adjustment' => 100.00, 'sort_order' => 2],
                    ['value' => '16gb', 'label' => '16 GB', 'price_adjustment' => 200.00, 'sort_order' => 3],
                    ['value' => '32gb', 'label' => '32 GB', 'price_adjustment' => 400.00, 'sort_order' => 4],
                    ['value' => '64gb', 'label' => '64 GB', 'price_adjustment' => 800.00, 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Screen Size',
                'type' => 'button',
                'description' => 'Display size options',
                'sort_order' => 4,
                'values' => [
                    ['value' => '11inch', 'label' => '11"', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => '12.9inch', 'label' => '12.9"', 'price_adjustment' => 200.00, 'sort_order' => 2],
                    ['value' => '13inch', 'label' => '13"', 'price_adjustment' => 0, 'sort_order' => 3],
                    ['value' => '14inch', 'label' => '14"', 'price_adjustment' => 200.00, 'sort_order' => 4],
                    ['value' => '15inch', 'label' => '15"', 'price_adjustment' => 300.00, 'sort_order' => 5],
                    ['value' => '16inch', 'label' => '16"', 'price_adjustment' => 400.00, 'sort_order' => 6],
                ],
            ],
            [
                'name' => 'Connectivity',
                'type' => 'select',
                'description' => 'Network connectivity options',
                'sort_order' => 5,
                'values' => [
                    ['value' => 'wifi', 'label' => 'WiFi Only', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => 'wifi_cellular', 'label' => 'WiFi + Cellular', 'price_adjustment' => 150.00, 'sort_order' => 2],
                    ['value' => '5g', 'label' => '5G', 'price_adjustment' => 200.00, 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Processor',
                'type' => 'button',
                'description' => 'CPU/Chip options',
                'sort_order' => 6,
                'values' => [
                    ['value' => 'm1', 'label' => 'M1', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => 'm2', 'label' => 'M2', 'price_adjustment' => 200.00, 'sort_order' => 2],
                    ['value' => 'm3', 'label' => 'M3', 'price_adjustment' => 400.00, 'sort_order' => 3],
                    ['value' => 'm3_pro', 'label' => 'M3 Pro', 'price_adjustment' => 600.00, 'sort_order' => 4],
                    ['value' => 'm3_max', 'label' => 'M3 Max', 'price_adjustment' => 1000.00, 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Warranty',
                'type' => 'select',
                'description' => 'Extended warranty options',
                'sort_order' => 7,
                'values' => [
                    ['value' => 'standard', 'label' => 'Standard (1 Year)', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => '2year', 'label' => '2 Years', 'price_adjustment' => 99.00, 'sort_order' => 2],
                    ['value' => '3year', 'label' => '3 Years', 'price_adjustment' => 149.00, 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Keyboard Layout',
                'type' => 'select',
                'description' => 'Keyboard language layout',
                'sort_order' => 8,
                'values' => [
                    ['value' => 'us', 'label' => 'US English', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => 'uk', 'label' => 'UK English', 'price_adjustment' => 0, 'sort_order' => 2],
                    ['value' => 'german', 'label' => 'German', 'price_adjustment' => 0, 'sort_order' => 3],
                    ['value' => 'french', 'label' => 'French', 'price_adjustment' => 0, 'sort_order' => 4],
                    ['value' => 'spanish', 'label' => 'Spanish', 'price_adjustment' => 0, 'sort_order' => 5],
                ],
            ],
        ];

        foreach ($attributes as $attrData) {
            $values = $attrData['values'];
            unset($attrData['values']);

            $attribute = ProductAttribute::firstOrCreate(
                ['slug' => Str::slug($attrData['name'])],
                array_merge($attrData, [
                    'uuid' => Str::uuid(),
                    'is_active' => true,
                ])
            );

            foreach ($values as $valueData) {
                ProductAttributeValue::firstOrCreate(
                    [
                        'attribute_id' => $attribute->id,
                        'value' => $valueData['value'],
                    ],
                    array_merge($valueData, [
                        'uuid' => Str::uuid(),
                        'is_active' => true,
                    ])
                );
            }
        }

        $this->command->info('Product attributes seeded: ' . ProductAttribute::count() . ' attributes, ' . ProductAttributeValue::count() . ' values.');
    }
}
