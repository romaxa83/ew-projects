<?php

namespace App\Services\Telegram;

interface BotSender
{
    public function send(SendDataDto $dto): void;
}

