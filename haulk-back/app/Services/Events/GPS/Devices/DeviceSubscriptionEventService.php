<?php

namespace App\Services\Events\GPS\Devices;

use App\Broadcasting\Events\GPS\Device\Subscription\ChangeRateBroadcast;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Services\Events\EventService;

class DeviceSubscriptionEventService extends EventService
{
    private const ACTION_CHANGE_RATE = 'change_rate';

    protected DeviceSubscription $deviceSubscription;

    public function __construct(DeviceSubscription $deviceSubscription)
    {
        $this->deviceSubscription = $deviceSubscription;
    }

    public function changeRate():self
    {
        $this->action = self::ACTION_CHANGE_RATE;

        return $this;
    }

    public function broadcast(): self
    {
        switch ($this->action) {
            case self::ACTION_CHANGE_RATE:
                event(new ChangeRateBroadcast($this->deviceSubscription, $this->user));

                return $this;
        }

        return $this;
    }

}


