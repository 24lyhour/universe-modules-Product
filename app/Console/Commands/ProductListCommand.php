<?php

namespace Modules\Product\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Product\Models\Product;

class ProductListCommand extends Command
{
    protected $signature = 'product:list
                            {--status= : Filter by status (active, inactive, draft)}
                            {--outlet= : Filter by outlet ID}
                            {--type= : Filter by product type ID}
                            {--brand= : Filter by brand ID}
                            {--featured : Show only featured products}
                            {--low-stock : Show only low stock products}
                            {--limit=10 : Number of products to show}';

    protected $description = 'List products';

    public function handle(): int
    {
        $this->info('Product List');
        $this->line('-------------');

        $query = Product::with(['outlet', 'productType', 'brand']);

        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        if ($outlet = $this->option('outlet')) {
            $query->where('outlet_id', $outlet);
        }

        if ($type = $this->option('type')) {
            $query->where('product_type_id', $type);
        }

        if ($brand = $this->option('brand')) {
            $query->where('brand_id', $brand);
        }

        if ($this->option('featured')) {
            $query->where('is_featured', true);
        }

        if ($this->option('low-stock')) {
            $query->whereColumn('stock', '<=', 'low_stock_threshold')
                ->where('stock', '>', 0);
        }

        $query->orderBy('id', 'desc');

        $limit = (int) $this->option('limit');
        $products = $query->limit($limit)->get();

        if ($products->isEmpty()) {
            $this->warn('No products found.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->table(
            ['ID', 'SKU', 'Name', 'Outlet', 'Type', 'Brand', 'Price', 'Sale', 'Stock', 'Status', 'Featured'],
            $products->map(fn ($product) => [
                $product->id,
                $product->sku,
                Str::limit($product->name, 25),
                Str::limit($product->outlet?->name ?? '-', 15),
                Str::limit($product->productType?->name ?? '-', 12),
                Str::limit($product->brand?->name ?? '-', 12),
                '$' . number_format($product->price, 2),
                $product->sale_price ? '$' . number_format($product->sale_price, 2) : '-',
                $product->stock,
                $product->status,
                $product->is_featured ? 'Yes' : 'No',
            ])->toArray()
        );

        $this->newLine();
        $this->line("Showing {$products->count()} of " . Product::count() . " total products");

        // Show filter summary if any filters applied
        $filters = [];
        if ($this->option('status')) {
            $filters[] = "status={$this->option('status')}";
        }
        if ($this->option('outlet')) {
            $filters[] = "outlet={$this->option('outlet')}";
        }
        if ($this->option('type')) {
            $filters[] = "type={$this->option('type')}";
        }
        if ($this->option('brand')) {
            $filters[] = "brand={$this->option('brand')}";
        }
        if ($this->option('featured')) {
            $filters[] = "featured=true";
        }
        if ($this->option('low-stock')) {
            $filters[] = "low-stock=true";
        }

        if (!empty($filters)) {
            $this->line("Filters: " . implode(', ', $filters));
        }

        // Show stats
        $this->newLine();
        $this->info('Quick Stats:');
        $this->line("  Active: " . Product::where('status', 'active')->count());
        $this->line("  Featured: " . Product::where('is_featured', true)->count());
        $this->line("  Low Stock: " . Product::whereColumn('stock', '<=', 'low_stock_threshold')->where('stock', '>', 0)->count());
        $this->line("  Out of Stock: " . Product::where('stock', 0)->count());

        return Command::SUCCESS;
    }
}
