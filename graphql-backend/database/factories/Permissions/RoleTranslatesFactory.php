<?php

namespace Database\Factories\Permissions;

use App\Models\Permissions\RoleTranslates;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method RoleTranslates|Collection create(array $attributes = [])
 */
class RoleTranslatesFactory extends Factory
{
    protected $model = RoleTranslates::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->jobTitle,
        ];
    }
}
