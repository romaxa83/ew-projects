<?php

namespace App\IPTelephony\Listeners\Queue;

use App\Events\Departments\DepartmentCreatedEvent;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;

class QueueInsertListener
{
    public function __construct(protected QueueService $service)
    {}

    public function handle(DepartmentCreatedEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {
                $this->service->create($event->getModel());

                logger_info("INSERT queue [asterisk] SUCCESS [{$event->getModel()->id}]");
            } catch (\Throwable $e) {
                logger_info("INSERT queue [asterisk] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}

