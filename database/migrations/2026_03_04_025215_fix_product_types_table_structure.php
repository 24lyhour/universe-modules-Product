<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_types', function (Blueprint $table) {
            // Drop the incorrect product_id column
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');

            // Add missing columns
            $table->string('slug')->unique()->after('name');
            $table->integer('sort_order')->default(0)->after('outlet_id');

            // Fix is_active to be boolean
            $table->boolean('is_active_new')->default(true)->after('sort_order');
        });

        // Convert existing is_active values
        DB::table('product_types')->update([
            'is_active_new' => DB::raw("CASE WHEN is_active = 'active' THEN 1 ELSE 0 END"),
        ]);

        Schema::table('product_types', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('product_types', function (Blueprint $table) {
            $table->renameColumn('is_active_new', 'is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_types', function (Blueprint $table) {
            $table->dropColumn(['slug', 'sort_order']);
            $table->string('is_active')->default('active')->change();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
