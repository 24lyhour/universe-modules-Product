<?php

namespace Modules\Product\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DashboardMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Menu items are registered in ProductServiceProvider.
     * This middleware can be used for other Product-specific request handling.
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
