<?php

namespace WezomCms\Catalog\Database\Factories\Collections;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Core\Models\Administrator;

class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        $data = [
            'published' => $this->faker->boolean(80),
            'creator_id' => Administrator::factory(),
            'moderator_id' => null,
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addDay(),
        ];

        return array_merge($data, array_fill_keys(array_keys(app('locales')), [
            'name' => $this->faker->realText(15),
            'image' => $this->faker->imageUrl()
        ]));
    }
}

