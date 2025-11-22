<?php

namespace App\Repositories\Catalog\Region;

use App\Models\Catalogs\Region\Region;
use App\Repositories\AbstractRepository;

class RegionRepository extends AbstractRepository
{
    public function query()
    {
        return Region::query();
    }
}
