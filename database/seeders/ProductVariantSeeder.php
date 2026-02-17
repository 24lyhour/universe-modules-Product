<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAttribute;
use Modules\Product\Models\ProductAttributeValue;
use Modules\Product\Models\ProductVariant;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get attributes
        $sizeAttr = ProductAttribute::where('slug', 'size')->first();
        $spiceAttr = ProductAttribute::where('slug', 'spice-level')->first();
        $tempAttr = ProductAttribute::where('slug', 'temperature')->first();

        if (!$sizeAttr) {
            $this->command->warn('No attributes found. Please run ProductAttributeSeeder first.');
            return;
        }

        // Get some products to add variants to
        $burgers = Product::where('name', 'like', '%Burger%')->get();
        $drinks = Product::where('product_type', 'beverage')->get();

        $variantCount = 0;

        // Add size variants to burgers
        foreach ($burgers as $product) {
            // Attach size attribute to product
            $product->attributes()->syncWithoutDetaching([
                $sizeAttr->id => ['sort_order' => 1, 'is_required' => true],
            ]);

            if ($spiceAttr) {
                $product->attributes()->syncWithoutDetaching([
                    $spiceAttr->id => ['sort_order' => 2, 'is_required' => false],
                ]);
            }

            // Create size variants
            $sizeValues = $sizeAttr->values()->get();
            foreach ($sizeValues as $index => $sizeValue) {
                $variant = ProductVariant::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'sku' => $product->sku . '-' . strtoupper($sizeValue->value),
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'name' => $product->name . ' - ' . $sizeValue->label,
                        'price' => $product->price + $sizeValue->price_adjustment,
                        'stock' => rand(10, 50),
                        'low_stock_threshold' => 5,
                        'is_default' => $index === 1, // Medium as default
                        'is_active' => true,
                        'sort_order' => $sizeValue->sort_order,
                    ]
                );

                // Attach attribute value to variant
                $variant->attributeValueRelations()->syncWithoutDetaching([$sizeValue->id]);
                $variantCount++;
            }
        }

        // Add temperature variants to drinks
        if ($tempAttr) {
            foreach ($drinks as $product) {
                // Attach temperature attribute to product
                $product->attributes()->syncWithoutDetaching([
                    $tempAttr->id => ['sort_order' => 1, 'is_required' => true],
                ]);

                // Create temperature variants
                $tempValues = $tempAttr->values()->get();
                foreach ($tempValues as $index => $tempValue) {
                    $variant = ProductVariant::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'sku' => $product->sku . '-' . strtoupper($tempValue->value),
                        ],
                        [
                            'uuid' => Str::uuid(),
                            'name' => $product->name . ' - ' . $tempValue->label,
                            'price' => $product->price + $tempValue->price_adjustment,
                            'stock' => rand(20, 100),
                            'low_stock_threshold' => 10,
                            'is_default' => $index === 0, // Hot as default
                            'is_active' => true,
                            'sort_order' => $tempValue->sort_order,
                        ]
                    );

                    // Attach attribute value to variant
                    $variant->attributeValueRelations()->syncWithoutDetaching([$tempValue->id]);
                    $variantCount++;
                }
            }
        }

        $this->command->info("Product variants seeded: {$variantCount} variants created.");
    }
}
