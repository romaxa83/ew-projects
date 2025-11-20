<?php

namespace App\IPTelephony\Listeners\Queue;

use App\IPTelephony\Events\Queue\QueueDeleteMusicEvent;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;

class QueueDeleteMusicListener
{
    public function __construct(protected QueueService $service)
    {}

    public function handle(QueueDeleteMusicEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {
                $this->service->deleteOnlyMusicData($event->getModel());

                logger_info("DELETE MUSIC DATA queue [asterisk] SUCCESS [{$event->getModel()->id}]");
            } catch (\Throwable $e) {
                logger_info("DELETE MUSIC DATA queue [asterisk] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}


