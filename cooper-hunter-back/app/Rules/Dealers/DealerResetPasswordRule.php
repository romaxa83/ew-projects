<?php

namespace App\Rules\Dealers;

use App\Models\BaseAuthenticatable;
use App\Models\Dealers\Dealer;
use App\Rules\AbstractResetPasswordRule;
use App\Services\Dealers\DealerVerificationService;
use Illuminate\Database\Eloquent\Builder;

class DealerResetPasswordRule extends AbstractResetPasswordRule
{
    public function getVerificationService()
    {
        return app(DealerVerificationService::class);
    }

    public function getQuery(): Builder|BaseAuthenticatable
    {
        return Dealer::query();
    }
}

