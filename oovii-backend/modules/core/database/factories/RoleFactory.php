<?php

namespace WezomCms\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Core\Models\Role;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->jobTitle,
            'permissions' => [],
        ];
    }
}
