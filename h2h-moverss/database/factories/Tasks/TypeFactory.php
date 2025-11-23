<?php

namespace Database\Factories\Tasks;

use App\Models\Tasks\Type;
use Database\Factories\BaseFactory;

class TypeFactory extends BaseFactory
{
    protected $model = Type::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'active' => true,
            'title' => $this->faker->company(),
            'sort' => 1,
            'icon' => 'hourglass',
            'color' => '#75a9f9',
        ];
    }
}
