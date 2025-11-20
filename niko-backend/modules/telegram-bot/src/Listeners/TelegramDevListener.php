<?php

namespace WezomCms\TelegramBot\Listeners;

use WezomCms\TelegramBot\Jobs\TelegramSendJob;

class TelegramDevListener
{
    public function handle($event)
    {
        dispatch(new TelegramSendJob($event->message));
    }
}
