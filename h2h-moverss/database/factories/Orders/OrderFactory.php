<?php

namespace Database\Factories\Orders;

use App\Models\Client;
use App\Models\Division;
use App\Models\Order;
use App\Models\Order\Status;
use App\User;
use Database\Factories\BaseFactory;

class OrderFactory extends BaseFactory
{
    protected $model = Order::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'division_id' => Division::factory(),
            'status_id' => Status::factory(),
            'source_id' => null,
            'move_size_id' => null,
            'sizing_is_auto' => 1,
            'sizing_volume' => null,
            'sizing_weight' => null,
            'type' => null,
            'updated_by' => null,
            'hash' => $this->faker->hexColor(),
            'base_id' => null,
        ];
    }
}
