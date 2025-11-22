<?php

namespace Database\Factories\News;

use App\Models\News\Video;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Video[]|Video create(array $attributes = [])
 */
class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->slug,
        ];
    }
}
