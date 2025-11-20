<?php
namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class HttpRequestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function (Application $app) {
            $config = $app->make('config')->get('guzzle');

            return new Client([
                'base_uri' => $config['params']['base_url']]
            );
        });
    }
}
