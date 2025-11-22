<?php

namespace App\Providers;

use App\Services\OneC\Client\RequestClient;
use App\Services\OneC\Client\SimpleLaravelRequest;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class OneCServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RequestClient::class, function (Application $app) {
            $config = $app->make('config')->get('onec');

            return new SimpleLaravelRequest($config);
        });
    }
}

