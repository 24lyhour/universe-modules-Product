<?php

namespace Modules\Product\Console\Commands;

use Illuminate\Console\Command;

class ProductCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:list
                            {--active : Show only active products}
                            {--category= : Filter by category}
                            {--limit=10 : Limit the number of results}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all products in the system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Product Module Command');
        $this->line('----------------------');

        $limit = $this->option('limit');
        $category = $this->option('category');

        if ($this->option('active')) {
            $this->info('Showing active products only...');
        } else {
            $this->info('Showing all products...');
        }

        if ($category) {
            $this->info("Filtering by category: {$category}");
        }

        $this->info("Limit: {$limit}");

        // TODO: Add logic to fetch and display products from database
        $this->warn('No products found. Database integration pending.');

        return Command::SUCCESS;
    }
}
