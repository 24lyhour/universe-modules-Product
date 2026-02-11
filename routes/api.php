<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\Api\V1\ProductApiController;
use Modules\Product\Http\Controllers\Api\V1\ProductStatusApiController;

// Protected product routes (requires authentication)
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Product CRUD
    Route::apiResource('products', ProductApiController::class)
        ->names('product.api');

    // Product Stats & Search
    Route::get('products-stats', [ProductApiController::class, 'stats'])
        ->name('product.api.stats');
    Route::get('products-search', [ProductApiController::class, 'search'])
        ->name('product.api.search');

    // Product Status Actions
    Route::prefix('products/{product}')->name('product.api.')->group(function () {
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
