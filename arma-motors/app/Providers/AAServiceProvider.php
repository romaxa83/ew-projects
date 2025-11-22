<?php

namespace App\Providers;

use App\Services\AA\Client\RequestClient;
use App\Services\AA\Client\SimpleLaravelRequest;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class AAServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RequestClient::class, function (Application $app) {
            $config = $app->make('config')->get('aa.to');

            return new SimpleLaravelRequest($config['url'], $config['token']);
        });
    }
}
