<?php

namespace App\Providers;

use App\Http\Middleware\ApiExpectJson;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {

            Route::prefix('pdf')
                ->name('pdf.')
                ->namespace($this->namespace)
                ->group(base_path('routes/pdf.php'));

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::prefix('webhook')
                ->name('webhook.')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/webhook.php'));

            Route::prefix('api/1c')
                ->name('1c.')
                ->middleware([ApiExpectJson::class, 'api_1c'])
                ->namespace($this->namespace)
                ->group(base_path('routes/api_1c.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            if (config('routes.limit_by') === 'user') {
                $by = optional($request->user())->id ?: $request->ip();
            } else {
                $by = $request->ip();
            }

            return Limit::perMinute(config('routes.rates.api'))->by($by);
        });
    }
}
