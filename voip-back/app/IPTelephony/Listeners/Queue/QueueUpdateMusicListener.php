<?php

namespace App\IPTelephony\Listeners\Queue;

use App\IPTelephony\Events\Queue\QueueUpdateMusicEvent;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;

class QueueUpdateMusicListener
{
    public function __construct(protected QueueService $service)
    {}

    public function handle(QueueUpdateMusicEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {
                $this->service->editOnlyMusicData($event->getModel());

                logger_info("UPDATE MUSIC DATA queue [asterisk] SUCCESS [{$event->getModel()->id}]");
            } catch (\Throwable $e) {
                logger_info("UPDATE MUSIC DATA queue [asterisk] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}

