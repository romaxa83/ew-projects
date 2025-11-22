<?php

namespace App\Events\Companies;

use App\Models\Companies\Company;

class CompanyCreatedEvent
{
    public function __construct(private Company $company)
    {
    }

    public function getCompany(): Company
    {
        return $this->company;
    }
}
