<?php

namespace App\Console\Commands\Workers;

use App\IPTelephony\Services\Storage\Asterisk\CdrService;
use Illuminate\Console\Command;

class SyncCDR extends Command
{
    protected $signature = 'workers:sync_cdr';

    protected $description = 'Загружает данные по истории звонков - тб. "cdr"[asterisk]';

    public function __construct(
        protected CdrService $cdrService,
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            $start = microtime(true);

            $this->cdrService->uploadCdrData();

            $time = microtime(true) - $start;
            logger_info("[worker] SYNC CDR Time [{$time}]");
        } catch (\Exception $e){
            logger_info("[worker] SYNC CDR FAIL", [$e]);
            $this->error($e->getMessage(), []);
        }
    }
}
