<?php

namespace Tests\Builders\Vehicles;

use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\TruckDriverHistory;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class TruckDriverHistoryBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return TruckDriverHistory::class;
    }

    public function driver(User $model): self
    {
        $this->data['driver_id'] = $model->id;
        return $this;
    }

    public function truck(Truck $model): self
    {
        $this->data['truck_id'] = $model->id;
        return $this;
    }

    public function startAt(CarbonImmutable $value): self
    {
        $this->data['assigned_at'] = $value;
        return $this;
    }

    public function endAt(CarbonImmutable $value): self
    {
        $this->data['unassigned_at'] = $value;
        return $this;
    }
}
