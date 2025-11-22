<?php


namespace App\Broadcasting\Events\Support\Crm;


use App\Broadcasting\Channels\Support\Crm\SupportUserChannel;
use App\Broadcasting\Events\Support\SupportBroadcast;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewIsNotReadMessageBroadcast extends SupportBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public const NAME = 'support.new-is-not-read-message';

    public int $message_id;

    public function __construct(int $supportRequestId, int $supportRequestMessageId, ?User $user)
    {
        parent::__construct($supportRequestId);

        $this->message_id = $supportRequestMessageId;

        $this->user = $user;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(SupportUserChannel::getNameForUser($this->user));
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
