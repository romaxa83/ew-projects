<?php

namespace App\Broadcasting\Events\GPS\Device;

use App\Broadcasting\Channels\GPS\Device\DeviceChannel;
use App\Models\Saas\GPS\Device;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ToggleActivityBroadcast extends DeviceBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    protected Device $device;

    public const NAME = 'device.toggle-activity';

    public function __construct(
        Device $device
    )
    {
        parent::__construct($device);

        $this->device = $device;
    }
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(DeviceChannel::getNameForUser($this->user));
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->device->id,
        ];
    }

    public function getName(): string
    {
        return self::NAME;
    }
}

