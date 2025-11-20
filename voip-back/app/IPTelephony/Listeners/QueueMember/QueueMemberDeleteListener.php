<?php

namespace App\IPTelephony\Listeners\QueueMember;

use App\IPTelephony\Events\Subscriber\SubscriberDeleteEvent;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;

class QueueMemberDeleteListener
{
    public function __construct(protected QueueMemberService $service)
    {}

    public function handle(SubscriberDeleteEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {
                $this->service->remove($event->getModel());
            } catch (\Throwable $e) {
                logger_info("DELETE queue member [asterisk] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}
