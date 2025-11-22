<?php

namespace App\Repositories\Catalog\Service;

use App\Models\Catalogs\Service\Duration;
use App\Repositories\AbstractRepository;

class DurationRepository extends AbstractRepository
{
    public function query()
    {
        return Duration::query();
    }
}

