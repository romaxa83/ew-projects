<?php

namespace App\Broadcasting\Events\GPS\Device;

use App\Models\Admins\Admin;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceRequest;
use App\Models\Users\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class DeviceBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public const NAME = 'device.toggle-activity';

    public int $id;

    protected ?User $user = null;

    protected ?Admin $admin = null;

    public function __construct(Device $device)
    {
        $this->id = $device->id;
        $this->user = $device->sendRequestUser;

        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastAs(): string
    {
        return $this->getName();
    }

    /**
     * @return Channel|Channel[]
     */
    abstract public function broadcastOn();

    abstract public function getName(): string;
}


