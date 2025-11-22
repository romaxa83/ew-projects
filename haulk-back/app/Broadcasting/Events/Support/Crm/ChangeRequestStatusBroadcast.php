<?php


namespace App\Broadcasting\Events\Support\Crm;


use App\Broadcasting\Channels\Support\Crm\SupportChannel;
use App\Broadcasting\Events\Support\SupportBroadcast;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChangeRequestStatusBroadcast extends SupportBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public const NAME = 'support.change-status';

    public function __construct(int $supportRequestId, ?User $user)
    {
        parent::__construct($supportRequestId);

        $this->user = $user;
    }
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(SupportChannel::getNameForUser($this->user));
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
