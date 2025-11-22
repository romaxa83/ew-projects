<?php

namespace App\Rules\Saas\Company;

use App\Models\Saas\Company\Company;
use Illuminate\Contracts\Validation\Rule;

class CompanyHasGpsSubscription implements Rule
{
    public function passes($attribute, $value): bool
    {
        $model = Company::find($value);
        if($model){
            return $model->hasGpsDeviceSubscription();
        }

        return false;
    }

    public function message(): string
    {
        return __('validation.exists', ['attribute' => 'company_id']);
    }
}

