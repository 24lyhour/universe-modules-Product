<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_upsells', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('upsell_product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('type', ['upsell', 'downsell', 'cross_sell'])->default('upsell');
            $table->decimal('discount_percentage', 5, 2)->nullable()->comment('Optional discount when purchased together');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Prevent duplicate entries
            $table->unique(['product_id', 'upsell_product_id', 'type'], 'product_upsell_unique');

            // Indexes for quick lookups
            $table->index(['product_id', 'type', 'is_active']);
            $table->index(['upsell_product_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_upsells');
    }
};
