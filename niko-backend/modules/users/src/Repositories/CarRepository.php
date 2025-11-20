<?php

namespace WezomCms\Users\Repositories;

use App\Exceptions\UserNotFoundException;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Users\Models\Car;
use WezomCms\Users\Models\User;
use WezomCms\Users\Types\UserCarStatus;

class CarRepository extends AbstractRepository
{
    protected function query()
    {
        return Car::query();
    }

    public function getBy1CData($userId, $vinCode, $number)
    {
        return $this->query()
            ->where('vin_code', $vinCode)
            ->where('user_id', $userId)
            ->where('number_for_1c', $number)
            ->first();
    }

    public function existCar($userId, $number)
    {
        return $this->query()
            ->where('status', UserCarStatus::ACTIVE)
            ->where('user_id', $userId)
            ->where('number', $number)
            ->exists();
    }
}

