<?php

namespace Database\Factories\Orders\Parts;

use App\Enums\Orders\Parts\PaymentMethod;
use App\Models\Orders\Parts\Payment;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orders\Parts\Payment>
 */
class PaymentFactory extends BaseFactory
{
    protected $model = Payment::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => OrderFactory::new(),
            'amount' => fake()->randomFloat(2, 0, 99),
            'payment_at' => now()->format('Y-m-d H:i'),
            'payment_method' => PaymentMethod::Online(),
            'notes' => fake()->sentence,
        ];
    }
}


