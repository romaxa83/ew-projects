<?php

namespace WezomCms\TelegramBot;

use WezomCms\Core\BaseServiceProvider;
use WezomCms\TelegramBot\Events\TelegramDev;
use WezomCms\TelegramBot\Listeners\TelegramDevListener;

class TelegramBotServiceProvider extends BaseServiceProvider
{
    protected $listen = [
        TelegramDev::class => [
            TelegramDevListener::class,
        ],
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(){}

    /**
     * Application booting.
     */
    public function boot()
    {
//        if(env('TELEGRAM_USE')){
////            $bot = new \TelegramBot\Api\BotApi('YOUR_BOT_API_TOKEN');
//            $this->app->singleton(BotApi::class, function () {
//                return new BotApi(env('TELEGRAM_TOKEN'));
//
//            });
//        }

        parent::boot();
    }
}

