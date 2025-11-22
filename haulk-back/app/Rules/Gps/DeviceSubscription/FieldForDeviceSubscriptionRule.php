<?php

namespace App\Rules\Gps\DeviceSubscription;

use App\Models\Saas\Company\Company;
use Illuminate\Contracts\Validation\Rule;

class FieldForDeviceSubscriptionRule implements Rule
{
    protected Company $company;
    protected string $field = '';

    public function __construct(Company $company)
    {

        $this->company = $company;
    }

    public function passes($attribute, $value): bool
    {
        $this->field = $attribute;
        return $this->company->gpsDeviceSubscription !== null;
    }

    public function message(): string
    {
        return trans('validation.custom.gps.device_subscription.field_for_device_subscription', [
            'field' => $this->field
        ]);
    }
}

