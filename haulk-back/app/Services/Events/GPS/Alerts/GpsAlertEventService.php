<?php

namespace App\Services\Events\GPS\Alerts;

use App\Broadcasting\Events\GPS\Alerts\GpsAlertsCreateBroadcast;
use App\Models\GPS\Alert;
use App\Services\Events\EventService;

class GpsAlertEventService extends EventService
{
    protected Alert $alert;

    public function __construct(Alert $alert)
    {
        $this->alert = $alert;
    }

    public function broadcast(): self
    {
        event(new GpsAlertsCreateBroadcast($this->alert));

        return $this;
    }
}
