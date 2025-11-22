<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @method Vehicle|Vehicle[]|Collection create($attributes = [], ?Model $parent = null)
 */
class VehicleFactory extends Factory
{

    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'make' => $this->faker->word,
            'model' => $this->faker->word,
            'type_id' => Vehicle::VEHICLE_TYPE_ATV,
            'vin' => Str::random(17)
        ];
    }
}
