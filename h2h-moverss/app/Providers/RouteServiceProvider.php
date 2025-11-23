<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{RateLimiter, Route};

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
//    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot(): void
    {
        Route::pattern('id', '[0-9]+');
        Route::pattern('slug', '[A-Za-z0-9\-\.]+');
        Route::pattern('slug2', '[A-Za-z0-9\-\.]+');
        Route::pattern('hash', '[A-Za-z0-9]+');

        Route::pattern('group', '[A-Za-z]+');

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Route::middleware(['web'])
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));

        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));

        parent::boot();
    }

}
