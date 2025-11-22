<?php

namespace App\Rules\Saas\Gps;

use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\Company\Company;
use Illuminate\Contracts\Validation\Rule;

class CanAddDeviceToCompanyRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        /** @var $model Company */
        $model = Company::query()->where('id', $value)->first();

        return !$model->gpsDeviceSubscription->status->is(DeviceSubscriptionStatus::ACTIVE_TILL());
    }

    public function message(): string
    {
        return __('validation.custom.gps.device_subscription.company_cancel_subscription');
    }
}
