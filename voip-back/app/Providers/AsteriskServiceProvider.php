<?php

namespace App\Providers;

use App\IPTelephony\Services\Client\Asterisk\AmiSocketClient;
use App\PAMI\Client\Impl\ClientAMI;
use App\Services\ARI\ClientARI;
use App\Services\ARI\SimpleClientARI;
use Illuminate\Support\ServiceProvider;

class AsteriskServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerClientAMI();
        $this->registerClientARI();
//        $this->registerAmiSocketClient();
    }

    protected function registerAmiSocketClient()
    {
        $this->app->singleton(AmiSocketClient::class, function ($app) {
            $config = $app['config']['asterisk']['ami'];
            return new AmiSocketClient(
                data_get($config, 'host'),
                data_get($config, 'port'),
                data_get($config, 'username'),
                data_get($config, 'secret'),
            );
        });
    }

    protected function registerClientAMI()
    {
        $this->app->singleton(ClientAMI::class, function ($app) {
            $config = $app['config']['asterisk']['ami'];
            return new ClientAMI([
                'host' => data_get($config, 'host'),
                'port' => data_get($config, 'port'),
                'username' => data_get($config, 'username'),
                'secret' => data_get($config, 'secret'),
                'connect_timeout' => data_get($config, 'connect_timeout'),
                'read_timeout' => data_get($config, 'read_timeout'),
                'scheme' => data_get($config, 'connect_schema'),
            ]);
        });
    }

    protected function registerClientARI()
    {
        $this->app->singleton(ClientARI::class, function ($app) {
            $config = $app['config']['asterisk']['ari'];
            return new SimpleClientARI(
                data_get($config, 'host'),
                data_get($config, 'port'),
                data_get($config, 'username'),
                data_get($config, 'password'),
                data_get($config, 'settings'),
            );
        });
    }
}
