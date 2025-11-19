<?php

declare(strict_types=1);

namespace Wezom\Admins\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\Models\Admin;
use Wezom\Core\Enums\ActiveTypeEnum;
use Wezom\Core\Enums\RoleEnum;
use Wezom\Core\Models\Permission\Role;
use Wezom\Core\Traits\Factory\ActiveFactoryTrait;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    use ActiveFactoryTrait;

    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'status' => AdminStatusEnum::ACTIVE,
            'password' => Hash::createBcryptDriver()->make('password'), // password
            'active' => true,
        ];
    }

    public function admin(): static
    {
        return $this->hasAttached(
            Role::query()->whereType(RoleEnum::ADMIN)->whereGuardName(Admin::GUARD)->first(),
        );
    }

    public function manager(): static
    {
        return $this->hasAttached(
            Role::factory()->create(['name' => 'Manager'])
        );
    }

    public function superAdmin(): static
    {
        return $this->hasAttached(
            Role::query()->where('system_type', config('permissions.roles.super_admin'))->first(),
        );
    }

    public function disabled(): static
    {
        return $this->state([
            'active' => ActiveTypeEnum::DISABLED,
            'status' => AdminStatusEnum::INACTIVE,
        ]);
    }
}
