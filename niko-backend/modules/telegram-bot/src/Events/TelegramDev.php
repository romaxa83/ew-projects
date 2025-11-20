<?php

namespace WezomCms\TelegramBot\Events;

use Illuminate\Queue\SerializesModels;

class TelegramDev
{
    use SerializesModels;

    /**
     * @var string|int
     */
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }
}
