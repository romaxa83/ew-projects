<?php

namespace Database\Factories\AA;

use App\Models\AA\AAOrder;
use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AAOrderFactory extends Factory
{
    protected $model = AAOrder::class;

    public function definition(): array
    {
        $service = Service::find(1);
        $dealership = Dealership::find(1);

        return [
            'order_uuid' => $this->faker->uuid,
            'user_uuid' => $this->faker->uuid,
            'car_uuid' => $this->faker->uuid,
            'service_alias' => $service->alias,
            'sub_service_alias' => null,
            'dealership_alias' => $dealership->alias,
            'post_uuid' => $this->faker->uuid,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()
        ];
    }
}

