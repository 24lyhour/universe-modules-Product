<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\Api\V1\Product\ProductApiController;
use Modules\Product\Http\Controllers\Api\V1\Product\ProductPublicController;
use Modules\Product\Http\Controllers\Api\V1\Product\ProductStatusApiController;

/*
|--------------------------------------------------------------------------
| Public Routes (no authentication required)
|--------------------------------------------------------------------------
| These routes are for mobile app to browse products
*/
Route::prefix('v1')->group(function () {
    // Public product listing
    Route::get('products', [ProductPublicController::class, 'index'])
        ->name('product.public.index');

    // Get product types for filtering
    Route::get('product-types', [ProductPublicController::class, 'types'])
        ->name('product.public.types');

    // Search products
    Route::get('products-search', [ProductPublicController::class, 'search'])
        ->name('product.public.search');

    // Featured products for home page
    Route::get('products-featured', [ProductPublicController::class, 'featured'])
        ->name('product.public.featured');

    // New arrivals for home page
    Route::get('products-new-arrivals', [ProductPublicController::class, 'newArrivals'])
        ->name('product.public.new-arrivals');

    // On-sale products
    Route::get('products-on-sale', [ProductPublicController::class, 'onSale'])
        ->name('product.public.on-sale');

    // Single product detail (by UUID or slug)
    Route::get('products/{identifier}', [ProductPublicController::class, 'show'])
        ->name('product.public.show');

    // Related products
    Route::get('products/{identifier}/related', [ProductPublicController::class, 'related'])
        ->name('product.public.related');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (requires authentication)
|--------------------------------------------------------------------------
| These routes are for admin/dashboard API
*/
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Product CRUD (admin)
    Route::apiResource('admin/products', ProductApiController::class)
        ->names('product.admin');

    // Product Stats
    Route::get('admin/products-stats', [ProductApiController::class, 'stats'])
        ->name('product.admin.stats');

    // Product Status Actions
    Route::prefix('admin/products/{product}')->name('product.admin.')->group(function () {
        Route::patch('activate', [ProductStatusApiController::class, 'activate'])
            ->name('activate');
        Route::patch('deactivate', [ProductStatusApiController::class, 'deactivate'])
            ->name('deactivate');
        Route::patch('draft', [ProductStatusApiController::class, 'setDraft'])
            ->name('draft');
        Route::patch('toggle-featured', [ProductStatusApiController::class, 'toggleFeatured'])
            ->name('toggle-featured');
        Route::patch('stock', [ProductStatusApiController::class, 'updateStock'])
            ->name('update-stock');
    });
});
