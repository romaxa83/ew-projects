<?php

namespace App\Repositories\Catalog\Region;

use App\Models\Catalogs\Region\City;
use App\Repositories\AbstractRepository;

class CityRepository extends AbstractRepository
{
    public function query()
    {
        return City::query();
    }
}


