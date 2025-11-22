<?php

namespace App\Repositories\User\CarOrder;

use App\Models\User\OrderCar\OrderCarStatus;
use App\Repositories\AbstractRepository;

class UserCarOrderStatusRepository extends AbstractRepository
{
    public function query()
    {
        return OrderCarStatus::query();
    }
}
