<?php

namespace Database\Factories\Users;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method User|User[]|Collection create($attributes = [], ?Model $parent = null)
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'carrier_id' => 1,
            'broker_id' => null,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'status' => User::STATUS_ACTIVE,
            'can_check_orders' => null,
        ];
    }

    public function driver(): self
    {
        return $this->configure()
            ->afterCreating(
                static fn(User $user) => $user->assignRole(User::DRIVER_ROLE)
            );
    }

    public function dispatcher(): self
    {
        return $this->configure()
            ->afterCreating(
                static fn(User $user) => $user->assignRole(User::DISPATCHER_ROLE)
            );
    }
}
