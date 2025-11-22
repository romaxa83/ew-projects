<?php

namespace App\Jobs;

use App\Services\Telegram\TelegramBotSender;
use App\Services\Telegram\TelegramDev;
use App\Services\Telegram\TelegramDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TelegramSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private TelegramDTO $dto,
    )
    {}

    public function handle()
    {
        if($this->dto->isInfo()){
            app(TelegramBotSender::class)->send($this->dto);
        }

        if($this->dto->isError()){
            app(TelegramBotSender::class)->error($this->dto);
        }
    }
}
