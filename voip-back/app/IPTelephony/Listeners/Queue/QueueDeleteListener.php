<?php

namespace App\IPTelephony\Listeners\Queue;

use App\IPTelephony\Events\Queue\QueueDeleteEvent;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;

class QueueDeleteListener
{
    public function __construct(protected QueueService $service)
    {}

    public function handle(QueueDeleteEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {
                $this->service->remove($event->getModel());

                logger_info("DELETE queue [asterisk] SUCCESS [{$event->getModel()->id}]");
            } catch (\Throwable $e) {
                logger_info("DELETE queue [asterisk] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}


