<?php

namespace Database\Factories\Admins;

use App\Models\Admins\Admin;
use App\ValueObjects\Email;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method static Admin|Admin[]|Collection create(array $attributes = [])
 */
class AdminFactory extends Factory
{

    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->lastName . ' '. $this->faker->firstName,
            'email' => new Email($this->faker->unique()->safeEmail),
            'password' => '$2y$10$k1wlbzKjC09gx2yMca/e6.Pm2pCLX9cni8eY0eD2RnjOVzLQ7X/xK', // Password123
            'lang' => app('localization')->getDefaultSlug(),
            'active' => true,
        ];
    }
}
