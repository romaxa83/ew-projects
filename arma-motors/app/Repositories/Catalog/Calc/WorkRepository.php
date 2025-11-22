<?php

namespace App\Repositories\Catalog\Calc;

use App\Models\Catalogs\Calc\Work;
use App\Repositories\AbstractRepository;

class WorkRepository extends AbstractRepository
{
    public function query()
    {
        return Work::query();
    }
}
