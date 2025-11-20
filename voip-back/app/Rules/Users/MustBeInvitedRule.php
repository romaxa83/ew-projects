<?php

namespace App\Rules\Users;

use App\Enums\Companies\CompanyStateEnum;
use App\Models\Companies\Company;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class MustBeInvitedRule implements Rule
{
    public function __construct(protected Company $company)
    {
    }

    public function passes($attribute, $value): bool
    {
        $user = User::query()->where('email', $value)
            ->with('companyUser')
            ->firstOrFail();

        return $user?->companyUser?->state === CompanyStateEnum::INVITED &&
            $user?->companyUser?->company_id === $this->company->id;
    }

    public function message(): string
    {
        return __('messages.user.must-be-in-same-company');
    }
}
