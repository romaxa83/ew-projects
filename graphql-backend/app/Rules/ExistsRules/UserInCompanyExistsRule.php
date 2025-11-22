<?php

namespace App\Rules\ExistsRules;

use App\Models\Companies\Company;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class UserInCompanyExistsRule implements Rule
{
    public function __construct(protected Company|int $company)
    {
    }

    public function passes($attribute, $value): bool
    {
        return User::query()
            ->whereKey($value)
            ->whereCompany($this->company)
            ->exists();
    }

    public function message(): string
    {
        return __('validation.user_in_company_not_exists');
    }
}
