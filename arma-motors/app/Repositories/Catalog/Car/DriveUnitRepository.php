<?php

namespace App\Repositories\Catalog\Car;

use App\Models\Catalogs\Car\DriveUnit;
use App\Repositories\AbstractRepository;

class DriveUnitRepository extends AbstractRepository
{
    public function query()
    {
        return DriveUnit::query();
    }
}
