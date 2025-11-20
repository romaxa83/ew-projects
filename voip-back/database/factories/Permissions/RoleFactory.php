<?php

namespace Database\Factories\Permissions;

use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Models\Permissions\Role;
use App\Models\Permissions\RoleTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Role|Role[]|Collection create(array $attributes = [])
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'guard_name' => Employee::GUARD,
            'name' => $this->faker->jobTitle,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(
            static function (Role $role) {
                foreach (languages() as $language) {

                    RoleTranslation::factory()->create(
                        [
                            'title' => $role->name,
                            'row_id' => $role->id,
                            'language' => $language->slug
                        ]
                    );
                }
            }
        );
    }

    public function admin(): self
    {
        return $this->state(
            [
                'guard_name' => Admin::GUARD
            ]
        );
    }
}
