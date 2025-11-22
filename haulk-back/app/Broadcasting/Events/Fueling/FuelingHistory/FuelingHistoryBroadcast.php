<?php

namespace App\Broadcasting\Events\Fueling\FuelingHistory;

use App\Broadcasting\Channels\Fueling\FuelingHistory\FuelingHistoryChannel;
use App\Models\Fueling\FuelingHistory;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FuelingHistoryBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public const NAME = 'fueling-history.create';

    public int $id;
    protected ?FuelingHistory $fuelingHistory = null;
    protected ?User $user = null;

    public function __construct(FuelingHistory $fuelingHistory, User $user)
    {
        $this->fuelingHistory = $fuelingHistory;

        $this->user = $user;

        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastAs(): string
    {
        return self::NAME;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(FuelingHistoryChannel::getNameForUser($this->user));
    }

    public function broadcastWith()
    {
        return [
            'status' => $this->fuelingHistory->status,
            'count_errors' => $this->fuelingHistory->count_errors,
            'counts_success' => $this->fuelingHistory->counts_success,
            'progress' => $this->fuelingHistory->getProgress(),
            'total' => $this->fuelingHistory->total,
        ];
    }
}


