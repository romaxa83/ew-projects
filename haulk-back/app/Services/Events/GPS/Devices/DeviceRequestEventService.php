<?php

namespace App\Services\Events\GPS\Devices;

use App\Broadcasting\Events\GPS\Device\Request\ChangeStatusBroadcast;
use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Models\Saas\GPS\DeviceRequest;
use App\Services\Events\EventService;

class DeviceRequestEventService extends EventService
{
    private const ACTION_CHANGE_STATUS = 'change_status';

    private DeviceRequest $deviceRequest;
    private ?DeviceRequestStatus $status;

    public function __construct(DeviceRequest $deviceRequest)
    {
        $this->deviceRequest = $deviceRequest;
    }

    public function status(DeviceRequestStatus $status):self
    {
        $this->action = self::ACTION_CHANGE_STATUS;

        $this->status = $status;

        return $this;
    }

    public function broadcast(): self
    {
        switch ($this->action) {
            case self::ACTION_CHANGE_STATUS:
                event(new ChangeStatusBroadcast($this->deviceRequest, $this->status));

                return $this;
        }

        return $this;
    }

}

