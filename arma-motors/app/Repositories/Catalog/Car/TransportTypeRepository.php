<?php

namespace App\Repositories\Catalog\Car;

use App\Models\Catalogs\Car\TransportType;
use App\Repositories\AbstractRepository;

class TransportTypeRepository extends AbstractRepository
{
    public function query()
    {
        return TransportType::query();
    }
}
