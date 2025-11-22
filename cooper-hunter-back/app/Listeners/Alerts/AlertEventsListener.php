<?php

namespace App\Listeners\Alerts;

use App\Contracts\Alerts\AlertEvent;
use App\Services\Alerts\AlertService;

class AlertEventsListener
{
    public function __construct(private AlertService $service)
    {}

    public function handle(AlertEvent $alert): void
    {
        if (!$alert->isAlertEvent()) {
            return;
        }

        $this->service
            ->setInitiator($alert->getInitiator())
            ->setMetaData($alert->getMetaData())
            ->create($alert->getModel())
        ;
    }
}
