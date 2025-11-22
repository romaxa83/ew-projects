<?php

namespace Tests\Builders\Saas\GPS;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatusActivateRequest;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DevicePayment;
use App\Models\Users\User;
use App\ValueObjects\Phone;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class DevicePaymentBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return DevicePayment::class;
    }

    public function company(Company $model): self
    {
        $this->data['company_id'] = $model->id;

        if($model->gpsDeviceSubscription){
            $this->data['amount'] = $model->gpsDeviceSubscription->current_rate;
        }

        return $this;
    }

    public function device(Device $model): self
    {
        $this->data['device_id'] = $model->id;
        return $this;
    }

    public function date(CarbonImmutable $value): self
    {
        $this->data['date'] = $value;

        return $this;
    }
}


