<?php

namespace Modules\Product\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Outlet\Models\Outlet;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductType;

class ProductCreateCommand extends Command
{
    protected $signature = 'product:create
                            {--count=1 : Number of products to create}
                            {--outlet= : Outlet ID}
                            {--type= : Product type ID}
                            {--brand= : Brand ID}
                            {--status=active : Product status (active, inactive, draft)}
                            {--featured : Mark as featured}
                            {--interactive : Interactive mode}';

    protected $description = 'Create test products for development/testing';

    protected array $productNames = [
        'food' => ['Grilled Salmon', 'Beef Steak', 'Chicken Pasta', 'Veggie Bowl', 'Fish Tacos', 'BBQ Ribs', 'Lamb Chops', 'Shrimp Risotto'],
        'beverage' => ['Iced Latte', 'Mango Smoothie', 'Green Tea', 'Fresh Orange Juice', 'Cappuccino', 'Matcha Latte', 'Berry Shake', 'Espresso'],
        'dessert' => ['Chocolate Cake', 'Tiramisu', 'Cheesecake', 'Ice Cream Sundae', 'Apple Pie', 'Brownie', 'Panna Cotta', 'Crème Brûlée'],
        'gadget' => ['Wireless Earbuds', 'Smart Watch', 'Tablet Pro', 'Bluetooth Speaker', 'Power Bank', 'USB Hub', 'Webcam HD', 'Gaming Mouse'],
        'clothing' => ['Cotton T-Shirt', 'Denim Jeans', 'Hoodie', 'Sneakers', 'Jacket', 'Polo Shirt', 'Cargo Pants', 'Running Shoes'],
        'book' => ['Programming Guide', 'Novel Collection', 'Business Strategy', 'Self Help Book', 'Cookbook', 'Art History', 'Science Fiction', 'Biography'],
        'default' => ['Product A', 'Product B', 'Product C', 'Product D', 'Product E', 'Product F', 'Product G', 'Product H'],
    ];

    public function handle(): int
    {
        $this->info('Create Test Products');
        $this->line('--------------------');

        if ($this->option('interactive')) {
            return $this->createInteractive();
        }

        return $this->createFromOptions();
    }

    protected function createInteractive(): int
    {
        $count = (int) $this->ask('How many products to create?', 1);

        $outlets = Outlet::pluck('name', 'id')->toArray();
        $outletId = null;

        if (!empty($outlets)) {
            $outletChoice = $this->choice(
                'Select outlet (or skip for random)',
                ['random' => 'Random Outlet'] + $outlets,
                'random'
            );
            $outletId = $outletChoice !== 'random' ? array_search($outletChoice, $outlets) : null;
        }

        $productTypes = ProductType::when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->pluck('name', 'id')
            ->toArray();
        $typeId = null;

        if (!empty($productTypes)) {
            $typeChoice = $this->choice(
                'Select product type (or skip for random)',
                ['random' => 'Random Type'] + $productTypes,
                'random'
            );
            $typeId = $typeChoice !== 'random' ? array_search($typeChoice, $productTypes) : null;
        }

        $brands = Brand::when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->pluck('name', 'id')
            ->toArray();
        $brandId = null;

        if (!empty($brands)) {
            $brandChoice = $this->choice(
                'Select brand (or skip for random)',
                ['random' => 'Random Brand', 'none' => 'No Brand'] + $brands,
                'random'
            );
            $brandId = match ($brandChoice) {
                'random' => null,
                'none' => 0,
                default => array_search($brandChoice, $brands),
            };
        }

        $status = $this->choice(
            'Product status',
            ['active' => 'Active', 'inactive' => 'Inactive', 'draft' => 'Draft'],
            'active'
        );

        $featured = $this->confirm('Mark as featured?', false);

        return $this->createProducts($count, $outletId, $typeId, $brandId, $status, $featured);
    }

    protected function createFromOptions(): int
    {
        $count = (int) $this->option('count');
        $outletId = $this->option('outlet');
        $typeId = $this->option('type');
        $brandId = $this->option('brand');
        $status = $this->option('status') ?? 'active';
        $featured = $this->option('featured');

        return $this->createProducts($count, $outletId, $typeId, $brandId, $status, $featured);
    }

    protected function createProducts(int $count, ?int $outletId, ?int $typeId, ?int $brandId, string $status, bool $featured): int
    {
        $this->newLine();
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $products = [];

        for ($i = 0; $i < $count; $i++) {
            $product = $this->createSingleProduct($outletId, $typeId, $brandId, $status, $featured);
            $products[] = $product;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Created {$count} test product(s) successfully!");
        $this->newLine();

        // Show summary table
        $this->info('Products Summary:');
        $this->table(
            ['ID', 'SKU', 'Name', 'Outlet', 'Type', 'Brand', 'Price', 'Sale', 'Stock', 'Status', 'Featured'],
            collect($products)->map(fn ($product) => [
                $product->id,
                $product->sku,
                Str::limit($product->name, 20),
                $product->outlet?->name ?? '-',
                $product->productType?->name ?? '-',
                $product->brand?->name ?? '-',
                '$' . number_format($product->price, 2),
                $product->sale_price ? '$' . number_format($product->sale_price, 2) : '-',
                $product->stock,
                $product->status,
                $product->is_featured ? 'Yes' : 'No',
            ])->toArray()
        );

        return Command::SUCCESS;
    }

    protected function createSingleProduct(?int $outletId, ?int $typeId, ?int $brandId, string $status, bool $featured): Product
    {
        // Get random outlet if not specified
        $outlet = $outletId
            ? Outlet::find($outletId)
            : Outlet::inRandomOrder()->first();

        // Get random product type for outlet
        $productType = $typeId
            ? ProductType::find($typeId)
            : ProductType::where('outlet_id', $outlet?->id)->inRandomOrder()->first();

        // Get random brand for outlet (0 means no brand)
        $brand = null;
        if ($brandId === null) {
            $brand = Brand::where('outlet_id', $outlet?->id)->inRandomOrder()->first();
        } elseif ($brandId > 0) {
            $brand = Brand::find($brandId);
        }

        // Generate product data
        $typeSlug = $productType?->slug ?? 'default';
        $names = $this->productNames[$typeSlug] ?? $this->productNames['default'];
        $name = $names[array_rand($names)] . ' ' . Str::random(4);

        $price = $this->generatePrice($typeSlug);
        $purchasePrice = $price * rand(40, 70) / 100;
        $salePrice = rand(0, 100) > 70 ? $price * rand(80, 95) / 100 : null;

        $product = Product::create([
            'uuid' => Str::uuid(),
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => strtoupper(substr($typeSlug, 0, 4)) . '-' . strtoupper(Str::random(6)),
            'description' => "Test product: {$name}. Created via CLI for development/testing purposes.",
            'outlet_id' => $outlet?->id,
            'product_type_id' => $productType?->id,
            'product_type' => $typeSlug,
            'brand_id' => $brand?->id,
            'price' => $price,
            'purchase_price' => round($purchasePrice, 2),
            'sale_price' => $salePrice ? round($salePrice, 2) : null,
            'stock' => rand(10, 200),
            'low_stock_threshold' => rand(5, 20),
            'status' => $status,
            'is_featured' => $featured || rand(0, 100) > 80,
            'pre_order' => rand(0, 100) > 90,
            'images' => [],
        ]);

        return $product->fresh(['outlet', 'productType', 'brand']);
    }

    protected function generatePrice(string $typeSlug): float
    {
        return match ($typeSlug) {
            'food' => rand(800, 3500) / 100,
            'beverage' => rand(300, 1200) / 100,
            'dessert' => rand(500, 1500) / 100,
            'gadget' => rand(2999, 99999) / 100,
            'clothing' => rand(1999, 19999) / 100,
            'book' => rand(999, 5999) / 100,
            default => rand(500, 10000) / 100,
        };
    }
}
