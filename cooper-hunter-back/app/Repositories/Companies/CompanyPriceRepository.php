<?php

namespace App\Repositories\Companies;

use App\Models\Companies\Price;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class CompanyPriceRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Price::query();
    }
}

