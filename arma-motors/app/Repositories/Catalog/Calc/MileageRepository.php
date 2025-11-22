<?php

namespace App\Repositories\Catalog\Calc;

use App\Models\Catalogs\Calc\Mileage;
use App\Repositories\AbstractRepository;

class MileageRepository extends AbstractRepository
{
    public function query()
    {
        return Mileage::query();
    }
}
