<?php

namespace App\Repositories\User\CarOrder;

use App\Models\User\OrderCar\OrderCar;
use App\Repositories\AbstractRepository;

class OrderCarRepository extends AbstractRepository
{
    public function query()
    {
        return OrderCar::query();
    }

    public function getCountForDashboard(): int
    {
        return $this->query()->count();
    }
}

