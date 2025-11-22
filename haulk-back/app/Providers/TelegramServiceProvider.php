<?php

namespace App\Providers;

use App\Services\Telegram\BotSender;
use App\Services\Telegram\TelegramBotSender;
use Illuminate\Support\ServiceProvider;
use TelegramBot\Api\Client;

class TelegramServiceProvider extends ServiceProvider
{
    public $bindings = [
        BotSender::class => TelegramBotSender::class
    ];

    public function register(): void
    {
        $config = config('telegram');
        if($config['enabled']){
            $this->app->singleton(Client::class, function () use ($config) {
                return new Client($config['token']);
            });
        }
    }
}
