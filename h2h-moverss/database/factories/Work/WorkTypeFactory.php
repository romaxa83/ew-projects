<?php

namespace Database\Factories\Work;

use App\Models\WorkTypes;
use Database\Factories\BaseFactory;

class WorkTypeFactory extends BaseFactory
{
    protected $model = WorkTypes::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word,
            'sort' => 1,
            'deleted_at' => null,
        ];
    }
}
