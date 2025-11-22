<?php

namespace App\Rules\Users;

use App\Models\BaseAuthenticatable;
use App\Models\Users\User;
use App\Rules\AbstractResetPasswordRule;
use App\Services\Users\UserVerificationService;
use Illuminate\Database\Eloquent\Builder;

class ResetPasswordRule extends AbstractResetPasswordRule
{
    public function getVerificationService()
    {
        return app(UserVerificationService::class);
    }

    public function getQuery(): Builder|BaseAuthenticatable
    {
        return User::query();
    }
}
