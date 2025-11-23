<?php

namespace Database\Factories\Orders;

use App\Models\Order;
use Database\Factories\BaseFactory;

class WorkFactory extends BaseFactory
{
    protected $model = Order\Work::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'start_date' => '2020-09-16',
            'start_time' => '12:00:00',
            'start_time_to' => '13:00:00',
            'duration' => 3.0,
            'trucks' => 1,
            'employees' => 2,
            'notes' => null,
            'notes_by' => null,
            'notes_created_at' => null,
            'in_dispatch' => 0,
            'dispatch_updated_at' => null,
            'deleted_at' => null,
        ];
    }
}
