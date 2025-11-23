<?php

namespace App\Providers;

use App\Services\Requests\Ringostat\RingostatRequestClient;
use App\Services\Requests\VAPI\VapiClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class RequestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = config('requests');

        $this->app->singleton(RingostatRequestClient::class, function (Application $app) use ($config) {
            return new RingostatRequestClient(
                $config['ringostat']['host'],
            );
        });

        $this->app->singleton(VapiClient::class, function (Application $app) use ($config) {
            return new VapiClient(
                $config['vapi']['url'],
                $config['vapi']['api_key'],
            );
        });
    }
}

