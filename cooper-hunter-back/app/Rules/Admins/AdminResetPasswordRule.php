<?php

namespace App\Rules\Admins;

use App\Models\Admins\Admin;
use App\Models\BaseAuthenticatable;
use App\Rules\AbstractResetPasswordRule;
use App\Services\Admins\AdminVerificationService;
use Illuminate\Database\Eloquent\Builder;

class AdminResetPasswordRule extends AbstractResetPasswordRule
{
    public function getVerificationService()
    {
        return app(AdminVerificationService::class);
    }

    public function getQuery(): Builder|BaseAuthenticatable
    {
        return Admin::query();
    }
}
