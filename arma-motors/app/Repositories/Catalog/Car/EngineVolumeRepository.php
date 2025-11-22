<?php

namespace App\Repositories\Catalog\Car;

use App\Models\Catalogs\Car\EngineVolume;
use App\Repositories\AbstractRepository;

class EngineVolumeRepository extends AbstractRepository
{
    public function query()
    {
        return EngineVolume::query();
    }
}
