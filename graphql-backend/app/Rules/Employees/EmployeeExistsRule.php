<?php

namespace App\Rules\Employees;

use App\Models\Companies\Company;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class EmployeeExistsRule implements Rule
{
    public function __construct(private Company $company)
    {
    }

    public function passes(mixed $attribute, mixed $value): bool
    {
        return User::query()
            ->whereCompany($this->company)
            ->whereKey($value)
            ->exists();
    }

    public function message(): string
    {
        return __('validation.exists');
    }
}
