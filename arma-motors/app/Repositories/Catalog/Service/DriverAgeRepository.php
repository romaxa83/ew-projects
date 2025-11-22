<?php

namespace App\Repositories\Catalog\Service;

use App\Models\Catalogs\Service\DriverAge;
use App\Repositories\AbstractRepository;

class DriverAgeRepository extends AbstractRepository
{
    public function query()
    {
        return DriverAge::query();
    }
}
