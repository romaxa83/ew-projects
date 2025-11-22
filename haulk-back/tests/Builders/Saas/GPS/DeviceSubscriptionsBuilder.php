<?php

namespace Tests\Builders\Saas\GPS;

use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceSubscription;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class DeviceSubscriptionsBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return DeviceSubscription::class;
    }

    public function company(Company $model): self
    {
        $this->data['company_id'] = $model->id;
        return $this;
    }

    public function status(DeviceSubscriptionStatus $value): self
    {
        $this->data['status'] = $value;
        return $this;
    }

    public function currentRate(float $value): self
    {
        $this->data['current_rate'] = $value;
        return $this;
    }

    public function nextRate(float $value): self
    {
        $this->data['next_rate'] = $value;
        return $this;
    }

    public function activeAt(CarbonImmutable $value): self
    {
        $this->data['active_at'] = $value;
        return $this;
    }

    public function activateTillAt(CarbonImmutable $value): self
    {
        $this->data['activate_till_at'] = $value;
        return $this;
    }

    public function accessTillAt(CarbonImmutable $value): self
    {
        $this->data['access_till_at'] = $value;
        return $this;
    }

    public function canceledAt(CarbonImmutable $value): self
    {
        $this->data['canceled_at'] = $value;
        return $this;
    }

    public function sendWarningNotify(bool $value = true): self
    {
        $this->data['send_warning_notify'] = $value;
        return $this;
    }
}


