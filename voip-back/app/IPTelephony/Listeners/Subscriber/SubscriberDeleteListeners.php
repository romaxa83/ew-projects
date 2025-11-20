<?php

namespace App\IPTelephony\Listeners\Subscriber;

use App\IPTelephony\Events\Subscriber\SubscriberDeleteEvent;
use App\IPTelephony\Services\Storage\Kamailio\SubscriberService;

class SubscriberDeleteListeners
{
    public function __construct(protected SubscriberService $service)
    {}

    public function handle(SubscriberDeleteEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {
                $this->service->remove($event->getModel());
            } catch (\Throwable $e) {
                logger_info("DELETE subscriber [camailio] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}

