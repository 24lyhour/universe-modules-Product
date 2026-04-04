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
        Schema::create('product_promotion', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_promotion_type'); // example: food, drink, etc.
            $table->decimal('percentage', 5, 2)->nullable(); 
            // example: 10.50 (%)
            $table->decimal('price_discounts', 10, 2)->nullable(); 
            // example: 2.50 ($ discount)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_promotion');
    }
};
