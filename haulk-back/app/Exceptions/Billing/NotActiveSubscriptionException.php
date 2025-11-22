<?php

namespace App\Exceptions\Billing;

use App\Models\Saas\Company\Company;
use Exception;

class NotActiveSubscriptionException extends Exception
{
    public static function denied(Company $company)
    {
        throw new self(__('exceptions.company.billing.not_active', [
            'company_name' => $company->name
        ]), 401);
    }
}

