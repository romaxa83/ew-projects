<?php

namespace Tests\Builders\Saas\GPS;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatusActivateRequest;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\ValueObjects\Phone;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class DeviceBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Device::class;
    }

    public function company(Company $model): self
    {
        $this->data['company_id'] = $model->id;
        return $this;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }

    public function phone(?string $value): self
    {
        if($value){
            $this->data['phone'] = new Phone($value);
        } else {
            $this->data['phone'] = null;
        }
        return $this;
    }

    public function imei(string $value): self
    {
        $this->data['imei'] = $value;
        return $this;
    }

    public function withoutPhone(): self
    {
        $this->data['phone'] = null;
        return $this;
    }

    public function flespiDeviceId(int $value): self
    {
        $this->data['flespi_device_id'] = $value;
        return $this;
    }

    public function status(DeviceStatus $value, ?CarbonImmutable $deletedAt = null): self
    {
        $this->data['status'] = $value;

        if($value->isDeleted()){
            $this->data['deleted_at'] = $deletedAt ? $deletedAt : CarbonImmutable::now();
        }

        return $this;
    }

    public function activeTillAt(CarbonImmutable $value): self
    {
        $this->data['active_till_at'] = $value;

        return $this;
    }

    public function statusRequest(DeviceRequestStatus $value): self
    {
        $this->data['status_request'] = $value;

        return $this;
    }

    public function requestClosedAt(CarbonImmutable $value): self
    {
        $this->data['request_closed_at'] = $value;

        return $this;
    }

    public function statusActiveRequest(DeviceStatusActivateRequest $value): self
    {
        $this->data['status_activate_request'] = $value;

        return $this;
    }

    public function sendRequestUser(User $model): self
    {
        $this->data['send_request_user_id'] = $model->id;

        return $this;
    }

    public function statusActivateRequest(DeviceStatusActivateRequest $value): self
    {
        $this->data['status_activate_request'] = $value;

        return $this;
    }

    public function activeAt(CarbonImmutable $value): self
    {
        $this->data['active_at'] = $value;
        return $this;
    }
}

