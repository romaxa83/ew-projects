<?php

namespace App\Repositories\Catalog\Calc;

use App\Models\Catalogs\Calc\Spares;
use App\Repositories\AbstractRepository;

class SparesRepository extends AbstractRepository
{
    public function query()
    {
        return Spares::query();
    }
}
