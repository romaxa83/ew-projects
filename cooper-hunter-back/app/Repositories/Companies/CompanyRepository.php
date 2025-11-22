<?php

namespace App\Repositories\Companies;

use App\Models\Companies\Company;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class CompanyRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Company::query();
    }
}
