<?php

namespace App\Services\Events\Fueling;

use App\Broadcasting\Events\Fueling\FuelingHistory\FuelingHistoryBroadcast;
use App\Models\Fueling\FuelingHistory;
use App\Services\Events\EventService;

class FuelingHistoryEventService extends EventService
{
    protected FuelingHistory $fuelingHistory;

    public function __construct(FuelingHistory $fuelingHistory)
    {
        $this->fuelingHistory = $fuelingHistory;
    }

    public function broadcast(): self
    {
        event(new FuelingHistoryBroadcast($this->fuelingHistory, $this->user));

        return $this;
    }
}
