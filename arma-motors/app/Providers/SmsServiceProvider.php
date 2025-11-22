<?php

namespace App\Providers;

use App\Services\Sms\Sender\ArraySender;
use App\Services\Sms\Sender\OmniCellSender;
use App\Services\Sms\Sender\SmsSender;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SmsSender::class, function (Application $app) {
            $config = $app->make('config')->get('sms');

            switch ($config['driver']) {

                case 'omnicell':
                    return new OmniCellSender(
                        $config['drivers']['omnicell']['url'],
                        $config['drivers']['omnicell']['login'],
                        $config['drivers']['omnicell']['password'],
                    );
                case 'array':
                    return new ArraySender();
                default:
                    throw new \InvalidArgumentException('Undefined SMS driver ' . $config['driver']);
            }
        });
    }
}
