<?php

namespace Modules\Product\Http\Middleware;

use App\Services\MenuService;
use Closure;
use Illuminate\Http\Request;

class DashboardMiddlewareHandle
{
    protected static bool $registered = false;

    public function handle(Request $request, Closure $next)
    {
        if ($request->is('dashboard', 'dashboard/*')) {
            $this->registerMenuItems();
        }

        return $next($request);
    }

    protected function registerMenuItems(): void
    {
        if (static::$registered) {
            return;
        }

        MenuService::addMenuItem(
            'primary',
            'product',
            __('Products'),
            '/dashboard/products',
            'LayoutGrid',
            20,
            'products.view_any',
            'product.products.*'
        );

        MenuService::addSubmenuItem('primary', 'product', __('Product Types'), '/dashboard/product-types', 15, 'product_types.view_any', 'product.product-types.index', 'PackageSearch');
        MenuService::addSubmenuItem('primary', 'product', __('Brands'), '/dashboard/brands', 17, 'brands.view_any', 'product.brands.index', 'Tag');
        MenuService::addSubmenuItem('primary', 'product', __('Products'), '/dashboard/products', 10, 'products.view_any', 'product.products.index', 'LayoutGrid');
        MenuService::addSubmenuItem('primary', 'product', __('Attributes'), '/dashboard/products/attributes', 20, 'product_attributes.view_any', 'dashboard.product.attributes.index', 'Tags');
        MenuService::addSubmenuItem('primary', 'product', __('Add-ons'), '/dashboard/products/addons', 30, 'product_add_ons.view_any', 'dashboard.product.addons.all', 'PlusCircle');

        static::$registered = true;
    }
}
