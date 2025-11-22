<?php

namespace Database\Factories\Permissions;

use App\Models\Permissions\RoleTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method RoleTranslate|Collection create(array $attributes = [])
 */
class RoleTranslateFactory extends Factory
{
    protected $model = RoleTranslate::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->jobTitle,
        ];
    }
}
