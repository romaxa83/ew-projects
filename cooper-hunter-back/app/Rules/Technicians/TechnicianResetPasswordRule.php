<?php

namespace App\Rules\Technicians;

use App\Models\BaseAuthenticatable;
use App\Models\Technicians\Technician;
use App\Rules\AbstractResetPasswordRule;
use App\Services\Technicians\TechnicianVerificationService;
use Illuminate\Database\Eloquent\Builder;

class TechnicianResetPasswordRule extends AbstractResetPasswordRule
{
    public function getVerificationService()
    {
        return app(TechnicianVerificationService::class);
    }

    public function getQuery(): Builder|BaseAuthenticatable
    {
        return Technician::query();
    }
}
