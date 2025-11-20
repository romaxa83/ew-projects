<?php

namespace App\IPTelephony\Listeners\Queue;

use App\IPTelephony\Events\Queue\QueueUpdateOrCreateEvent;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;

class QueueUpdateOrInsertListener
{
    public function __construct(protected QueueService $service)
    {}

    public function handle(QueueUpdateOrCreateEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {
                $this->service->editOrCreate($event->getModel());

                logger_info("UPDATE OR CREATE queue [asterisk] SUCCESS [{$event->getModel()->id}]");
            } catch (\Throwable $e) {
                logger_info("UPDATE OR CREATE queue [asterisk] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}
