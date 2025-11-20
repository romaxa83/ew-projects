<?php

namespace App\Console\Commands\Workers;

use App\IPTelephony\Services\Storage\Asterisk\QueueLogService;
use Illuminate\Console\Command;

class SyncQueueLog extends Command
{
    protected $signature = 'workers:sync_queue_log';

    protected $description = 'Загружает данные по статистики звонков - тб. "queue_log"[asterisk]';

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

            $this->service->uploadData();

            $time = microtime(true) - $start;
            logger_info("[worker] SYNC Queue Log Time [{$time}]");
        } catch (\Exception $e){
            logger_info("[worker] SYNC Queue Log Time FAIL [$e]");
            $this->error($e->getMessage());
        }
    }
}
