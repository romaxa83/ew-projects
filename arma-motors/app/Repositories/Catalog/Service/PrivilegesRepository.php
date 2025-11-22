<?php

namespace App\Repositories\Catalog\Service;

use App\Models\Catalogs\Service\Privileges;
use App\Repositories\AbstractRepository;

class PrivilegesRepository extends AbstractRepository
{
    public function query()
    {
        return Privileges::query();
    }
}
