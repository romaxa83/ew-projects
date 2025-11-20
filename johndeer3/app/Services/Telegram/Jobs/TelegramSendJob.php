<?php

namespace App\Services\Telegram\Jobs;

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

    public $dto;

    public function __construct(TelegramDTO $dto)
    {
        $this->dto = $dto;
    }

    public function handle()
    {
        if($this->dto->isInfo()){
            app(TelegramBotSender::class)->send($this->dto);
        }

        if($this->dto->isError()){
            app(TelegramBotSender::class)->error($this->dto);
        }

        if($this->dto->isWarn()){
            app(TelegramBotSender::class)->warn($this->dto);
        }
    }
}
