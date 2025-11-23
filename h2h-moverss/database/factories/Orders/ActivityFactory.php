<?php

namespace Database\Factories\Orders;

use App\Enums\Orders\ActivityType;
use App\Models\Order;
use App\User;
use Database\Factories\BaseFactory;

class ActivityFactory extends BaseFactory
{
    protected $model = Order\Activity::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'type' => ActivityType::Status(),
            'miscs' => [],
            'ext_id' => null,
        ];
    }
}
