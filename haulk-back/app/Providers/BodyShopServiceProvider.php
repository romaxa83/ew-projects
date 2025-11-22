<?php

namespace App\Providers;

use App\Services\BodyShop\Sync\BSApiClient;
use App\Services\BodyShop\Sync\SimpleBSApiClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class BodyShopServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BSApiClient::class, function (Application $app) {
            $config = $app->make('config')->get('bodyshop');

            return new SimpleBSApiClient(
                $config['host'],
                $config['token']
            );
        });
    }
}
