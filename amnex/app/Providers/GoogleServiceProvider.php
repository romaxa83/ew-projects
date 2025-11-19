<?php

namespace App\Providers;

use App\Services\Requests\Google\Map\GoogleMapApiClient;
use App\Services\Requests\Google\Map\SimpleGoogleMapApiClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class GoogleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GoogleMapApiClient::class, function (Application $app) {
            $config = $app->make('config')->get('google.map_api');

            return new SimpleGoogleMapApiClient(
                $config['host'],
                $config['key']
            );
        });
    }
}
