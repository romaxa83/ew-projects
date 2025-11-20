<?php

namespace App\IPTelephony\Listeners\Subscriber;

use App\Events\Employees\EmployeeUpdatedEvent;
use App\IPTelephony\Events\Subscriber\SubscriberUpdateOrCreateEvent;
use App\IPTelephony\Services\Storage\Kamailio\SubscriberService;

class SubscriberUpdateOrInsertListener
{
    public function __construct(protected SubscriberService $service)
    {}

    public function handle(EmployeeUpdatedEvent|SubscriberUpdateOrCreateEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {
                $this->service->editOrCreate($event->getModel());

                logger_info("UPDATE OR CREATE subscriber [camailio] SUCCESS [{$event->getModel()->id}]");
            } catch (\Throwable $e) {
                logger_info("UPDATE OR CREATE subscriber [camailio] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}

