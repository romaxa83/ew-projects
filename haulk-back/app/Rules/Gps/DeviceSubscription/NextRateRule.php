<?php

namespace App\Rules\Gps\DeviceSubscription;

use App\Models\Saas\Company\Company;
use Illuminate\Contracts\Validation\Rule;

class NextRateRule implements Rule
{
    protected Company $company;

    public function __construct(Company $company)
    {

        $this->company = $company;
    }

    public function passes($attribute, $value): bool
    {

        return $this->company->gpsDeviceSubscription->current_rate != $value;
    }

    public function message(): string
    {
        return trans('validation.custom.gps.device_subscription.next_rate_same_current_rate');
    }
}
