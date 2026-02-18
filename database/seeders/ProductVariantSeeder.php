<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAttribute;
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
        $colorAttr = ProductAttribute::where('slug', 'color')->first();
        $spiceAttr = ProductAttribute::where('slug', 'spice-level')->first();
        $tempAttr = ProductAttribute::where('slug', 'temperature')->first();
        $toppingAttr = ProductAttribute::where('slug', 'topping')->first();

        if (!$sizeAttr) {
            $this->command->warn('No attributes found. Please run ProductAttributeSeeder first.');
            return;
        }

        $variantCount = 0;

        // Get products by type
        $burgers = Product::where('name', 'like', '%Burger%')->get();
        $drinks = Product::where('product_type', 'beverage')->get();
        $pizzas = Product::where('name', 'like', '%Pizza%')->get();
        $sandwiches = Product::where('name', 'like', '%Sandwich%')->orWhere('name', 'like', '%Wrap%')->get();
        $wings = Product::where('name', 'like', '%Wing%')->get();

        // ===== BURGERS: Size + Spice Level =====
        foreach ($burgers as $product) {
            // Attach attributes to product
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

                $variant->attributeValueRelations()->syncWithoutDetaching([$sizeValue->id]);
                $variantCount++;
            }
        }

        // ===== DRINKS: Temperature + Size =====
        if ($tempAttr) {
            foreach ($drinks as $product) {
                $product->attributes()->syncWithoutDetaching([
                    $tempAttr->id => ['sort_order' => 1, 'is_required' => true],
                    $sizeAttr->id => ['sort_order' => 2, 'is_required' => true],
                ]);

                // Create temperature + size combo variants
                $tempValues = $tempAttr->values()->get();
                $sizeValues = $sizeAttr->values()->whereIn('value', ['S', 'M', 'L'])->get();

                foreach ($tempValues as $tempValue) {
                    foreach ($sizeValues as $sizeIndex => $sizeValue) {
                        $variant = ProductVariant::firstOrCreate(
                            [
                                'product_id' => $product->id,
                                'sku' => $product->sku . '-' . strtoupper($tempValue->value) . '-' . strtoupper($sizeValue->value),
                            ],
                            [
                                'uuid' => Str::uuid(),
                                'name' => $product->name . ' - ' . $tempValue->label . ' ' . $sizeValue->label,
                                'price' => $product->price + $tempValue->price_adjustment + $sizeValue->price_adjustment,
                                'stock' => rand(20, 100),
                                'low_stock_threshold' => 10,
                                'is_default' => $tempValue->value === 'iced' && $sizeValue->value === 'M',
                                'is_active' => true,
                                'sort_order' => $tempValue->sort_order * 10 + $sizeValue->sort_order,
                            ]
                        );

                        $variant->attributeValueRelations()->syncWithoutDetaching([
                            $tempValue->id,
                            $sizeValue->id,
                        ]);
                        $variantCount++;
                    }
                }
            }
        }

        // ===== PIZZA: Size only =====
        foreach ($pizzas as $product) {
            $product->attributes()->syncWithoutDetaching([
                $sizeAttr->id => ['sort_order' => 1, 'is_required' => true],
            ]);

            $sizeValues = $sizeAttr->values()->get();
            foreach ($sizeValues as $index => $sizeValue) {
                // Different pricing for pizza sizes
                $pizzaPriceAdjustment = match ($sizeValue->value) {
                    'S' => 0,
                    'M' => 4.00,
                    'L' => 8.00,
                    'XL' => 12.00,
                    default => 0,
                };

                $variant = ProductVariant::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'sku' => $product->sku . '-' . strtoupper($sizeValue->value),
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'name' => $product->name . ' - ' . $sizeValue->label,
                        'price' => $product->price + $pizzaPriceAdjustment,
                        'stock' => rand(15, 40),
                        'low_stock_threshold' => 5,
                        'is_default' => $index === 1,
                        'is_active' => true,
                        'sort_order' => $sizeValue->sort_order,
                    ]
                );

                $variant->attributeValueRelations()->syncWithoutDetaching([$sizeValue->id]);
                $variantCount++;
            }
        }

        // ===== SANDWICHES/WRAPS: Size + Topping =====
        foreach ($sandwiches as $product) {
            $product->attributes()->syncWithoutDetaching([
                $sizeAttr->id => ['sort_order' => 1, 'is_required' => true],
            ]);

            if ($toppingAttr) {
                $product->attributes()->syncWithoutDetaching([
                    $toppingAttr->id => ['sort_order' => 2, 'is_required' => false],
                ]);
            }

            // Create size variants
            $sizeValues = $sizeAttr->values()->whereIn('value', ['S', 'M', 'L'])->get();
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
                        'stock' => rand(20, 60),
                        'low_stock_threshold' => 8,
                        'is_default' => $index === 1,
                        'is_active' => true,
                        'sort_order' => $sizeValue->sort_order,
                    ]
                );

                $variant->attributeValueRelations()->syncWithoutDetaching([$sizeValue->id]);
                $variantCount++;
            }
        }

        // ===== WINGS: Spice Level =====
        if ($spiceAttr) {
            foreach ($wings as $product) {
                $product->attributes()->syncWithoutDetaching([
                    $spiceAttr->id => ['sort_order' => 1, 'is_required' => true],
                ]);

                $spiceValues = $spiceAttr->values()->get();
                foreach ($spiceValues as $index => $spiceValue) {
                    $variant = ProductVariant::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'sku' => $product->sku . '-' . strtoupper(str_replace('_', '', $spiceValue->value)),
                        ],
                        [
                            'uuid' => Str::uuid(),
                            'name' => $product->name . ' - ' . $spiceValue->label,
                            'price' => $product->price + $spiceValue->price_adjustment,
                            'stock' => rand(30, 80),
                            'low_stock_threshold' => 10,
                            'is_default' => $index === 1, // Medium as default
                            'is_active' => true,
                            'sort_order' => $spiceValue->sort_order,
                        ]
                    );

                    $variant->attributeValueRelations()->syncWithoutDetaching([$spiceValue->id]);
                    $variantCount++;
                }
            }
        }

        $this->command->info("Product variants seeded: {$variantCount} variants created.");
    }
}
