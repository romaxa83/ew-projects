<?php

namespace App\Repositories\User\CarOrder;

use App\Models\User\OrderCar\OrderStatus;
use App\Repositories\AbstractRepository;

class CarOrderStatusRepository extends AbstractRepository
{
    public function query()
    {
        return OrderStatus::query();
    }
}
