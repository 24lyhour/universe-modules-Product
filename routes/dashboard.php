<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\Dashboard\V1\ProductAttributeController;
use Modules\Product\Http\Controllers\Dashboard\V1\ProductController;
use Modules\Product\Http\Controllers\Dashboard\V1\ProductAddOnController;
use Modules\Product\Http\Controllers\Dashboard\V1\ProductTypeController;
use Modules\Product\Http\Controllers\Dashboard\V1\ProductUpsellController;
use Modules\Product\Http\Controllers\Dashboard\V1\ProductVariantController;
use Modules\Product\Http\Controllers\Dashboard\V1\ProductSettingsController;
use Modules\Product\Http\Middleware\DashboardMiddleware;

Route::middleware(['auth', 'verified', DashboardMiddleware::class, 'auto.permission'])
    ->prefix('dashboard')
    ->group(function () {
        // Product Settings (must be before products resource to avoid conflict)
        Route::get('products/settings', [ProductSettingsController::class, 'index'])
            ->name('dashboard.product.settings');
        Route::post('products/settings', [ProductSettingsController::class, 'update'])
            ->name('dashboard.product.settings.update');

        // Product Attributes Bulk Delete (must be before resource to avoid conflict)
        Route::get('products/attributes/bulk-delete', [ProductAttributeController::class, 'confirmBulkDelete'])
            ->name('dashboard.product.attributes.bulk-delete.confirm');
        Route::delete('products/attributes/bulk-delete', [ProductAttributeController::class, 'bulkDelete'])
            ->name('dashboard.product.attributes.bulk-delete');

        // Product Attributes Trash routes
        Route::get('products/attributes/trash', [ProductAttributeController::class, 'trash'])
            ->name('dashboard.product.attributes.trash');
        Route::get('products/attributes/export', [ProductAttributeController::class, 'export'])
            ->name('dashboard.product.attributes.export');
        Route::delete('products/attributes/trash/empty', [ProductAttributeController::class, 'emptyTrash'])
            ->name('dashboard.product.attributes.trash.empty');
        Route::put('products/attributes/trash/bulk-restore', [ProductAttributeController::class, 'bulkRestore'])
            ->name('dashboard.product.attributes.trash.bulk-restore');
        Route::delete('products/attributes/trash/bulk-force-delete', [ProductAttributeController::class, 'bulkForceDelete'])
            ->name('dashboard.product.attributes.trash.bulk-force-delete');
        Route::put('products/attributes/{uuid}/restore', [ProductAttributeController::class, 'restore'])
            ->name('dashboard.product.attributes.restore');
        Route::delete('products/attributes/{uuid}/force-delete', [ProductAttributeController::class, 'forceDelete'])
            ->name('dashboard.product.attributes.force-delete');

        // Product Attributes CRUD (must be before products resource to avoid conflict)
        Route::resource('products/attributes', ProductAttributeController::class)
            ->names('dashboard.product.attributes')
            ->parameters(['attributes' => 'attribute']);
        Route::patch('products/attributes/{attribute}/toggle-status', [ProductAttributeController::class, 'toggleStatus'])
            ->name('dashboard.product.attributes.toggle-status');

        // Product Add-ons standalone routes (must be before products resource to avoid conflict)
        Route::get('products/addons', [ProductAddOnController::class, 'indexAll'])
            ->name('dashboard.product.addons.all');
        Route::get('products/addons/create', [ProductAddOnController::class, 'createStandalone'])
            ->name('dashboard.product.addons.create-standalone');
        Route::post('products/addons', [ProductAddOnController::class, 'storeStandalone'])
            ->name('dashboard.product.addons.store-standalone');

        // Product Add-ons Trash routes
        Route::get('products/addons/trash', [ProductAddOnController::class, 'trash'])
            ->name('dashboard.product.addons.trash');
        Route::get('products/addons/export', [ProductAddOnController::class, 'export'])
            ->name('dashboard.product.addons.export');
        Route::delete('products/addons/bulk-delete', [ProductAddOnController::class, 'bulkDelete'])
            ->name('dashboard.product.addons.bulk-delete');
        Route::delete('products/addons/trash/empty', [ProductAddOnController::class, 'emptyTrash'])
            ->name('dashboard.product.addons.trash.empty');
        Route::put('products/addons/trash/bulk-restore', [ProductAddOnController::class, 'bulkRestore'])
            ->name('dashboard.product.addons.trash.bulk-restore');
        Route::delete('products/addons/trash/bulk-force-delete', [ProductAddOnController::class, 'bulkForceDelete'])
            ->name('dashboard.product.addons.trash.bulk-force-delete');
        Route::put('products/addons/{uuid}/restore', [ProductAddOnController::class, 'restore'])
            ->name('dashboard.product.addons.restore');
        Route::delete('products/addons/{uuid}/force-delete', [ProductAddOnController::class, 'forceDelete'])
            ->name('dashboard.product.addons.force-delete');
        Route::delete('products/addons/{addon}/delete', [ProductAddOnController::class, 'destroyGlobal'])
            ->name('dashboard.product.addons.destroy-global');

        // Products Bulk Delete (must be before products resource to avoid conflict)
        Route::get('products/bulk-delete', [ProductController::class, 'confirmBulkDelete'])
            ->name('product.products.bulk-delete.confirm');
        Route::delete('products/bulk-delete', [ProductController::class, 'bulkDelete'])
            ->name('product.products.bulk-delete');

        // Products Trash (must be before products resource to avoid conflict)
        Route::get('products/trash', [ProductController::class, 'trash'])
            ->name('product.products.trash');
        Route::delete('products/trash/empty', [ProductController::class, 'emptyTrash'])
            ->name('product.products.trash.empty');
        Route::put('products/trash/bulk-restore', [ProductController::class, 'bulkRestore'])
            ->name('product.products.trash.bulk-restore');
        Route::delete('products/trash/bulk-force-delete', [ProductController::class, 'bulkForceDelete'])
            ->name('product.products.trash.bulk-force-delete');
        Route::put('products/{uuid}/restore', [ProductController::class, 'restore'])
            ->name('product.products.restore');
        Route::delete('products/{uuid}/force-delete', [ProductController::class, 'forceDelete'])
            ->name('product.products.force-delete');

        // Products Import/Export/Template (must be before products resource to avoid conflict)
        Route::get('products/import', [ProductController::class, 'import'])
            ->name('product.products.import');
        Route::post('products/import/preview', [ProductController::class, 'previewImport'])
            ->name('product.products.import.preview');
        Route::post('products/import', [ProductController::class, 'processImport'])
            ->name('product.products.import.process');
        Route::get('products/export', [ProductController::class, 'export'])
            ->name('product.products.export');
        Route::get('products/template', [ProductController::class, 'template'])
            ->name('product.products.template');

        // Product Types Bulk Delete (must be before resource to avoid conflict)
        Route::get('product-types/bulk-delete', [ProductTypeController::class, 'confirmBulkDelete'])
            ->name('product.product-types.bulk-delete.confirm');
        Route::delete('product-types/bulk-delete', [ProductTypeController::class, 'bulkDelete'])
            ->name('product.product-types.bulk-delete');

        // Product Types Trash (must be before resource to avoid conflict)
        Route::get('product-types/trash', [ProductTypeController::class, 'trash'])
            ->name('product.product-types.trash');
        Route::delete('product-types/trash/empty', [ProductTypeController::class, 'emptyTrash'])
            ->name('product.product-types.trash.empty');
        Route::put('product-types/trash/bulk-restore', [ProductTypeController::class, 'bulkRestore'])
            ->name('product.product-types.trash.bulk-restore');
        Route::delete('product-types/trash/bulk-force-delete', [ProductTypeController::class, 'bulkForceDelete'])
            ->name('product.product-types.trash.bulk-force-delete');
        Route::put('product-types/{uuid}/restore', [ProductTypeController::class, 'restore'])
            ->name('product.product-types.restore');
        Route::delete('product-types/{uuid}/force-delete', [ProductTypeController::class, 'forceDelete'])
            ->name('product.product-types.force-delete');

        // Product Types Export
        Route::get('product-types/export', [ProductTypeController::class, 'export'])
            ->name('product.product-types.export');

        // Product Types CRUD
        Route::resource('product-types', ProductTypeController::class)
            ->names('product.product-types')
            ->parameters(['product-types' => 'productType']);

        // Product Types Toggle Status
        Route::put('product-types/{productType}/toggle-status', [ProductTypeController::class, 'toggleStatus'])
            ->name('product.product-types.toggle-status');

        // Products CRUD
        Route::resource('products', ProductController::class)->names('product.products');

        // Product delete confirmation modal (must be after resource to use route model binding)
        Route::get('products/{product}/delete', [ProductController::class, 'confirmDelete'])
            ->name('product.products.delete');

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

        // Product Add-ons (nested under products)
        Route::resource('products/{product}/addons', ProductAddOnController::class)
            ->names('dashboard.product.addons')
            ->parameters(['addons' => 'addon'])
            ->except(['show']);
        Route::patch('products/{product}/addons/{addon}/toggle-status', [ProductAddOnController::class, 'toggleStatus'])
            ->name('dashboard.product.addons.toggle-status');
        Route::post('products/{product}/addons/reorder', [ProductAddOnController::class, 'reorder'])
            ->name('dashboard.product.addons.reorder');
    });
