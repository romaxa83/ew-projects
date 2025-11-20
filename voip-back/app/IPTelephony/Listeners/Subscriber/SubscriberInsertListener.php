<?php

namespace App\IPTelephony\Listeners\Subscriber;

use App\Events\Employees\EmployeeCreatedEvent;
use App\IPTelephony\Services\Storage\Kamailio\SubscriberService;

class SubscriberInsertListener
{
    public function __construct(protected SubscriberService $service)
    {}

    public function handle(EmployeeCreatedEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {
                $this->service->create($event->getModel());

                logger_info("INSERT subscriber [camailio] SUCCESS [{$event->getModel()->id}]");
            } catch (\Throwable $e) {
                logger_info("INSERT subscriber [camailio] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}
