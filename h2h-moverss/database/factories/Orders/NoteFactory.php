<?php

namespace Database\Factories\Orders;

use App\Models\Order;
use App\Models\Order\Notes;
use App\User;
use Database\Factories\BaseFactory;

class NoteFactory extends BaseFactory
{
    protected $model = Notes::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'is_pinned' => 1,
            'visibility' => 1,
            'text' => $this->faker->text(),
            'deleted_at' => null,
        ];
    }
}
