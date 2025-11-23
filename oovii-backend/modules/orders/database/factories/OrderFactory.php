<?php

namespace WezomCms\Orders\Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Orders\Models\Payment;
use WezomCms\Users\Models\User;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        /** @var Delivery $delivery */
        $delivery = Delivery::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();

        $status = OrderStatus::first() ?? OrderStatus::factory()->create();

        return [
            'delivery_id' => $delivery->id,
            'payment_id' => Payment::factory()->create(),
            'status_id' => $status->id,
            'user_id' => $user->id,
            'payed' => false,
            'dont_call_back' => $this->faker->boolean(),
            // 'comment' => $this->faker->sentences(3, true),
        ];
    }
}
