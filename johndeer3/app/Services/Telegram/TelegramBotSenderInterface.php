<?php

namespace App\Services\Telegram;

interface TelegramBotSenderInterface
{
    public function send(TelegramDTO $dto): void;
}
