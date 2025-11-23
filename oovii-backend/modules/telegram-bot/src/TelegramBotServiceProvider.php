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

    public function register(): void
    {}

    public function boot(): void
    {
        parent::boot();
    }
}

