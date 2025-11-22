<?php

namespace App\Console\Commands\Helpers\Telegram;

use App\Services\Telegram\Telegram;
use Illuminate\Console\Command;

class Check extends Command
{
    protected $signature = 'helper:telegram_check';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            $this->info("[helper] DONE [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function exec(): void
    {
        Telegram::info('hi');
    }
}
