<?php

namespace App\Console\Commands\Worker;

use Illuminate\Console\Command;
use WezomCms\TelegramBot\Telegram;

class TestWorker extends Command
{
    protected $signature = 'cmd:worker:test';

    public function handle()
    {
        Telegram::info("🗑 Test Wokker", 'SYSTEM',Telegram::LEVEL_IMPORTANT);
    }
}
