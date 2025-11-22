<?php

namespace App\Services\Telegram;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TelegramSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public SendDataDto $dto;

    public function __construct(SendDataDto $dto)
    {
        $this->dto = $dto;
    }

    public function handle()
    {
        /** @var $sender TelegramBotSender */
        $sender = resolve(TelegramBotSender::class);
        $sender->send($this->dto);
    }
}
