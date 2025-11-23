<?php

namespace Database\Factories\Orders;

use App\Models\Order;
use App\Models\PaymentAccount;
use App\User;
use Database\Factories\BaseFactory;

class PaymentFactory extends BaseFactory
{
    protected $model = Order\Payment::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'payment_account_id' => PaymentAccount::factory(),
            'description' => $this->faker->sentence(),
            'amount' => 50,
            'in_total_sum' => true,
        ];
    }
}
