<?php

namespace Database\Factories\Catalog\Videos;

use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use App\Models\Catalog\Videos\Group;
use App\Models\Catalog\Videos\VideoLink;
use App\Models\Localization\Language;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method VideoLink|VideoLink[]|Collection create(array $attrs = [])
 */
class VideoLinkFactory extends BaseFactory
{
    protected $model = VideoLink::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'link_type' => VideoLinkTypeEnum::COMMON(),
            'sort' => 1,
            'link' => $this->faker->url,
            'group_id' => Group::factory(),
        ];
    }

    public function disabled(): static
    {
        return $this->state(['active' => false]);
    }

    public function configure(): self
    {
        return $this->afterCreating(
            fn (VideoLink $videoLink) => $videoLink->translations()->createMany(
                languages()->map(
                    fn (Language $language) => [
                        'slug' => $this->faker->slug,
                        'title' => $this->faker->title,
                        'description' => $this->faker->text,
                        'language' => $language->slug
                    ]
                )
            )
        );
    }
}


