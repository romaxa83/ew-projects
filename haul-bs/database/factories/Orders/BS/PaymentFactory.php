<?php

namespace Database\Factories\Orders\BS;

use App\Enums\Orders\PaymentMethod;
use App\Models\Orders\BS\Payment;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orders\BS\Payment>
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
            'payment_date' => now()->format('Y-m-d H:i'),
            'payment_method' => PaymentMethod::Cash->value,
            'notes' => fake()->sentence,
            'reference_number' => '5646456',
        ];
    }
}


