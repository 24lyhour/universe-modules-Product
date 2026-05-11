<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop unique constraint if it exists
        $indexes = Schema::getIndexes('product_types');
        $hasSlugOutletUnique = false;
        foreach ($indexes as $index) {
            if ($index['name'] === 'product_types_slug_outlet_id_unique') {
                $hasSlugOutletUnique = true;
                break;
            }
        }

        if ($hasSlugOutletUnique) {
            Schema::table('product_types', function (Blueprint $table) {
                $table->dropUnique(['slug', 'outlet_id']);
            });
        }

        // Clean up duplicates before adding unique constraint
        $duplicates = DB::table('product_types')
            ->select('slug', DB::raw('MIN(id) as min_id'))
            ->groupBy('slug')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('product_types')
                ->where('slug', $duplicate->slug)
                ->where('id', '>', $duplicate->min_id)
                ->delete();
        }

        Schema::table('product_types', function (Blueprint $table) {
            // Drop foreign key if it exists
            $foreignKeys = Schema::getForeignKeys('product_types');
            $hasOutletIdFk = false;
            foreach ($foreignKeys as $fk) {
                if (in_array('outlet_id', $fk['columns'])) {
                    $hasOutletIdFk = true;
                    break;
                }
            }
            
            if ($hasOutletIdFk) {
                $table->dropForeign(['outlet_id']);
            }
            
            if (Schema::hasColumn('product_types', 'outlet_id')) {
                $table->dropColumn('outlet_id');
            }
            
            // Check if slug unique index already exists
            $indexes = Schema::getIndexes('product_types');
            $hasSlugUnique = false;
            foreach ($indexes as $index) {
                if ($index['name'] === 'product_types_slug_unique') {
                    $hasSlugUnique = true;
                    break;
                }
            }
            
            if (!$hasSlugUnique) {
                $table->unique('slug');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_types', function (Blueprint $table) {
            $indexes = Schema::getIndexes('product_types');
            $hasSlugUnique = false;
            foreach ($indexes as $index) {
                if ($index['name'] === 'product_types_slug_unique') {
                    $hasSlugUnique = true;
                    break;
                }
            }
            
            if ($hasSlugUnique) {
                $table->dropUnique(['slug']);
            }
            
            if (!Schema::hasColumn('product_types', 'outlet_id')) {
                $table->foreignId('outlet_id')->nullable()->constrained()->onDelete('cascade');
            }
            
            $indexes = Schema::getIndexes('product_types');
            $hasSlugOutletUnique = false;
            foreach ($indexes as $index) {
                if ($index['name'] === 'product_types_slug_outlet_id_unique') {
                    $hasSlugOutletUnique = true;
                    break;
                }
            }
            
            if (!$hasSlugOutletUnique) {
                $table->unique(['slug', 'outlet_id']);
            }
        });
    }
};
