<?php

namespace App\Repositories\Order;

use App\Models\Agreement\Agreement;
use App\Repositories\AbstractRepository;

class AgreementRepository extends AbstractRepository
{
    public function query()
    {
        return Agreement::query();
    }
}

