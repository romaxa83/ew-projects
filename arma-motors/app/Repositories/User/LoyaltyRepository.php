<?php

namespace App\Repositories\User;

use App\Models\User\Car;
use App\Models\User\Loyalty\Loyalty;
use App\Repositories\AbstractRepository;

class LoyaltyRepository extends AbstractRepository
{
    public function query()
    {
        return Loyalty::query();
    }

    public function getItemForType(Car $car, $age, $type): ?Loyalty
    {
        $itemService = $this->query()
            ->where('brand_id', $car->brand_id)
            ->where('type', $type)
            ->where('age', $age)
            ->first();

        if(null == $itemService){
            $itemService = $this->query()
                ->where('brand_id', $car->brand_id)
                ->where('type', $type)
                ->where('age', 'like', '%+')
                ->first();

            if(!($itemService && ($age > (int)$itemService->age))){
                $itemService = null;
            }
        }

        return $itemService;
    }

    public function getItemForTypeWithoutAge(Car $car, $type): ?Loyalty
    {
        return $this->query()
            ->where('brand_id', $car->brand_id)
            ->where('type', $type)
            ->first();
    }
}
