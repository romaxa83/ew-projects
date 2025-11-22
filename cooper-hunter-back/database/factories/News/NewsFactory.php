<?php

namespace Database\Factories\News;

use App\Models\News\News;
use App\Models\News\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|News[]|News create(array $attributes = [])
 */
class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'tag_id' => Tag::factory(),
            'active' => 1,
            'sort' => 0,
            'slug' => $this->faker->slug,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
