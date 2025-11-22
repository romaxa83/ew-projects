<?php

namespace Tests\Builders\Vehicles;

use App\Models\GPS\History;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class TrailerBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Trailer::class;
    }

    public function unitNumber(string $value): self
    {
        $this->data['unit_number'] = $value;
        return $this;
    }

    public function device(Device $model): self
    {
        $this->data['gps_device_id'] = $model->id;
        return $this;
    }

    public function vin(string $value): self
    {
        $this->data['vin'] = $value;
        return $this;
    }

    public function company(Company $model): self
    {
        $this->data['carrier_id'] = $model->id;
        return $this;
    }

    public function driver(User $model): self
    {
        $this->data['driver_id'] = $model->id;
        return $this;
    }

    public function lastDeviceHistory(History $model): self
    {
        $this->data['last_gps_history_id'] = $model->id;
        return $this;
    }

    public function lastDrivingAt(CarbonImmutable $value): self
    {
        $this->data['last_driving_at'] = $value;
        return $this;
    }
}
