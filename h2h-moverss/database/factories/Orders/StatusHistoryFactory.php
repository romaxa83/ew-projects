<?php

namespace Database\Factories\Orders;

use App\Models\Order;
use App\Models\Order\Status;
use App\Models\Order\StatusChangeHistory;
use App\User;
use Database\Factories\BaseFactory;

class StatusHistoryFactory extends BaseFactory
{
    protected $model = StatusChangeHistory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'prev_status' => Status::factory(),
            'new_status' => Status::factory(),
            'is_deleted' => 0,
            'created_at' => now(),
        ];
    }
}
