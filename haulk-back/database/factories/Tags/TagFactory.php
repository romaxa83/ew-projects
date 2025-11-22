<?php

namespace Database\Factories\Tags;

use App\Models\Tags\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Tag|Tag[]|Collection create($attributes = [], ?Model $parent = null)
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'carrier_id' => !empty($data['carrier_id']) ? $data['carrier_id'] : 1,
            'name' => $this->faker->title,
            'type' => Tag::TYPE_ORDER,
            'color' => '#ffff',
        ];
    }
}
