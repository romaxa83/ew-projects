<?php

namespace App\Broadcasting\Events\GPS\Alerts;

use App\Broadcasting\Channels\GPS\Alerts\GpsAlertChannel;

use App\Models\GPS\Alert;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GpsAlertsCreateBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public const NAME = 'gps-alert.create';

    public int $id;

    protected ?User $user = null;
    protected ?Alert $alert = null;

    public function __construct(Alert $alert)
    {
        $this->alert = $alert;

        $this->user = $this->alert->company->getSuperAdmin();

        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastAs(): string
    {
        return self::NAME;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(GpsAlertChannel::getNameForUser($this->user));
    }

    public function broadcastWith()
    {
        $startFromTime = now()->subDays(config('gps.count_days_to_show_alerts') - 1)->startOfDay();

        $count = Alert::query()
            ->where('company_id', $this->alert->company_id)
            ->where('received_at', '>=', $startFromTime)
            ->count();

        return [
            'count' => $count,
        ];
    }
}


