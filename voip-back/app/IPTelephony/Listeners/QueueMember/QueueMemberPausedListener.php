<?php

namespace App\IPTelephony\Listeners\QueueMember;

use App\IPTelephony\Events\QueueMember\QueueMemberPausedEvent;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;

class QueueMemberPausedListener
{
    public function __construct(protected QueueMemberService $service)
    {}

    public function handle(QueueMemberPausedEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {

                $this->service->togglePaused($event->getModel(), $event->paused());

                logger_info("TOGGLE PAUSED queue member [asterisk] SUCCESS [{$event->getModel()->id}]");
            } catch (\Throwable $e) {

                logger_info("TOGGLE PAUSED queue member [asterisk] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}
