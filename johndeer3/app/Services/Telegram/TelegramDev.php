<?php

namespace App\Services\Telegram;

use App\Services\Telegram\Jobs\TelegramSendJob;
use Throwable;

class TelegramDev
{
    public const INFO = 'info';
    public const ERROR = 'error';

    public static function info(string $message, ?string $username = null): void
    {
        if(config('telegram.develop.enable')){
            dispatch(new TelegramSendJob(TelegramDTO::asInfo($message, $username)));
        }
    }

    public static function warn(?string $message, ?string $username = null, ?string $locate = null): void
    {
        if(config('telegram.develop.enable')){
            dispatch(new TelegramSendJob(TelegramDTO::asWarn($message, $username, $locate)));
        }
    }

    public static function error(
        string $locate,
        Throwable $error,
        ?string $username = null
    ): void
    {

        if(config('telegram.develop.enable')){
            dispatch(new TelegramSendJob(TelegramDTO::asError($locate, $error, $username)));

        }
    }
}
