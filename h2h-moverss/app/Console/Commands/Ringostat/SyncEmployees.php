<?php

namespace App\Console\Commands\Ringostat;

use App\Enums\Common\LogKeyEnum;
use App\Services\Employees\SyncEmployeeWithRingostat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncEmployees extends Command
{
    protected $signature = 'ringostat:sync-employees';

    public function __construct(protected SyncEmployeeWithRingostat $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            echo PHP_EOL;
            $this->info("Done [time = {$time}]");
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            Log::error(LogKeyEnum::SyncRingostat() . 'FAIL in console command SyncEmployees', [$e]);
            return self::FAILURE;
        }
    }

    public function exec()
    {
        $this->service->exec();
    }
}
