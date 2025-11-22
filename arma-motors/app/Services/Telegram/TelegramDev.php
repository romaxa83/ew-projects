<?php

namespace App\Services\Telegram;

use App\Jobs\TelegramSendJob;
use Throwable;

class TelegramDev
{
    public const LEVEL_ALL = 'all';
    public const LEVEL_IMPORTANT = 'important';
    public const LEVEL_CRITICAL = 'critical';

    public const INFO = 'info';
    public const ERROR = 'error';

    public static function info(string $message, null|string $username = null, $level = self::LEVEL_ALL): void
    {
        if(config('telegram.develop.enable')){
            if(self::checkLevel($level)){
                dispatch(new TelegramSendJob(TelegramDTO::asInfo($message, $username)));
            }
        }
    }

    public static function error(
        string $locate,
        Throwable $error,
        null|string $username = null,
        $level = self::LEVEL_IMPORTANT
    ): void
    {
        if(config('telegram.develop.enable')){
            if(self::checkLevel($level)){
                dispatch(new TelegramSendJob(TelegramDTO::asError($locate, $error, $username)));
            }
        }
    }

    public static function checkLevel($level): bool
    {
        if(config('telegram.develop.level') === self::LEVEL_ALL){
            return true;
        }

        if(config('telegram.develop.level') === self::LEVEL_IMPORTANT){

            if($level == self::LEVEL_IMPORTANT || $level == self::LEVEL_CRITICAL){
                return true;
            }
            return false;
        }

        if(config('telegram.develop.level') === self::LEVEL_CRITICAL){

            if($level == self::LEVEL_CRITICAL){
                return true;
            }
            return false;
        }

        return false;
    }
}
