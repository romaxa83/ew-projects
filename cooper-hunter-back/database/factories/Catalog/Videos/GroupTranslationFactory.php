<?php

namespace Database\Factories\Catalog\Videos;

use App\Models\Catalog\Videos\GroupTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * @method GroupTranslation|GroupTranslation[]|Collection create(array $attrs = [])
 */
class GroupTranslationFactory extends BaseTranslationFactory
{
    protected $model = GroupTranslation::class;

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

