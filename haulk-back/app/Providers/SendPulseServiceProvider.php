<?php

namespace App\Providers;

use App\Services\SendPulse\SendPulseApiClient;
use App\Services\SendPulse\SimpleSendPulseApiClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SendPulseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SendPulseApiClient::class, function (Application $app) {
            $config = $app->make('config')->get('sendpulse');

            return new SimpleSendPulseApiClient(
                $config['host'],
                $config['client_id'],
                $config['client_secret']
            );
        });
    }
}
