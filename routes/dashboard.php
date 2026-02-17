<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\Dashboard\V1\ProductAttributeController;
use Modules\Product\Http\Controllers\Dashboard\V1\ProductController;
use Modules\Product\Http\Controllers\Dashboard\V1\ProductUpsellController;
use Modules\Product\Http\Controllers\Dashboard\V1\ProductVariantController;
use Modules\Product\Http\Middleware\DashboardMiddleware;

Route::middleware(['auth', 'verified', DashboardMiddleware::class])
    ->prefix('dashboard')
    ->group(function () {
        // Product Attributes CRUD (must be before products resource to avoid conflict)
        Route::resource('products/attributes', ProductAttributeController::class)
            ->names('dashboard.product.attributes')
            ->parameters(['attributes' => 'attribute']);
        Route::patch('products/attributes/{attribute}/toggle-status', [ProductAttributeController::class, 'toggleStatus'])
            ->name('dashboard.product.attributes.toggle-status');

        // Products CRUD
        Route::resource('products', ProductController::class)->names('product.products');

        // Additional product actions
        Route::patch('products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])
            ->name('product.products.toggle-featured');
        Route::patch('products/{product}/status', [ProductController::class, 'updateStatus'])
            ->name('product.products.update-status');

        // Product Attributes Management (modal)
        Route::get('products/{product}/attributes/manage', [ProductController::class, 'manageAttributes'])
            ->name('product.products.attributes.manage');
        Route::post('products/{product}/attributes/sync', [ProductController::class, 'syncAttributes'])
            ->name('product.products.attributes.sync');

        // Product Variants (nested under products)
        Route::resource('products/{product}/variants', ProductVariantController::class)
            ->names('dashboard.product.variants')
            ->parameters(['variants' => 'variant']);

        // Product Upsells (nested under products)
        Route::resource('products/{product}/upsells', ProductUpsellController::class)
            ->names('dashboard.product.upsells')
            ->parameters(['upsells' => 'upsell'])
            ->except(['show']);
        Route::patch('products/{product}/upsells/{upsell}/toggle-status', [ProductUpsellController::class, 'toggleStatus'])
            ->name('dashboard.product.upsells.toggle-status');
        Route::post('products/{product}/upsells/reorder', [ProductUpsellController::class, 'reorder'])
            ->name('dashboard.product.upsells.reorder');
    });
