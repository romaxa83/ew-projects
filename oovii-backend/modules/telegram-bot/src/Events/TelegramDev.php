<?php

namespace WezomCms\TelegramBot\Events;

use Illuminate\Queue\SerializesModels;
use WezomCms\TelegramBot\TelegramDto;

class TelegramDev
{
    use SerializesModels;

    public $dto;

    public function __construct(TelegramDto $dto)
    {
        $this->dto = $dto;
    }
}
