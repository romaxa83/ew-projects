<?php

declare(strict_types=1);

namespace Wezom\Core\Database\Factories\Permission;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wezom\Core\Models\Permission\Role;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'guard_name' => 'graph_admin',
            'name' => $this->faker->jobTitle,
            'title' => $this->faker->jobTitle,
        ];
    }

    public function asDefault(): self
    {
        return $this->state(
            [
                'for_owner' => true,
            ]
        );
    }

    public function admin(): self
    {
        return $this->state(
            [
                'guard_name' => 'graph_admin',
            ]
        );
    }
}
