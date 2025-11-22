<?php

namespace Tests\Builders\Saas\GPS;

use App\Models\GPS\History;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class HistoryBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return History::class;
    }

    public function eventType(string $value): self
    {
        $this->data['event_type'] = $value;
        return $this;
    }

    public function truck(Truck $model): self
    {
        $this->data['truck_id'] = $model->id;
        return $this;
    }

    public function trailer(Trailer $model): self
    {
        $this->data['trailer_id'] = $model->id;
        return $this;
    }

    public function driver(User $model): self
    {
        $this->data['driver_id'] = $model->id;
        return $this;
    }

    public function oldDriver(User $model): self
    {
        $this->data['old_driver_id'] = $model->id;
        return $this;
    }

    public function device(Device $model): self
    {
        $this->data['device_id'] = $model->id;
        return $this;
    }

    public function company(Company $model): self
    {
        $this->data['company_id'] = $model->id;
        return $this;
    }

    public function receivedAt(CarbonImmutable $value): self
    {
        $this->data['received_at'] = $value;
        return $this;
    }

    public function mileage(float $value): self
    {
        $this->data['vehicle_mileage'] = $value;
        return $this;
    }

    public function latitude(?float $value): self
    {
        $this->data['latitude'] = $value;
        return $this;
    }

    public function longitude(?float $value): self
    {
        $this->data['longitude'] = $value;
        return $this;
    }

    public function speed(int $value): self
    {
        $this->data['speed'] = $value;
        return $this;
    }

    public function isSpeeding(bool $value): self
    {
        $this->data['is_speeding'] = $value;
        return $this;
    }
}
