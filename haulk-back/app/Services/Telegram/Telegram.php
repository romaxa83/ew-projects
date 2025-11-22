<?php

namespace App\Services\Telegram;

class Telegram
{
    public static function info(
        ?string $msg = null,
        ?string $user = null,
        array $data = []
    ): void
    {
        self::exec(SendDataDto::make([
            'msg' => $msg,
            'username' => $user,
            'data' => $data
        ]));
    }

    public static function error(
        ?string $msg = null,
        ?string $user = null,
        array $data = []
    ): void
    {
        self::exec(SendDataDto::make([
            'type' => SendDataDto::ERROR,
            'msg' => $msg,
            'username' => $user,
            'data' => $data
        ]));
    }

    private static function exec(SendDataDto $dto)
    {
        if(config('telegram.enabled')){
            dispatch(new TelegramSendJob($dto));
        }
    }
}
