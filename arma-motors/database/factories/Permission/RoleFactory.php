<?php

namespace Database\Factories\Permission;

use App\Models\Permission\Role;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method static Role create(array $attributes = [])
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => \Str::random(),
            'guard_name' => User::GUARD,
        ];
    }
}

