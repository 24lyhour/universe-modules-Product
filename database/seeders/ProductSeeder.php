<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run all Product module seeders in order.
     */
    public function run(): void
    {
        $this->call([
            ProductAttributeSeeder::class,
            ProductDatabaseSeeder::class,
            ProductVariantSeeder::class,
        ]);
    }
}
