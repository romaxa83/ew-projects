<?php

declare(strict_types=1);

namespace Wezom\Core\Database\Factories\Permission;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wezom\Core\Models\Permission\Permission;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        return [
            'guard_name' => 'graph_admin',
            'name' => $this->faker->sentence,
        ];
    }

    public function admin(): PermissionFactory
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'guard_name' => 'graph_admin',
                ];
            }
        );
    }
}
