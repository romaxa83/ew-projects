<?php

namespace App\IPTelephony\Listeners\QueueMember;

use App\Events\Employees\EmployeeUpdatedEvent;
use App\IPTelephony\Events\QueueMember\QueueMemberUpdateEvent;
use App\IPTelephony\Events\Subscriber\SubscriberUpdateOrCreateEvent;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;

class QueueMemberUpdateOrInsertListener
{
    public function __construct(protected QueueMemberService $service)
    {}

    public function handle(
        QueueMemberUpdateEvent|EmployeeUpdatedEvent|SubscriberUpdateOrCreateEvent $event
    ): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {
                if($this->service->remove($event->getModel())){
                    $this->service->create($event->getModel());
                }

//                $this->service->editOrCreate($event->getModel());

                logger_info("UPDATE OR CREATE queue member [asterisk] SUCCESS [{$event->getModel()->id}]");
            } catch (\Throwable $e) {
                logger_info("UPDATE OR CREATE queue member [asterisk] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}
