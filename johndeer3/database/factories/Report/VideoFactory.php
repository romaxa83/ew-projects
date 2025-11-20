<?php

namespace Database\Factories\Report;

use App\Models\Report\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city,
            'url' => $this->faker->imageUrl,
        ];
    }
}
