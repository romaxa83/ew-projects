<?php

namespace Database\Factories\Tasks;

use App\Models\Tasks\Status;
use Database\Factories\BaseFactory;

class StatusFactory extends BaseFactory
{
    protected $model = Status::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'active' => true,
            'title' => $this->faker->company(),
            'sort' => 1,
            'class' => 'danger',
        ];
    }
}
