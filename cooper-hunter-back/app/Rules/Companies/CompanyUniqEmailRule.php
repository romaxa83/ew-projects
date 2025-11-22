<?php

namespace App\Rules\Companies;

use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class CompanyUniqEmailRule implements Rule
{
    protected string $attr;

    public function __construct()
    {}

    public function passes($attribute, $value): bool
    {
        if(User::query()->where('email', $value)->exists()){
            return false;
        }
        if(Technician::query()->where('email', $value)->exists()){
            return false;
        }
        if(Dealer::query()->where('email', $value)->exists()){
            return false;
        }
        if(Company::query()->where('email', $value)->exists()){
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return __('validation.unique', ['attribute' => 'email']);
    }
}

