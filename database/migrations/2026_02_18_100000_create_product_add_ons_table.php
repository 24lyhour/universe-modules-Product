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
        Schema::create('product_add_ons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('add_on_product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('price_adjustment', 10, 2)->default(0); // Additional price for this add-on
            $table->integer('max_quantity')->default(1); // Maximum quantity allowed
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(false); // Required add-on?
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'add_on_product_id']);
            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_add_ons');
    }
};
