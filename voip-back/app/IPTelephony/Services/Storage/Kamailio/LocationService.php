<?php

namespace App\IPTelephony\Services\Storage\Kamailio;

use App\IPTelephony\Entities\Kamailio\LocationEntity;

class LocationService extends KamailioService
{
    public function getTable(): string
    {
        return LocationEntity::TABLE;
    }

    public function locationsUsername(): array
    {
        return $this->getAll()
            ->pluck('username', 'username')
            ->toArray();
    }
}

