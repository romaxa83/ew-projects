<?php

namespace App\Providers;

use App\Http\Controllers\ApiController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\Http\Controllers';

    public const HOME = '/home';

    public function map(): void
    {
        // legacy routing
        $this->mapApiRoutes();
        $this->mapWebRoutes();

        // versioned routing
        $this->registerApiVersionChecker();
        $this->mapAuthRoutes();
        $this->mapDataRoutes();
        $this->mapSaasRoutes();
        $this->mapBrokerRoutes();
        $this->mapCarrierRoutes();
        $this->mapBrokerMobileRoutes();
        $this->mapCarrierMobileRoutes();
        $this->mapBodyShopRoutes();
        $this->mapGPSRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    protected function registerApiVersionChecker(): void
    {
        Route::get('api-version', [ApiController::class, 'apiVersion']);
    }

    protected function mapAuthRoutes(): void
    {
        Route::name('v' . config('routing.api_version') . '.authorize.')
            ->prefix('v' . config('routing.api_version') . '/auth')
            ->middleware('api')
            ->group(base_path('routes/v' . config('routing.api_version') . '/auth.php'));
    }

    protected function mapDataRoutes(): void
    {
        Route::name('v' . config('routing.api_version') . '.data.')
            ->prefix('v' . config('routing.api_version') . '/data')
            ->middleware('api')
            ->group(base_path('routes/v' . config('routing.api_version') . '/data.php'));
    }

    protected function mapSaasRoutes(): void
    {
        Route::name('v' . config('routing.api_version') . '.saas.')
            ->prefix('v' . config('routing.api_version') . '/saas')
            ->middleware('api')
            ->group(base_path('routes/v' . config('routing.api_version') . '/saas.php'));
    }

    protected function mapCarrierRoutes(): void
    {
        Route::name('v' . config('routing.api_version') . '.carrier.')
            ->prefix('v' . config('routing.api_version') . '/carrier')
            ->middleware('api')
            ->namespace($this->namespace . '\\V' . config('routing.api_version') . '\\Carrier')
            ->group(base_path('routes/v' . config('routing.api_version') . '/carrier.php'));
        Route::name('v2.carrier.')
            ->prefix('v2/carrier')
            ->middleware('api')
            ->namespace($this->namespace . '\\V2\\Carrier')
            ->group(base_path('routes/v2/carrier.php'));
    }

    protected function mapCarrierMobileRoutes(): void
    {
        Route::name('v' . config('routing.api_version') . '.carrier-mobile.')
            ->prefix('v' . config('routing.api_version') . '/carrier-mobile')
            ->middleware('api')
            ->group(base_path('routes/v' . config('routing.api_version') . '/carrier-mobile.php'));
        Route::name('v2.carrier-mobile.')
            ->prefix('v2/carrier-mobile')
            ->middleware('api')
            ->group(base_path('routes/v2/carrier-mobile.php'));
    }

    protected function mapBrokerRoutes(): void
    {
        Route::name('v' . config('routing.api_version') . '.broker.')
            ->prefix('v' . config('routing.api_version') . '/broker')
            ->middleware('api')
            ->namespace($this->namespace . '\\V' . config('routing.api_version') . '\\Broker')
            ->group(base_path('routes/v' . config('routing.api_version') . '/broker.php'));
    }

    protected function mapBrokerMobileRoutes(): void
    {
        Route::name('v' . config('routing.api_version') . '.broker-mobile.')
            ->prefix('v' . config('routing.api_version') . '/broker-mobile')
            ->middleware('api')
            ->namespace($this->namespace . '\\V' . config('routing.api_version') . '\\BrokerMobile')
            ->group(base_path('routes/v' . config('routing.api_version') . '/broker-mobile.php'));
    }

    protected function mapBodyShopRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/body-shop.php'));
    }

    protected function mapGPSRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/gps.php'));
    }
}
