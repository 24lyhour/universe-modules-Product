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
     * 2. ProductAttributeSeeder - Creates product attributes
     * 3. ProductDatabaseSeeder - Creates products linked to types and outlets
     * 4. ProductVariantSeeder - Creates product variants
     */
    public function run(): void
    {
        $this->call([
            ProductTypeSeeder::class,
            ProductAttributeSeeder::class,
            ProductDatabaseSeeder::class,
            ProductVariantSeeder::class,
        ]);
    }
}
