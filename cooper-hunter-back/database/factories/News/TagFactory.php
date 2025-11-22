<?php

namespace Database\Factories\News;

use App\Models\News\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Tag[]|Tag create(array $attributes = [])
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'color' => $this->faker->colorName,
        ];
    }
}
