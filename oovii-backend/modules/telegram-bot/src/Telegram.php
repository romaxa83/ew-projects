<?php

namespace WezomCms\TelegramBot;

use Throwable;
use WezomCms\TelegramBot\Events\TelegramDev;
use Illuminate\Http\Request;

class Telegram
{
    const LEVEL_INFO      = 'info';
    const LEVEL_IMPORTANT = 'important';
    const LEVEL_CRITICAL  = 'critical';

    public static function info(
        string $message,
        null|string $username = null,
        $level = self::LEVEL_INFO,
    )
    {
        try {
            if(config('cms.telegram-bot.bot.telegram_use')){
                if(self::checkLevel($level)){
                    logger($message);
                    event(new TelegramDev(TelegramDto::asInfo($message, $username)));
                }
            }
        } catch (Throwable $e){
            $e->getMessage();
            logger($e->getMessage());
        }
    }

    public static function error(
        Throwable $error,
        null|string $username = null,
        null|Request $request = null,
        $level = self::LEVEL_CRITICAL,
    )
    {
        try {
            if(config('cms.telegram-bot.bot.telegram_use')){
                if(self::checkLevel($level)){
                    event(new TelegramDev(TelegramDto::asError($error, $username, $request)));
                }
            }
        } catch (Throwable $e){
            logger($e->getMessage());
        }
    }

    private static function checkLevel($level): bool
    {
        $sysLvl = config('cms.telegram-bot.bot.telegram_level');

        if($sysLvl === self::LEVEL_INFO){
            return true;
        }

        if($sysLvl === self::LEVEL_IMPORTANT){
            if($level == self::LEVEL_IMPORTANT || $level == self::LEVEL_CRITICAL){
                return true;
            }
            return false;
        }

        if($sysLvl === self::LEVEL_CRITICAL){

            if($level == self::LEVEL_CRITICAL){
                return true;
            }
            return false;
        }

        return false;
    }
}
