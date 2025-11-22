<?php

namespace App\Providers;

use App\Services\Google\GoogleApiClient;
use App\Services\Google\Map\GoogleMapApiClient;
use App\Services\Google\Map\SimpleGoogleMapApiClient;
use App\Services\Google\SimpleGoogleApiClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class GoogleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GoogleApiClient::class, function (Application $app) {
            $config = $app->make('config')->get('google');

            return new SimpleGoogleApiClient(
                $config['roads_api']['host'],
                $config['roads_api']['key']
            );
        });

        $this->app->singleton(GoogleMapApiClient::class, function (Application $app) {
            $config = $app->make('config')->get('google');

            return new SimpleGoogleMapApiClient(
                $config['map_api']['host'],
                $config['map_api']['key']
            );
        });
    }
}
