<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Outlet\Models\Outlet;
use Modules\Outlet\Models\TypeOutlet;
use Modules\Product\Models\ProductType;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates ProductTypes based on outlet type:
     * - Restaurant/Cafe/Fast Food/Bar/Bakery/Food Truck: Food, Beverage, Dessert
     * - Store/Warehouse/Kiosk: Gadget, Clothing, Book, Accessory
     * - Office: Service, Consultation
     */
    public function run(): void
    {
        // Product types for Food & Beverage outlets
        $foodBeverageTypes = [
            ['name' => 'Food', 'slug' => 'food', 'description' => 'Food items including meals, snacks, and appetizers', 'sort_order' => 1],
            ['name' => 'Beverage', 'slug' => 'beverage', 'description' => 'Drinks including coffee, tea, juices, and soft drinks', 'sort_order' => 2],
            ['name' => 'Dessert', 'slug' => 'dessert', 'description' => 'Sweet treats and desserts', 'sort_order' => 3],
            ['name' => 'Combo', 'slug' => 'combo', 'description' => 'Combo meals and value sets', 'sort_order' => 4],
        ];

        // Product types for Retail outlets
        $retailTypes = [
            ['name' => 'Gadget', 'slug' => 'gadget', 'description' => 'Electronics and gadgets', 'sort_order' => 1],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Apparel and fashion items', 'sort_order' => 2],
            ['name' => 'Book', 'slug' => 'book', 'description' => 'Books and publications', 'sort_order' => 3],
            ['name' => 'Accessory', 'slug' => 'accessory', 'description' => 'Accessories and add-ons', 'sort_order' => 4],
            ['name' => 'Home & Living', 'slug' => 'home-living', 'description' => 'Home decor and furniture', 'sort_order' => 5],
        ];

        // Food & Beverage outlet types
        $foodBeverageOutletTypes = ['Restaurant', 'Cafe', 'Fast Food', 'Bar', 'Bakery', 'Food Truck'];

        // Retail outlet types
        $retailOutletTypes = ['Store', 'Warehouse', 'Kiosk'];

        // Get all outlets
        $outlets = Outlet::with('typeOutlet')->get();

        if ($outlets->isEmpty()) {
            $this->command->warn('No outlets found. Please run OutletSeeder first.');
            return;
        }

        $createdCount = 0;

        foreach ($outlets as $outlet) {
            $typeName = $outlet->typeOutlet?->name_type ?? 'Restaurant';

            // Determine which product types to create based on outlet type
            if (in_array($typeName, $foodBeverageOutletTypes)) {
                $productTypes = $foodBeverageTypes;
            } elseif (in_array($typeName, $retailOutletTypes)) {
                $productTypes = $retailTypes;
            } else {
                // Default to all types
                $productTypes = array_merge($foodBeverageTypes, $retailTypes);
            }

            foreach ($productTypes as $type) {
                ProductType::firstOrCreate(
                    [
                        'slug' => $type['slug'],
                        'outlet_id' => $outlet->id,
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'name' => $type['name'],
                        'slug' => $type['slug'],
                        'description' => $type['description'],
                        'outlet_id' => $outlet->id,
                        'sort_order' => $type['sort_order'],
                        'is_active' => true,
                    ]
                );
                $createdCount++;
            }
        }

        $this->command->info("ProductTypes seeded successfully. Created {$createdCount} product types for {$outlets->count()} outlets.");
    }
}
