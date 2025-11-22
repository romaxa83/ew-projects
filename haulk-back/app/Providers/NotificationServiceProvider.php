<?php

namespace App\Providers;

use App\Services\Fax\Drivers\FaxDriver;
use App\Services\Fax\FaxServiceFactory;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{

    public function register()
    {
        parent::register();

        $this->app->singleton(
            FaxDriver::class,
            function (Application $app) {
                return $app->make(FaxServiceFactory::class)->create($app);
            }
        );
    }

    public function boot()
    {
        require base_path('routes/notifications.php');
    }
}
