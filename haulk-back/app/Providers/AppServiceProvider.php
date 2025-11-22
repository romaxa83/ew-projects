<?php

namespace App\Providers;

use App\Repositories\Usdot\UsdotApiRepository;
use App\Repositories\Usdot\UsdotRepository;
use App\Services\Images\DrawingImageInterface;
use App\Services\Images\InterventionImageService;
use App\Services\Orders\GeneratePdfService;
use App\Services\Permissions\Payments\AuthorizeNetPaymentService;
use App\Services\Permissions\Payments\PaymentProviderInterface;
use App\Services\Roles\RoleService;
use App\Services\Settings\SettingService;
use App\Services\Usdot\UsdotService;
use App\Services\Vehicles\VinDecodeService;
use App\Services\Vehicles\VpicVinDecodeService;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Cmixin\BusinessDay;
use DateTime;
use DB;
use Gate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Log;
use Validator;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->singleton(UsdotService::class);

        $this->app->singleton(UsdotRepository::class, UsdotApiRepository::class);

        $this->app->singleton(RoleService::class, RoleService::class);

        $this->app->singleton(DrawingImageInterface::class, InterventionImageService::class);

        $this->app->singleton(SettingService::class, SettingService::class);

        $this->app->singleton(GeneratePdfService::class, GeneratePdfService::class);

        $this->app->singleton(VinDecodeService::class, VpicVinDecodeService::class);

        $this->app->bind(
            PaymentProviderInterface::class,
            AuthorizeNetPaymentService::class
        );
    }

    public function boot(): void
    {
        BusinessDay::enable(\Illuminate\Support\Carbon::class);
        Carbon::setHolidaysRegion('us-national');

        if (config('database.db_debug')) {
            $this->queryLog();
        }

        if (!isProd()) {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        //Add custom validation rule.
        Validator::extend(
            'alpha_spaces',
            function ($attribute, $value) {
                // This will only accept alpha and spaces.
                return preg_match('/^[a-zA-Z\s]*$/u', $value);
            }
        );

        Gate::define(
            'viewWebSocketsDashboard',
            function ($user = null) {
                return !isProd();
            }
        );

        $this->macros();
    }

    protected function queryLog(): void
    {
        DB::listen(
            function ($query) {
                $pieces = array_filter(
                    $query->bindings,
                    static function ($piece): bool {
                        return !($piece instanceof DateTime);
                    }
                );

                Log::channel('single')->debug(
                    sprintf(
                        'Date: %s Time: %s | Sql: %s | params:[%s].' . PHP_EOL,
                        (int)(microtime(true) * 1000),
                        $query->time,
                        $query->sql
                        ,
                        implode(', ', $pieces)
                    )
                );
            }
        );
    }

    public function macros()
    {
        Collection::macro(
            'whereLike',
            function (string $column, $value) {
                return $this->filter(
                    function ($model) use ($column, $value): bool {
                        return stristr($model->$column, $value);
                    }
                );
            }
        );
    }
}
