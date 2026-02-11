<?php

namespace Modules\Product\Console\Commands;

use Illuminate\Console\Command;

class ProductCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:create
                            {name : The name of the product}
                            {--price= : The price of the product}
                            {--category= : The category of the product}
                            {--description= : The description of the product}
                            {--sku= : The SKU of the product}
                            {--stock=0 : The initial stock quantity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new product in the system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $price = $this->option('price');
        $category = $this->option('category');
        $description = $this->option('description');
        $sku = $this->option('sku');
        $stock = $this->option('stock');

        $this->info('Creating new product...');
        $this->line('------------------------');

        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Price', $price ?? 'Not set'],
                ['Category', $category ?? 'Not set'],
                ['Description', $description ?? 'Not set'],
                ['SKU', $sku ?? 'Auto-generated'],
                ['Stock', $stock],
            ]
        );

        // TODO: Add logic to create product in database
        // Example:
        // $product = Product::create([
        //     'name' => $name,
        //     'price' => $price,
        //     'category' => $category,
        //     'description' => $description,
        //     'sku' => $sku ?? Str::uuid(),
        //     'stock' => $stock,
        // ]);

        $this->newLine();
        $this->warn('Product creation pending. Database integration required.');

        return Command::SUCCESS;
    }
}
