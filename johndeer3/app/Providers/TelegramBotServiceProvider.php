<?php

namespace App\Providers;

use App\Services\Telegram\TelegramBotSender;
use App\Services\Telegram\TelegramBotSenderInterface;
use Illuminate\Support\ServiceProvider;
use TelegramBot\Api\Client;

class TelegramBotServiceProvider extends ServiceProvider
{
    public $bindings = [
        TelegramBotSenderInterface::class => TelegramBotSender::class
    ];

    public function register(): void
    {
        $config = config('telegram.develop');
        if($config['enable']){
            $this->app->singleton(Client::class, function () use ($config) {
                return new Client($config['token']);
            });
        }
    }
}

