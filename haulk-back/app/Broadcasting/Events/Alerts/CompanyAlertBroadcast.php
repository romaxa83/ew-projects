<?php


namespace App\Broadcasting\Events\Alerts;


use App\Broadcasting\Channels\Alerts\CompanyAlertsChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompanyAlertBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public const NAME = 'alerts.company';

    protected int $companyId;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;

        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(CompanyAlertsChannel::NAME . $this->companyId)
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
