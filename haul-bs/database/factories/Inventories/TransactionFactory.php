<?php

namespace Database\Factories\Inventories;

use App\Enums\Inventories\Transaction\OperationType;
use App\Models\Inventories\Transaction;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventories\Transaction>
 */
class TransactionFactory extends BaseFactory
{
    protected $model = Transaction::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'operation_type' => OperationType::PURCHASE->value,
            'quantity' => fake()->randomFloat(2, 1, 1000),
            'price' => fake()->randomFloat(2, 1, 1000),
            'invoice_number' => fake()->text(15),
            'transaction_date' => now(),
            'is_reserve' => false,
        ];
    }
}
