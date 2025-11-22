<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/api.php'));

            Route::name('api.v1.e_comm')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/e-comm.php'));

            Route::name('api.v1.users')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/user.php'));

            Route::name('api.v1.suppliers')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/supplier.php'));

            Route::name('api.v1.customers')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/customer.php'));

            Route::name('api.v1.vehicles')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/vehicles.php'));

            Route::name('api.v1.tags')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/tag.php'));

            Route::name('api.v1.inventories')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/inventory.php'));

            Route::name('api.v1.localization')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/localization.php'));

            Route::name('api.v1.location')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/location.php'));

            Route::name('api.v1.settings')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/settings.php'));
            Route::name('api.v1.forms')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/forms.php'));

            Route::name('api.v1.orders')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/order.php'));

            Route::name('api.v1.type-of-works')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/type_of_works.php'));

            Route::name('api.v1.webhooks')
                ->prefix('api/v1/webhooks')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/webhooks.php'));

            Route::name('api.v1')
                ->prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api/v1/common.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
