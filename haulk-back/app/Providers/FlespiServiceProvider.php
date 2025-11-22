<?php

namespace App\Providers;

use App\Services\Saas\GPS\Flespi\FlespiClient;
use App\Services\Saas\GPS\Flespi\SimpleFlespiClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class FlespiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FlespiClient::class, function (Application $app) {
            $config = $app->make('config')->get('flespi');

            return new SimpleFlespiClient(
                $config['host'],
                $config['token'],
                $config['settings']
            );
        });
    }
}
