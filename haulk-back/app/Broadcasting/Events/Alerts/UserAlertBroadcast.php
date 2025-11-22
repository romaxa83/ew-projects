<?php


namespace App\Broadcasting\Events\Alerts;


use App\Broadcasting\Channels\Alerts\UserAlertsChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserAlertBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public const NAME = 'alerts.user';

    protected int $userId;
    protected int $companyId;

    public function __construct(int $companyId, int $userId)
    {
        $this->userId = $userId;
        $this->companyId = $companyId;

        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                UserAlertsChannel::NAME . $this->companyId . UserAlertsChannel::NAME_POSTFIX . $this->userId
            )
        ];
    }

    public function broadcastAs(): string
    {
        return $this->getName();
    }

    protected function getName(): string
    {
        return self::NAME;
    }
}
