<?php

namespace Database\Factories\Users;

use App\Models\User\Role;
use Database\Factories\BaseFactory;

class RoleFactory extends BaseFactory
{
    protected $model = Role::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word,
            'for_crew' => false,
        ];
    }
}
