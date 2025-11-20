<?php

namespace App\Console\Commands\Workers;

use App\Repositories\Calls\QueueRepository;
use App\Services\Calls\QueueService;
use Illuminate\Console\Command;

class DeleteOldQueueRecords extends Command
{
    protected $signature = 'workers:remove_old_queue_recs';

    protected $description = 'Удаляет старые записи по звонкам в очереди';

    public function __construct(
        protected QueueService $queueService,
        protected QueueRepository $queueRepo,
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            $start = microtime(true);

            $recs = $this->queueRepo->recsForRemove();
            $count = $recs->count();

            foreach ($recs as $rec){
                $rec->delete();
            }

//            $res = $this->queueService->deleteAll();

            $time = microtime(true) - $start;
            logger_info("[worker] DELETE OLD QUEUE RECORDS [{$count}] [time = {$time}]");
        } catch (\Exception $e){
//            dd($e->getMessage());
            logger_info("[worker] DELETE OLD QUEUE RECORDS FAIL", [$e]);
            $this->error($e->getMessage(), []);
        }
    }
}

