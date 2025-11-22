<?php


namespace App\Broadcasting\Events\Support\Backoffice;


use App\Broadcasting\Channels\Support\Backoffice\SupportChannel;
use App\Broadcasting\Events\Support\SupportBroadcast;
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

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(SupportChannel::getNameForAdmin());
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
