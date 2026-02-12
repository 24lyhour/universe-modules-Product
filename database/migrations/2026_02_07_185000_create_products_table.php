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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('tenant_type')->nullable();
            $table->unsignedBigInteger('outlet_id')->nullable();
            $table->string('outlet_type')->nullable();
            $table->text('description')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('product_type')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->unsignedBigInteger('upsale_id')->nullable();
            $table->unsignedBigInteger('down_sale_id')->nullable();
            $table->unsignedBigInteger('varitations_id')->nullable();
            $table->unsignedBigInteger('add_ons_id')->nullable();
            $table->integer('low_stock_threshold')->default(10);
            $table->string('status')->default('active');
            $table->boolean('is_featured')->default(false);
            $table->boolean('pre_order')->default(false);
            $table->json('images')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tenant_id');
            $table->index('outlet_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
