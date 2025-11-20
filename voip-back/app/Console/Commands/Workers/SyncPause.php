<?php

namespace App\Console\Commands\Workers;

use App\IPTelephony\Services\Storage\Asterisk\QueueLogService;
use Illuminate\Console\Command;

class SyncPause extends Command
{
    protected $signature = 'workers:sync_pause';

    protected $description = 'Загружает данные по паузам сотрудника - тб. "queue_log"[asterisk]';

    public function __construct(
        protected QueueLogService $service,
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            $start = microtime(true);

            $this->service->uploadPauseData();

            $time = microtime(true) - $start;
            logger_info("[worker] SYNC Pause [{$time}]");
        } catch (\Exception $e){
            logger_info("[worker] SYNC Pause FAIL [$e]");
            $this->error($e->getMessage());
        }
    }
}

