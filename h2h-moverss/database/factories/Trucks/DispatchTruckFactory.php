<?php

namespace Database\Factories\Trucks;

use App\Models\DispatchTruck;
use App\Models\Order\Work;
use App\Models\Truck\Truck;
use Database\Factories\BaseFactory;

class DispatchTruckFactory extends BaseFactory
{
    protected $model = DispatchTruck::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'work_id' => Work::factory(),
            'truck_id' => Truck::factory(),
        ];
    }
}


