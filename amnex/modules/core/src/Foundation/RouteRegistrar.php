<?php

namespace Wezom\Core\Foundation;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Wezom\Core\Http\Middleware\ApiAuthByBearer;

class RouteRegistrar
{
    protected bool $routesAreCached;

    public function __construct()
    {
        $this->routesAreCached = app()->routesAreCached();
    }

    public function adminRoutes(callable|string $callback): void
    {
        Route::domain(config('app.admin_url'))->name('admin.')->group($callback);
    }

    public function siteRoutes(callable|string $callback): void
    {
        Route::domain(config('app.front_url'))->name('site.')->group($callback);
    }

    public function apiRoutes(callable|string $callback): void
    {
        if ($this->routesAreCached) {
            return;
        }

        if (!is_callable($callback)) {
            $callback = function () use ($callback) {
                require $callback;
            };
        }

        Route::prefix('api')
            ->middleware([ApiAuthByBearer::class, SubstituteBindings::class])
            ->group($callback);
    }
}
