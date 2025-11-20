<?php

namespace App\Rules\Users;

use App\Models\Companies\Company;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class MustBeInSameCompanyRule implements Rule
{
    public function __construct(protected Company $company, protected string $field = 'email')
    {
    }

    public function passes($attribute, $value): bool
    {
        return User::query()
            ->when($this->field === 'email', fn(Builder $b) => $b->where('email', $value))
            ->when($this->field === 'id', fn(Builder $b) => $b->whereKey($value))
            ->whereCompany($this->company)
            ->exists();
    }

    public function message(): string
    {
        return __('messages.user.must-be-in-same-company');
    }
}
