<?php

namespace Database\Factories\News;

use App\Models\News\Video;
use App\Models\News\VideoTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|VideoTranslation[]|VideoTranslation create(array $attributes = [])
 */
class VideoTranslationFactory extends BaseTranslationFactory
{
    protected $model = VideoTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => Video::factory(),
            'video_link' => $this->faker->imageUrl,
            'title' => $this->faker->sentence,
            'description' => $this->faker->text,
            'seo_title' => $this->faker->word,
            'seo_description' => $this->faker->sentence,
            'seo_h1' => $this->faker->word,
        ];
    }
}
