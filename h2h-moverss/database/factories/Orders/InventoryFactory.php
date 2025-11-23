<?php

namespace Database\Factories\Orders;

use App\Models\Order;
use App\User;
use Database\Factories\BaseFactory;

class InventoryFactory extends BaseFactory
{
    protected $model = Order\Inventory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'section_id' => User::factory(),
            'is_section' => 0,
            'item_id' => null,
            'title' => 'kitchen',
            'price' => null,
            'qty' => 1,
            'weight' => null,
            'volume' => null,
            'sort' => 1,
        ];
    }
}
