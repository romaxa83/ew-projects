<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\OrderComment;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderCommentFactory extends Factory
{

    protected $model = OrderComment::class;

    public function definition(): array
    {
        return [
            'created_at' => now(),
            'updated_at' => now(),
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'carrier_id' => Company::factory(),
            'comment' => $this->faker->sentence,
            'role_id' => 3,//DISPATCHER
        ];
    }
}
