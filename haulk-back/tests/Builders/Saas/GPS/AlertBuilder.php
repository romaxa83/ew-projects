<?php

namespace Tests\Builders\Saas\GPS;

use App\Models\GPS\Alert;
use App\Models\GPS\History;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class AlertBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Alert::class;
    }

    public function history(History $model): self
    {
        $this->data['history_id'] = $model->id;
        return $this;
    }

    public function type(string $value): self
    {
        $this->data['alert_type'] = $value;
        return $this;
    }

    public function truck(Truck $model): self
    {
        $this->data['truck_id'] = $model->id;
        $this->data['trailer_id'] = null;
        return $this;
    }

    public function companyId($value): self
    {
        $this->data['company_id'] = $value;
        return $this;
    }

    public function trailer(Trailer $model): self
    {
        $this->data['trailer_id'] = $model->id;
        $this->data['truck_id'] = null;
        return $this;
    }

    public function receivedAt(CarbonImmutable $value): self
    {
        $this->data['received_at'] = $value;
        return $this;
    }
}

