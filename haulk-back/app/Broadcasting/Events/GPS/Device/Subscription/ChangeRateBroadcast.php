<?php

namespace App\Broadcasting\Events\GPS\Device\Subscription;

use App\Broadcasting\Channels\GPS\Device\Request\DeviceRequestChannel;
use App\Broadcasting\Channels\GPS\Device\Subscription\DeviceSubscriptionChannel;
use App\Broadcasting\Events\GPS\Device\DeviceRequestBroadcast;
use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Models\Saas\GPS\DeviceRequest;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChangeRateBroadcast extends DeviceSubscriptionBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    protected DeviceSubscription $deviceSubscription;

    public const NAME = 'device-subscription.change-rate';

    public function __construct(
        DeviceSubscription $deviceSubscription,
        User $user
    )
    {
        parent::__construct($deviceSubscription);

        $this->deviceSubscription = $deviceSubscription;
        $this->user = $user;
    }
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(DeviceSubscriptionChannel::getNameForUser($this->user));
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->deviceSubscription->id,
        ];
    }

    public function getName(): string
    {
        return self::NAME;
    }
}

