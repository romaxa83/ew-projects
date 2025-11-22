<?php

namespace App\Services\Events\GPS\Devices;

use App\Broadcasting\Events\GPS\Device\Request\ChangeStatusBroadcast;
use App\Broadcasting\Events\GPS\Device\ToggleActivityBroadcast;
use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceRequest;
use App\Services\Events\EventService;

class DeviceApproveActivityEventService extends EventService
{
    private const ACTION_TOGGLE_ACTIVITY = 'toggle_activity';

    private Device $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    public function toggleActivity():self
    {
        $this->action = self::ACTION_TOGGLE_ACTIVITY;

        return $this;
    }

    public function broadcast(): self
    {
        switch ($this->action) {
            case self::ACTION_TOGGLE_ACTIVITY:
                event(new ToggleActivityBroadcast($this->device));

                return $this;
        }

        return $this;
    }

}


