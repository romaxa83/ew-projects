<?php

namespace Database\Factories\Tags;

use App\Enums\Tags\TagType;
use App\Models\Tags\Tag;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tags\Tag>
 */
class TagFactory extends BaseFactory
{
    protected $model = Tag::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker->city .'-'. $this->faker->postcode,
            'type' => TagType::CUSTOMER(),
            'color' => '#ffff',
        ];
    }
}
