<?php

namespace WezomCms\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Core\Models\Administrator;

class AdministratorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Administrator::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->userName,
            'email' => $this->faker->email,
            'active' => $this->faker->boolean,
            'password' => bcrypt($this->faker->password(8)),
            'super_admin' => $this->faker->boolean,
        ];
    }

    /**
     * Add a new state transformation to the model definition.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => true,
            ];
        });
    }

    /**
     * Add a new state transformation to the model definition.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function superAdmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'super_admin' => true,
            ];
        });
    }
}
