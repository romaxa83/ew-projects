<?php

namespace App\IPTelephony\Listeners\QueueMember;

use App\IPTelephony\Events\QueueMember\QueueMemberUpdateNameEvent;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;

class QueueMemberUpdateNameListener
{
    public function __construct(protected QueueMemberService $service)
    {}

    public function handle(QueueMemberUpdateNameEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {
                $this->service->updateQueueNames($event->getOldName(), $event->getNewName());

                logger_info("UPDATE NAME queue member [asterisk] SUCCESS [new - {$event->getNewName()}, old - {$event->getOldName()}]");
            } catch (\Throwable $e) {
                logger_info("UPDATE NAME queue member [asterisk] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}

