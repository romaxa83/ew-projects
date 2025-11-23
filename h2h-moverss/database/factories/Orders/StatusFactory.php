<?php

namespace Database\Factories\Orders;

use App\Models\Order\Status;
use App\Models\Order\StatusGroup;
use Database\Factories\BaseFactory;

class StatusFactory extends BaseFactory
{
    protected $model = Status::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->company(),
            'color' => $this->faker->hexColor(),
            'group_id' => StatusGroup::factory(),
            'sort' => 1,
            'actions' => null,
            'in_calendar' => 1,
        ];
    }
}
