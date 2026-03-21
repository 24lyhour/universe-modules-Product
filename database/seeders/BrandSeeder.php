<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Outlet\Models\Outlet;
use Modules\Product\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates Brands based on outlet type:
     * - Restaurant/Cafe/Fast Food/Bar/Bakery/Food Truck: Food & Beverage brands
     * - Store/Warehouse/Kiosk: Retail brands
     */
    public function run(): void
    {
        // Brands for Food & Beverage outlets
        $foodBeverageBrands = [
            [
                'name' => 'Fresh Bites',
                'description' => 'Premium quality fresh food and ingredients',
                'website' => 'https://freshbites.example.com',
                'logo' => 'https://ui-avatars.com/api/?name=Fresh+Bites&background=22c55e&color=fff&size=200',
                'sort_order' => 1,
            ],
            [
                'name' => 'Cafe Delight',
                'description' => 'Artisan coffee and specialty beverages',
                'website' => 'https://cafedelight.example.com',
                'logo' => 'https://ui-avatars.com/api/?name=Cafe+Delight&background=8b5cf6&color=fff&size=200',
                'sort_order' => 2,
            ],
            [
                'name' => 'Grill Master',
                'description' => 'Premium grilled meats and BBQ specialties',
                'website' => 'https://grillmaster.example.com',
                'logo' => 'https://ui-avatars.com/api/?name=Grill+Master&background=ef4444&color=fff&size=200',
                'sort_order' => 3,
            ],
            [
                'name' => 'Sweet Treats',
                'description' => 'Delicious desserts and bakery items',
                'website' => 'https://sweettreats.example.com',
                'logo' => 'https://ui-avatars.com/api/?name=Sweet+Treats&background=ec4899&color=fff&size=200',
                'sort_order' => 4,
            ],
            [
                'name' => 'Healthy Choice',
                'description' => 'Organic and healthy food options',
                'website' => 'https://healthychoice.example.com',
                'logo' => 'https://ui-avatars.com/api/?name=Healthy+Choice&background=14b8a6&color=fff&size=200',
                'sort_order' => 5,
            ],
        ];

        // Brands for Retail outlets
        $retailBrands = [
            [
                'name' => 'TechZone',
                'description' => 'Latest gadgets and electronics',
                'website' => 'https://techzone.example.com',
                'logo' => 'https://ui-avatars.com/api/?name=TechZone&background=3b82f6&color=fff&size=200',
                'sort_order' => 1,
            ],
            [
                'name' => 'StyleHub',
                'description' => 'Trendy fashion and apparel',
                'website' => 'https://stylehub.example.com',
                'logo' => 'https://ui-avatars.com/api/?name=StyleHub&background=f59e0b&color=fff&size=200',
                'sort_order' => 2,
            ],
            [
                'name' => 'BookWorm',
                'description' => 'Books, magazines, and publications',
                'website' => 'https://bookworm.example.com',
                'logo' => 'https://ui-avatars.com/api/?name=BookWorm&background=6366f1&color=fff&size=200',
                'sort_order' => 3,
            ],
            [
                'name' => 'HomeStyle',
                'description' => 'Home decor and lifestyle products',
                'website' => 'https://homestyle.example.com',
                'logo' => 'https://ui-avatars.com/api/?name=HomeStyle&background=84cc16&color=fff&size=200',
                'sort_order' => 4,
            ],
            [
                'name' => 'SportMax',
                'description' => 'Sports equipment and athletic wear',
                'website' => 'https://sportmax.example.com',
                'logo' => 'https://ui-avatars.com/api/?name=SportMax&background=f97316&color=fff&size=200',
                'sort_order' => 5,
            ],
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

            // Determine which brands to create based on outlet type
            if (in_array($typeName, $foodBeverageOutletTypes)) {
                $brands = $foodBeverageBrands;
            } elseif (in_array($typeName, $retailOutletTypes)) {
                $brands = $retailBrands;
            } else {
                // Default to all brands
                $brands = array_merge($foodBeverageBrands, $retailBrands);
            }

            foreach ($brands as $brand) {
                $slug = Str::slug($brand['name'] . '-' . $outlet->id);

                Brand::firstOrCreate(
                    [
                        'slug' => $slug,
                        'outlet_id' => $outlet->id,
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'name' => $brand['name'],
                        'slug' => $slug,
                        'description' => $brand['description'],
                        'website' => $brand['website'],
                        'logo' => $brand['logo'],
                        'outlet_id' => $outlet->id,
                        'sort_order' => $brand['sort_order'],
                        'is_active' => true,
                    ]
                );
                $createdCount++;
            }
        }

        $this->command->info("Brands seeded successfully. Created {$createdCount} brands for {$outlets->count()} outlets.");
    }
}
