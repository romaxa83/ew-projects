<?php

namespace Database\Factories\Permission;

use App\Models\Permission\RoleTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method static RoleTranslation create(array $attributes = [])
 */
class RoleTranslationFactory extends Factory
{
    protected $model = RoleTranslation::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
            'lang' => app('localization')->getDefaultSlug(),
        ];
    }
}
