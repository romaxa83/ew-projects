<?php

namespace App\Rules\Companies;

use App\Models\Companies\Company;
use Illuminate\Contracts\Validation\Rule;

class CompanyRegisterRule implements Rule
{
    public function __construct(
        protected array $args
    ) {}

    public function passes($attribute, $value): bool
    {
        $company = Company::find($value);

        return $company->status->isRegister();
    }

    public function message(): string
    {
        return __('validation.company.not_register');
    }
}
