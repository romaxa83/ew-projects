<?php

namespace Database\Factories\Items;

use App\Models\Item\Group;
use Database\Factories\BaseFactory;

class GroupFactory extends BaseFactory
{
    protected $model = Group::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'deleted_at' => null,
        ];
    }
}
