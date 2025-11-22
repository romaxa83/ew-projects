<?php

namespace Database\Factories\Catalog\Videos;

use App\Models\Catalog\Videos\VideoLinkTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * @method VideoLinkTranslation|VideoLinkTranslation[]|Collection create(array $attrs = [])
 */
class VideoLinkTranslationFactory extends BaseTranslationFactory
{
    protected $model = VideoLinkTranslation::class;

    public function definition(): array
    {
        $title = $this->faker->word;

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->sentence,
        ];
    }
}
