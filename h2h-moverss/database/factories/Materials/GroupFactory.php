<?php

namespace Database\Factories\Materials;

use App\Models\Material\Group;
use Database\Factories\BaseFactory;

class GroupFactory extends BaseFactory
{
    protected $model = Group::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->title(),
            'deleted_at' => null,
        ];
    }
}
