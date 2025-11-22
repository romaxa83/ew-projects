<?php

namespace App\Exceptions\Billing;

use App\Models\Saas\Company\Company;
use Exception;

class HasUnpaidInvoiceException extends Exception
{
    public static function denied(Company $company)
    {
        throw new self(__('exceptions.company.billing.has_unpaid_invoice', [
            'company_name' => $company->name
        ]), 401);
    }
}
