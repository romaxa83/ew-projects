<?php

namespace Database\Factories\Orders;

use App\Enums\ActionEnum;
use App\Models\Order;
use Database\Factories\BaseFactory;

class InventoryActivityFactory extends BaseFactory
{
    protected $model = Order\InventoryActivity::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'client_id' => null,
            'user_id' => null,
            'action' => ActionEnum::Create(),
            'is_client_action' => false,
            'miscs' => [],
        ];
    }
}
