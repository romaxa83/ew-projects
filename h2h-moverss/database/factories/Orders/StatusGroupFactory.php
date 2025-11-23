<?php

namespace Database\Factories\Orders;

use App\Models\Order\StatusGroup;
use Database\Factories\BaseFactory;

class StatusGroupFactory extends BaseFactory
{
    protected $model = StatusGroup::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->company(),
            'sort' => 1,
            'in_report' => 1,
            'in_funel_report' => 0,
        ];
    }
}
