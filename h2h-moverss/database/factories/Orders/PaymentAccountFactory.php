<?php

namespace Database\Factories\Orders;

use App\Models\Division;
use App\Models\PaymentAccount;
use Database\Factories\BaseFactory;

class PaymentAccountFactory extends BaseFactory
{
    protected $model = PaymentAccount::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'is_active' => true,
            'title' =>$this->faker->city(),
            'division_id' => Division::factory(),
            'sort' => 1,
            'deleted_at' => null,
        ];
    }
}
