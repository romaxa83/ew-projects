<?php

namespace App\Events\Companies;

use App\Models\Companies\Company;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateCompanyByOnecEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(protected Company $model)
    {}

    public function getCompany(): Company
    {
        return $this->model;
    }
}
