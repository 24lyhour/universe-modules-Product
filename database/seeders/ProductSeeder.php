<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run all Product module seeders in order.
     *
     * Order is important:
     * 1. ProductTypeSeeder - Creates product types per outlet (Food, Beverage, etc.)
     * 2. BrandSeeder - Creates brands per outlet
     * 3. ProductAttributeSeeder - Creates product attributes
     * 4. ProductDatabaseSeeder - Creates products linked to types and outlets
     * 5. ProductVariantSeeder - Creates product variants
     */
    public function run(): void
    {
        $this->call([
            ProductTypeSeeder::class,
            BrandSeeder::class,
            ProductAttributeSeeder::class,
            ProductDatabaseSeeder::class,
            ProductVariantSeeder::class,
        ]);
    }
}
