<?php

namespace App\Rules\Users;

use App\Enums\Companies\CompanyStateEnum;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class MustBeOwnerOfCompanyRule implements Rule
{
    public function __construct(protected ?User $user = null)
    {
    }

    public function passes($attribute, $value): bool
    {
        return $this->user?->companyUser?->state === CompanyStateEnum::OWNER;
    }

    public function message(): string
    {
        return __('messages.user.must-be-owner');
    }
}
