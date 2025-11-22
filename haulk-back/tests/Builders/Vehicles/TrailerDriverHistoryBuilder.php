<?php

namespace Tests\Builders\Vehicles;

use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\TrailerDriverHistory;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class TrailerDriverHistoryBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return TrailerDriverHistory::class;
    }

    public function driver(User $model): self
    {
        $this->data['driver_id'] = $model->id;
        return $this;
    }

    public function trailer(Trailer $model): self
    {
        $this->data['trailer_id'] = $model->id;
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
