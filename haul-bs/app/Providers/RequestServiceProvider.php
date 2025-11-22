<?php

namespace App\Providers;

use App\Services\Requests\BaseHaulk\BaseHaulkRequestClient;
use App\Services\Requests\ECom\EComRequestClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class RequestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $config = config('requests');

        $this->app->singleton(BaseHaulkRequestClient::class, function (Application $app) use ($config) {

            return new BaseHaulkRequestClient(
                $config['base_haulk']['host'],
                $config['base_haulk']['secrets'],
                $config['base_haulk']['settings'],
            );
        });

        if($config['e_com']['enabled']){
            $this->app->singleton(EComRequestClient::class, function (Application $app) use ($config) {
                return new EComRequestClient(
                    $config['e_com']['host'],
                    $config['e_com']['token'],
                    $config['e_com']['settings'],
                );
            });
        }
    }
}
