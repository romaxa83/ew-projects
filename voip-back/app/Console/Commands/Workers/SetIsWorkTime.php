<?php

namespace App\Console\Commands\Workers;

use App\IPTelephony\Services\Storage\Asterisk\WorkTimeService;
use Illuminate\Console\Command;

class SetIsWorkTime extends Command
{
    protected $signature = 'workers:set_is_work_time';

    protected $description = 'Загружает данные рабочее ли сейчас время - тб. "worktime"[asterisk]';

    public function __construct(
        protected WorkTimeService $service,
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            $start = microtime(true);

            $this->service->setIsWorkTime();

            $time = microtime(true) - $start;
            logger_info("[worker] SET IS WORKTIME [{$time}]");
        } catch (\Exception $e){
            logger_info("[worker] SET IS WORKTIME", [$e]);
            $this->error($e->getMessage(), []);
        }
    }
}
