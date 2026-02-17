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
                'name' => 'Size',
                'type' => 'button',
                'description' => 'Product size options',
                'sort_order' => 1,
                'values' => [
                    ['value' => 'S', 'label' => 'Small', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => 'M', 'label' => 'Medium', 'price_adjustment' => 1.00, 'sort_order' => 2],
                    ['value' => 'L', 'label' => 'Large', 'price_adjustment' => 2.00, 'sort_order' => 3],
                    ['value' => 'XL', 'label' => 'Extra Large', 'price_adjustment' => 3.00, 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Color',
                'type' => 'color',
                'description' => 'Product color options',
                'sort_order' => 2,
                'values' => [
                    ['value' => 'red', 'label' => 'Red', 'color_code' => '#EF4444', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => 'blue', 'label' => 'Blue', 'color_code' => '#3B82F6', 'price_adjustment' => 0, 'sort_order' => 2],
                    ['value' => 'green', 'label' => 'Green', 'color_code' => '#22C55E', 'price_adjustment' => 0, 'sort_order' => 3],
                    ['value' => 'black', 'label' => 'Black', 'color_code' => '#1F2937', 'price_adjustment' => 0, 'sort_order' => 4],
                    ['value' => 'white', 'label' => 'White', 'color_code' => '#F9FAFB', 'price_adjustment' => 0, 'sort_order' => 5],
                    ['value' => 'yellow', 'label' => 'Yellow', 'color_code' => '#EAB308', 'price_adjustment' => 0, 'sort_order' => 6],
                    ['value' => 'purple', 'label' => 'Purple', 'color_code' => '#A855F7', 'price_adjustment' => 1.00, 'sort_order' => 7],
                    ['value' => 'orange', 'label' => 'Orange', 'color_code' => '#F97316', 'price_adjustment' => 0, 'sort_order' => 8],
                ],
            ],
            [
                'name' => 'Spice Level',
                'type' => 'button',
                'description' => 'How spicy do you want it?',
                'sort_order' => 3,
                'values' => [
                    ['value' => 'mild', 'label' => 'Mild', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => 'medium', 'label' => 'Medium', 'price_adjustment' => 0, 'sort_order' => 2],
                    ['value' => 'hot', 'label' => 'Hot', 'price_adjustment' => 0.50, 'sort_order' => 3],
                    ['value' => 'extra_hot', 'label' => 'Extra Hot', 'price_adjustment' => 0.50, 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Temperature',
                'type' => 'button',
                'description' => 'Beverage temperature',
                'sort_order' => 4,
                'values' => [
                    ['value' => 'hot', 'label' => 'Hot', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => 'iced', 'label' => 'Iced', 'price_adjustment' => 0.50, 'sort_order' => 2],
                    ['value' => 'blended', 'label' => 'Blended', 'price_adjustment' => 1.00, 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Milk Type',
                'type' => 'select',
                'description' => 'Choice of milk',
                'sort_order' => 5,
                'values' => [
                    ['value' => 'regular', 'label' => 'Regular Milk', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => 'skim', 'label' => 'Skim Milk', 'price_adjustment' => 0, 'sort_order' => 2],
                    ['value' => 'oat', 'label' => 'Oat Milk', 'price_adjustment' => 0.75, 'sort_order' => 3],
                    ['value' => 'almond', 'label' => 'Almond Milk', 'price_adjustment' => 0.75, 'sort_order' => 4],
                    ['value' => 'soy', 'label' => 'Soy Milk', 'price_adjustment' => 0.50, 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Sweetness',
                'type' => 'button',
                'description' => 'Sugar level',
                'sort_order' => 6,
                'values' => [
                    ['value' => '0', 'label' => 'No Sugar', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => '25', 'label' => '25%', 'price_adjustment' => 0, 'sort_order' => 2],
                    ['value' => '50', 'label' => '50%', 'price_adjustment' => 0, 'sort_order' => 3],
                    ['value' => '75', 'label' => '75%', 'price_adjustment' => 0, 'sort_order' => 4],
                    ['value' => '100', 'label' => '100%', 'price_adjustment' => 0, 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Topping',
                'type' => 'select',
                'description' => 'Add extra toppings',
                'sort_order' => 7,
                'values' => [
                    ['value' => 'none', 'label' => 'No Topping', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => 'cheese', 'label' => 'Extra Cheese', 'price_adjustment' => 1.50, 'sort_order' => 2],
                    ['value' => 'bacon', 'label' => 'Bacon', 'price_adjustment' => 2.00, 'sort_order' => 3],
                    ['value' => 'avocado', 'label' => 'Avocado', 'price_adjustment' => 2.50, 'sort_order' => 4],
                    ['value' => 'egg', 'label' => 'Fried Egg', 'price_adjustment' => 1.00, 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Sauce',
                'type' => 'select',
                'description' => 'Choice of sauce',
                'sort_order' => 8,
                'values' => [
                    ['value' => 'ketchup', 'label' => 'Ketchup', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => 'mayo', 'label' => 'Mayonnaise', 'price_adjustment' => 0, 'sort_order' => 2],
                    ['value' => 'bbq', 'label' => 'BBQ Sauce', 'price_adjustment' => 0.50, 'sort_order' => 3],
                    ['value' => 'sriracha', 'label' => 'Sriracha', 'price_adjustment' => 0.50, 'sort_order' => 4],
                    ['value' => 'ranch', 'label' => 'Ranch', 'price_adjustment' => 0.50, 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Material',
                'type' => 'button',
                'description' => 'Product material type',
                'sort_order' => 9,
                'values' => [
                    ['value' => 'cotton', 'label' => 'Cotton', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => 'polyester', 'label' => 'Polyester', 'price_adjustment' => -1.00, 'sort_order' => 2],
                    ['value' => 'wool', 'label' => 'Wool', 'price_adjustment' => 5.00, 'sort_order' => 3],
                    ['value' => 'leather', 'label' => 'Leather', 'price_adjustment' => 15.00, 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Storage',
                'type' => 'button',
                'description' => 'Storage capacity',
                'sort_order' => 10,
                'values' => [
                    ['value' => '64gb', 'label' => '64 GB', 'price_adjustment' => 0, 'sort_order' => 1],
                    ['value' => '128gb', 'label' => '128 GB', 'price_adjustment' => 50.00, 'sort_order' => 2],
                    ['value' => '256gb', 'label' => '256 GB', 'price_adjustment' => 100.00, 'sort_order' => 3],
                    ['value' => '512gb', 'label' => '512 GB', 'price_adjustment' => 200.00, 'sort_order' => 4],
                    ['value' => '1tb', 'label' => '1 TB', 'price_adjustment' => 400.00, 'sort_order' => 5],
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
