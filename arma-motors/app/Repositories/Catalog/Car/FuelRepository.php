<?php

namespace App\Repositories\Catalog\Car;

use App\Models\Catalogs\Car\Fuel;
use App\Repositories\AbstractRepository;

class FuelRepository extends AbstractRepository
{
    public function query()
    {
        return Fuel::query();
    }
}

