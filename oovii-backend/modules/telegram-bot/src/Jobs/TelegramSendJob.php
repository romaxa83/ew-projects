<?php

namespace WezomCms\TelegramBot\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use WezomCms\TelegramBot\Services\TelegramClient;
use WezomCms\TelegramBot\TelegramDto;

class TelegramSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $dto;

    public function __construct(TelegramDto $dto)
    {
        $this->dto = $dto;
    }

    public function handle(): void
    {
        TelegramClient::send($this->dto);
    }
}
