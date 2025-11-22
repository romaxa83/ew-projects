<?php

namespace App\Repositories\User;

use App\Models\User\Car;
use App\Repositories\AbstractRepository;
use App\ValueObjects\CarNumber;

class CarRepository extends AbstractRepository
{
    public function query()
    {
        return Car::query();
    }

    public function getByNumber(CarNumber $number, $relations = [])
    {
        return $this->query()
            ->with($relations)
            ->where('number', $number)
            ->first();
    }

    public function getByUuidAndUserUuid(string $userId, string $carId)
    {
        return $this->query()
            ->with(['user'])
            ->where('uuid', $carId)
            ->whereHas('user', function($q) use ($userId){
                $q->where('uuid', $userId);
            })
            ->first();
    }

    public function getAllByOrderAndUser(string $userId)
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('is_order', true)
            ->get();
    }

    public function getByNumberAndUserToArchive(
        CarNumber $number,
        $userId
    ): null|Car
    {
        return $this->query()
            ->onlyTrashed()
            ->where('number', $number)
            ->where('user_id', $userId)
            ->first();
    }
}
