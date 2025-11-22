<?php

namespace App\Broadcasting\Events\GPS\Device;

use App\Models\Admins\Admin;
use App\Models\Saas\GPS\DeviceRequest;
use App\Models\Users\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class DeviceRequestBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public int $id;

    protected ?User $user = null;

    protected ?Admin $admin = null;

    public function __construct(DeviceRequest $deviceRequest)
    {
        $this->id = $deviceRequest->id;
        $this->user = $deviceRequest->user;

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

