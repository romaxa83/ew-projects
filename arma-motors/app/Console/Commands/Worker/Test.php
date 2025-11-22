<?php

namespace App\Console\Commands\Worker;

use App\Services\Telegram\TelegramDev;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'am:worker:test';

    public function handle()
    {
        TelegramDev::info('TEST', 'SYSTEM', TelegramDev::LEVEL_CRITICAL);
    }
}


