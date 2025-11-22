<?php

namespace App\Rules\Users;

use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class UserBelongsCompany implements Rule
{
    protected int $companyId;
    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    public function passes($attribute, $value): bool
    {
        $model = User::query()
            ->where('email', $value)
            ->where('carrier_id', $this->companyId)
            ->first();

        if(!$model){
            return false;
        }

        request()->merge(['user' => $model]);

        return true;
    }

    public function message(): string
    {
        return trans('validation.custom.user.not_belongs_company');
    }
}

