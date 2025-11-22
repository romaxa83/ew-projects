<?php

namespace Database\Factories\Permissions;

use App\Models\Admins\Admin;
use App\Models\Dealers\Dealer;
use App\Models\Permissions\Permission;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method static Permission|Permission[]|Collection create(array $attributes = [])
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        return [
            'guard_name' => User::GUARD,
            'name' => $this->faker->sentence,
        ];
    }

    public function admin(): self
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'guard_name' => Admin::GUARD
                ];
            }
        );
    }

    public function technician(): self
    {
        return $this->state(
            [
                'guard_name' => Technician::GUARD,
            ]
        );
    }

    public function dealer(): self
    {
        return $this->state(
            [
                'guard_name' => Dealer::GUARD,
            ]
        );
    }
}
