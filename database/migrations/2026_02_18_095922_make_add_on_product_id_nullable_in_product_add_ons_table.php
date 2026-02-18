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
        Schema::table('product_add_ons', function (Blueprint $table) {
            // Make add_on_product_id nullable (standalone add-ons don't need to link to a product)
            $table->foreignId('add_on_product_id')->nullable()->change();

            // Make name required for standalone add-ons
            $table->string('name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_add_ons', function (Blueprint $table) {
            $table->foreignId('add_on_product_id')->nullable(false)->change();
            $table->string('name')->nullable()->change();
        });
    }
};
