<?php

namespace Database\Factories\About;

use App\Models\About\Page;
use App\Models\Localization\Language;
use Carbon\Carbon;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Page[]|Page create(array $attributes = [])
 */
class PageFactory extends BaseFactory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'slug' => $this->faker->slug,
        ];
    }

    public function configure(): PageFactory
    {
        return $this->afterCreating(
            fn(Page $page) => $page
                ->translations()
                ->createMany(
                    languages()
                        ->map(
                            fn(Language $language) => [
                                'title' => $this->faker->text,
                                'description' => $this->faker->text,
                                'language' => $language->slug
                            ]
                        )
                        ->values()
                        ->toArray()
                )
        );
    }
}
