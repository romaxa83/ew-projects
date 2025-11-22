<?php

namespace App\Rules\Saas\Company;

use App\Models\Saas\Company\Company;
use Illuminate\Contracts\Validation\Rule;

class CompanyIsCancelSubscription implements Rule
{
    public function passes($attribute, $value): bool
    {
        $model = Company::find($value);

        if(!$model->subscription) return false;

        return $model->subscription->isActive();
    }

    public function message(): string
    {
        return __('validation.custom.company.cancel_subscription');
    }
}
