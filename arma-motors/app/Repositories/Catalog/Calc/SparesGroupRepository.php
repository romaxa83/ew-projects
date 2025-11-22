<?php

namespace App\Repositories\Catalog\Calc;

use App\Models\Catalogs\Calc\SparesGroup;
use App\Repositories\AbstractRepository;

class SparesGroupRepository extends AbstractRepository
{
    public function query()
    {
        return SparesGroup::query();
    }
}
