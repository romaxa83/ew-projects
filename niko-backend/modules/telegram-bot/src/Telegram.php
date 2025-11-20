<?php

namespace WezomCms\TelegramBot;

use WezomCms\TelegramBot\Events\TelegramDev;

class Telegram
{
    public static function event($message)
    {
//        logger(config('cms.telegram-bot.bot'));
        if(config('cms.telegram-bot.bot.telegram_use')){
            event(new TelegramDev($message));
        }
    }
}
