<?php


namespace App\Broadcasting\Events\Support\Backoffice;


use App\Broadcasting\Channels\Support\Backoffice\SupportChannel;
use App\Broadcasting\Events\Support\SupportBroadcast;
use App\Models\Admins\Admin;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewRequestBroadcast extends SupportBroadcast
{

    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public const NAME = 'support.new-request';

    public function __construct(int $supportRequestId, ?Admin $admin)
    {
        parent::__construct($supportRequestId);

        $this->id = $supportRequestId;

        $this->admin = $admin;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(SupportChannel::getNameForAdmin($this->admin));
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
