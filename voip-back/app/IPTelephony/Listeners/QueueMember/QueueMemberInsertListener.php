<?php

namespace App\IPTelephony\Listeners\QueueMember;

use App\Events\Employees\EmployeeCreatedEvent;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;

class QueueMemberInsertListener
{
    public function __construct(protected QueueMemberService $service)
    {}

    public function handle(EmployeeCreatedEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {
                $this->service->create($event->getModel());

                logger_info("ADD employee to queue member [asterisk] SUCCESS [{$event->getModel()->id}]");
            } catch (\Throwable $e) {
                logger_info("ADD employee to queue member [asterisk] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}



