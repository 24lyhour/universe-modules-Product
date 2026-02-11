<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Middleware\DashboardMiddleware;

Route::middleware(['auth', 'verified', DashboardMiddleware::class])
    ->prefix('dashboard')
    ->group(function () {
        // Products CRUD
        Route::resource('products', ProductController::class)->names('product.products');

        // Additional product actions
        Route::patch('products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])
            ->name('product.products.toggle-featured');
        Route::patch('products/{product}/status', [ProductController::class, 'updateStatus'])
            ->name('product.products.update-status');
    });
