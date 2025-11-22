<?php

namespace App\Repositories\Catalog\Car;

use App\Models\Catalogs\Car\Transmission;
use App\Repositories\AbstractRepository;

class TransmissionRepository extends AbstractRepository
{
    public function query()
    {
        return Transmission::query();
    }
}
