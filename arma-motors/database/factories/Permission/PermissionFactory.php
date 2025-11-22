<?php

namespace Database\Factories\Permission;

use App\Models\Permission\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method static Permission create(array $attributes = [])
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        return [
            'guard_name' => 'graph_user',
            'name' => $this->faker->sentence,
        ];
    }
}
